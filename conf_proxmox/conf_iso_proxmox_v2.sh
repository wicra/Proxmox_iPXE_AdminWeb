
#!/bin/bash

# Affiche une bannière d'informations sur le script
cat << EOF

#########################################################################################################
# Créer une image Proxmox bootable PXE incluant l'ISO                                                   #
#                                                                                                       #
# Auteur : mrballcb @ Proxmox Forum (06-12-2012)                                                        #
# Fil de discussion : http://forum.proxmox.com/threads/8484-Proxmox-installation-via-PXE-solution?p=55985#post55985 #
# Modifié : morph027 @ Proxmox Forum (23-02-2015) pour fonctionner avec 3.4                             #
#########################################################################################################

EOF

# Vérifie si deux arguments sont fournis (le chemin vers l'ISO et le chemin vers le script post-install)
if [ ! $# -eq 2 ]; then
  echo -ne "Usage: bash pve-iso-2-pxe.sh /path/to/pve.iso /path/to/post-install-script.sh\n\n"
  exit 1
fi

# Chemin vers l'ISO d'origine et le script post-install
ISO_ORIGINAL="$1"
SCRIPT_POST_INSTALL="$2"

# Vérifie si l'ISO d'origine existe
if [ ! -f "$ISO_ORIGINAL" ]; then
  echo "L'ISO d'origine n'existe pas : $ISO_ORIGINAL"
  exit 1
fi

# Vérifie si le script post-install existe
if [ ! -f "$SCRIPT_POST_INSTALL" ]; then
  echo "Le script post-install n'existe pas : $SCRIPT_POST_INSTALL"
  exit 1
fi

# Détermine le répertoire de base à partir du chemin de l'ISO
BASEDIR="$(dirname "$(readlink -f "$ISO_ORIGINAL")")"
# Change de répertoire vers BASEDIR
pushd "$BASEDIR" >/dev/null || exit 1

# Supprime un lien symbolique existant vers proxmox.iso s'il existe
[ -L "proxmox.iso" ] && rm proxmox.iso &>/dev/null

# Crée un lien symbolique vers le fichier ISO trouvé
ln -s "$ISO_ORIGINAL" proxmox.iso

# Vérifie si proxmox.iso existe
if [ ! -f "proxmox.iso" ]; then
  echo "Impossible de trouver un ISO proxmox, abandon."
  echo "Ajoutez /path/to/iso_dir à la ligne de commande."
  exit 2
fi

# Supprime et recrée le répertoire pxeboot
rm -rf pxeboot
[ -d pxeboot ] || mkdir pxeboot

# Change de répertoire vers pxeboot
pushd pxeboot >/dev/null || exit 1

# Extrait le noyau de l'ISO
echo "Extraction du noyau..."
if [ -x "$(which isoinfo)" ] ; then
  isoinfo -i ../proxmox.iso -R -x /boot/linux26 > linux26 || exit 3
else
  7z x ../proxmox.iso boot/linux26 -o/tmp || exit 3
  mv /tmp/boot/linux26 /tmp/
fi

# Extrait l'image initrd de l'ISO
echo "Extraction de l'initrd..."
if [ -x "$(which isoinfo)" ] ; then
  isoinfo -i ../proxmox.iso -R -x /boot/initrd.img > /tmp/initrd.img
else
  7z x ../proxmox.iso boot/initrd.img -o/tmp
  mv /tmp/boot/initrd.img /tmp/
fi

# Détecte le type de compression de initrd.img
mimetype="$(file --mime-type --brief /tmp/initrd.img)"
case "${mimetype##*/}" in
  "zstd"|"x-zstd")
    decompress="zstd -d /tmp/initrd.img -c"
    ;;
  "gzip"|"x-gzip")
    decompress="gzip -S img -d /tmp/initrd.img -c"
    ;;
  *)
    echo "Impossible de détecter la méthode de compression de initrd, sortie"
    exit 1
    ;;
esac

# Décompresse initrd.img
$decompress > initrd || exit 4

# Ajoute le fichier ISO à initrd
echo "Ajout du fichier ISO..."
if [ -x "$(which cpio)" ] ; then
  echo "../proxmox.iso" | cpio -L -H newc -o >> initrd || exit 5
else
  7z x "../proxmox.iso" >> initrd || exit 5
fi

# Ajoute le script post-install à initrd
echo "Ajout du script post-install à initrd..."
cp "$SCRIPT_POST_INSTALL" /tmp/
echo "path/to/script_post_install.sh" | cpio -L -H newc -o >> initrd || exit 5

# Modification de initrd pour exécuter le script post-install
echo "Modification de initrd pour exécuter le script post-install..."
if [ -f /tmp/init ]; then
  sed -i '/^exit 0/i bash /path/to/script_post_install.sh' /tmp/init
  echo "bash /path/to/script_post_install.sh" | cpio -L -H newc -o >> initrd || exit 5
else
  echo "bash /path/to/script_post_install.sh" >> /tmp/init
  echo "/tmp/init" | cpio -L -H newc -o >> initrd || exit 5
fi

# Retourne au répertoire précédent
popd >/dev/null 2>&1 || exit 1

echo "Terminé ! Les fichiers pxeboot se trouvent dans ${PWD}."
# Nettoie en revenant aux répertoires précédents, même si cela échoue
popd >/dev/null 2>&1 || true
popd >/dev/null 2>&1 || true

