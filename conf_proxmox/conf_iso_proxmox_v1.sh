
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

# Vérifie si un seul argument est fourni (le chemin vers l'ISO)
if [ ! $# -eq 1 ]; then
  echo -ne "Usage: bash pve-iso-2-pxe.sh /path/to/pve.iso\n\n"
  exit 1
fi

# Détermine le répertoire de base à partir du chemin de l'ISO
BASEDIR="$(dirname "$(readlink -f "$1")")"
# Change de répertoire vers BASEDIR
pushd "$BASEDIR" >/dev/null || exit 1

# Supprime un lien symbolique existant vers proxmox.iso s'il existe
[ -L "proxmox.iso" ] && rm proxmox.iso &>/dev/null

# Parcourt tous les fichiers .iso dans le répertoire
for ISO in *.iso; do
  # Ignore les fichiers nommés *.iso et proxmox.iso
  if [ "$ISO" = "*.iso" ]; then continue; fi
  if [ "$ISO" = "proxmox.iso" ]; then continue; fi
  echo "Utilisation de ${ISO}..."
  # Crée un lien symbolique vers le fichier ISO trouvé
  ln -s "$ISO" proxmox.iso
done

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
if [ -x $(which isoinfo) ] ; then
  isoinfo -i ../proxmox.iso -R -x /boot/linux26 > linux26 || exit 3
else
  7z x ../proxmox.iso boot/linux26 -o/tmp || exit 3
  mv /tmp/boot/linux26 /tmp/
fi

# Extrait l'image initrd de l'ISO
echo "Extraction de l'initrd..."
if [ -x $(which isoinfo) ] ; then
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
if [ -x $(which cpio) ] ; then
  echo "../proxmox.iso" | cpio -L -H newc -o >> initrd || exit 5
else
  7z x "../proxmox.iso" >> initrd || exit 5
fi

# Retourne au répertoire précédent
popd >/dev/null 2>&1 || exit 1

echo "Terminé ! Les fichiers pxeboot se trouvent dans ${PWD}."
# Nettoie en revenant aux répertoires précédents, même si cela échoue
popd >/dev/null 2>&1 || true
popd >/dev/null 2>&1 || true
