#!/bin/bash

#*************************************************************************************
#*                                                                                   *
#*  configuration du proxmox                                                         *
#*      + recueil des données utiles                                                 *
#*      + création d'un utilisateur                                                  *
#*      + configuration de l'autologin                                               *
#*      + installation et configuration de sudo                                      *
#*      + création d'un utilisateur proxmox                                          *
#*  connexion à la VM en local                                                       *
#*      +installation du serveur X et de virt-viewer                                 *
#*      +écriture du script "connexion2spice.sh" de connexion et d'affichage         *
#*           à la VM en local. le script est placé dans le 'home' de l'utilisateur   *
#*      +configuration du gestionnaire de fenetre pour supprimer l'écran             *
#*           de veille et lancer le script "connexion2spice.sh"                      *
#*      +ajout du démarrage du serveur X dans le ".profiile" de l'utilisateur        *
#*   création de la VM                                                               *
#*      +récupération des informations dans le bios de la machine physique           *
#*           pour les écrire dans le bios de la VM                                   *
#*      +si présente récupération de la clé d'activation windows pour l'intégrer     *
#*           dans le bios de la VM                                                   *
#*      +création de la VM                                                           *
#*                                                                                   *
#*    au reboot la VM est affichée en plein écran (l'accès au proxmox se fera        *
#*          via l'interface web à distance)                                          *
#*                                                                                   *
#*************************************************************************************

#création d'un utilisateur (pour linux afin d'éviter l'autologin avec root)
read -p "Nom d'utilisateur : " username
read -s -p "Taper un mot de passe : " password
echo
read -s -p "Retaper le mot de passe : " password2

while [ "$password" != "$password2" ];
do
    echo 
    echo "Les saisies diffèrent veuillez recommencer"
    read -s -p "Tapez un mot de passe : " password
    echo
    read -s -p "Retapez le mot de passe : " password2
done

useradd -m $username
echo -e "$password\n$password" | passwd  $username >/dev/null 2>&1
echo
read -p "Adresse ip du serveur proxy : " proxysys
read -p "Numéro de port du proxy : " proxyport

read -p "Quel est le nom de la machine virtuelle ?" vmname

read -p "Adresse du serveur de stockage : " ipstockage

#Section création de répertoire & de fichier

echo $vmname > /home/$username/NAME
chown $username:$username /home/$username/NAME
mkdir /home/$username/PROJECTEST
chown $username:$username /home/$username/PROJECTEST
mkdir /mnt/stockage
chown $username:$username /mnt/stockage
#autorisation ssh
mkdir /home/$username/.ssh
chown $username:$username /home/$username/.ssh
cp /mnt/stockage/authorized_keys /home/$username/.ssh

#Modifier un paramètre dans le fichier /etc/systemd/logind.conf
# Remplacer "#NAutoVTs=6"  par : "NAutoVTs=1"
sed -i -r 's/.*#NAutoVTs=6.*/NAutoVTs=1/g' /etc/systemd/logind.conf


mkdir /etc/systemd/system/getty@tty1.service.d
echo '[Service]' > /etc/systemd/system/getty@tty1.service.d/override.conf
echo 'ExecStart=' >> /etc/systemd/system/getty@tty1.service.d/override.conf
echo "ExecStart=-/usr/sbin/agetty --autologin $username --noclear %I \$TERM" >> /etc/systemd/system/getty@tty1.service.d/override.conf

#Activer le service au boot
systemctl is-enabled getty@tty1.service
systemctl enable getty@tty1.service
#Recharger les modifications
systemctl daemon-reload


#configuration du proxy pour Apt
echo -e "Acquire::http::proxy  \"http://$proxysys:$proxyport/\";" >> /etc/apt/apt.conf.d/70debconf
echo -e "Acquire::https::proxy  \"http://$proxysys:$proxyport/\";" >> /etc/apt/apt.conf.d/70debconf

apt update
apt install sudo


#Configurer sudo pour autoriser l'utilisateur à lancer la commande "qm list" en tant que root sans mot de passe
sed -ri "/^root/a$username  ALL=(ALL) NOPASSWD: /usr/sbin/qm, /usr/bin/sshfs, /usr/bin/mount, /usr/bin/umount, /usr/sbin/fdisk, /usr/sbin/reged" /etc/sudoers

#création de l'utilisateur dans proxmox (utilise l'authentification du système)
pveum useradd $username@pam
pveum acl modify /vms -user $username@pam -role PVEVMUser

apt -y install xserver-xorg x11-xserver-utils xinit openbox
apt -y install virt-viewer
apt -y install sshfs ntfs-3g chntpw vim nfs-common


mount $ipstockage:/images /mnt/stockage
echo "liste des disques disponibles sur le serveur de stockage"
ls -l /mnt/stockage
read -p "Quel est le nom du disque à charger ?" DisqueChargement

#création du script connexion2spice.sh dans le home de l'utilisateur
echo '#!/bin/bash' > /home/$username/connexion2spice.sh
echo 'set -e' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo '# Set VM ID : read the first ID in running vms list ' >> /home/$username/connexion2spice.sh
echo 'VMID=$(sudo qm list | grep running | sed -n -e "1p" | sed -nre "s/^[[:space:]]*([0-9]{3}).*/\1/p")' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo '# Set Node' >> /home/$username/connexion2spice.sh
echo '# This must either be a DNS address or name of the node in the cluster' >> /home/$username/connexion2spice.sh
echo 'NODE=$(hostname)' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo '# Proxy equals node if node is a DNS address' >> /home/$username/connexion2spice.sh
echo '# Otherwise, you need to set the IP address of the node here' >> /home/$username/connexion2spice.sh
echo 'PROXY="127.0.0.1"' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo '#The rest of the script from Proxmox' >> /home/$username/connexion2spice.sh
echo 'NODE="${NODE%%\.*}"' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo "DATA=\"\$(curl -f -s -S -k --data-urlencode \"username=$username@pam\" --data-urlencode \"password=$password\" \"https://\$PROXY:8006/api2/json/access/ticket\")\"" >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo 'echo "AUTH OK"' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo 'TICKET="${DATA//\"/}"' >> /home/$username/connexion2spice.sh
echo 'TICKET="${TICKET##*ticket:}"' >> /home/$username/connexion2spice.sh
echo 'TICKET="${TICKET%%,*}"' >> /home/$username/connexion2spice.sh
echo 'TICKET="${TICKET%%\}*}"' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo 'CSRF="${DATA//\"/}"' >> /home/$username/connexion2spice.sh
echo 'CSRF="${CSRF##*CSRFPreventionToken:}"' >> /home/$username/connexion2spice.sh
echo 'CSRF="${CSRF%%,*}"' >> /home/$username/connexion2spice.sh
echo 'CSRF="${CSRF%%\}*}"' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo 'curl -f -s -S -k -b "PVEAuthCookie=$TICKET" -H "CSRFPreventionToken: $CSRF" "https://$PROXY:8006/api2/spiceconfig/nodes/$NODE/qemu/$VMID/spiceproxy" -d "proxy=$PROXY" > ~/spiceproxy' >> /home/$username/connexion2spice.sh
echo '' >> /home/$username/connexion2spice.sh
echo '#Launch remote-viewer with spiceproxy file, in kiosk mode, quit on disconnect' >> /home/$username/connexion2spice.sh
echo '#The run loop will get a new ticket and launch us again if we disconnect' >> /home/$username/connexion2spice.sh
echo 'exec remote-viewer -k --kiosk-quit on-disconnect spiceproxy' >> /home/$username/connexion2spice.sh

chown $username:$username /home/$username/connexion2spice.sh
chmod +x /home/$username/connexion2spice.sh



echo '# Suppress screensaver start attempts and autoblanking
' > /etc/xdg/openbox/autostart
echo 'xset s off' >> /etc/xdg/openbox/autostart
echo 'xset -dpms' >> /etc/xdg/openbox/autostart
echo '' >> /etc/xdg/openbox/autostart
echo '#Start the shell script we already wrote in our home directory' >> /etc/xdg/openbox/autostart
echo '#Runloop restarts the graphic console (new access token, new config file)' >> /etc/xdg/openbox/autostart
echo '#if the session is terminated (i.e the VM is inaccessible or restarts)' >> /etc/xdg/openbox/autostart
echo '#User will see a black screen with a cursor during this process' >> /etc/xdg/openbox/autostart
echo 'while true' >> /etc/xdg/openbox/autostart
echo 'do' >> /etc/xdg/openbox/autostart
echo '~/connexion2spice.sh' >> /etc/xdg/openbox/autostart
echo 'done' >> /etc/xdg/openbox/autostart

echo 'startx --' >> /home/$username/.profile

# Ecriture du script montageDisk - Import du disque stocké sur la machine stockage et branchement sur la VM
echo "sudo qm stop 205" > /home/$username/MontageDisk.sh
echo "sudo qm set 205 --delete scsi0" >> /home/$username/MontageDisk.sh
echo "#Creation du partage via NFS" >> /home/$username/MontageDisk.sh
echo "sudo mount $ipstockage:/images /mnt/stockage/" >> /home/$username/MontageDisk.sh 
echo "#Importation du disque format .raw vers la machine cible" >> /home/$username/MontageDisk.sh
echo "sudo qm disk import 205 /mnt/stockage/\$1 local-lvm --format raw">> /home/$username/MontageDisk.sh >> /home/$username/MontageDisk.sh
echo "#Connecte le disque sur Proxmox" >> /home/$username/MontageDisk.sh
echo "sudo qm set 205 --scsi0 local-lvm:vm-205-disk-0" >> /home/$username/MontageDisk.sh
echo "sudo qm set 205 -boot order='scsi0;net0'" >> /home/$username/MontageDisk.sh
echo "#démontage du disque" >> /home/$username/MontageDisk.sh
echo "sudo umount /mnt/stockage/" >> /home/$username/MontageDisk.sh
chown $username:$username /home/$username/MontageDisk.sh
chmod +x /home/$username/MontageDisk.sh


# Ecriture du script de renommage de la machine virtuelle placer dans le répertoire /root
echo '#!/bin/bash' > /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#Nom du disque :' >> /home/$username/RenommageMachine.sh
echo 'diskname=$(sudo fdisk -l | grep "pve" | grep "part" | grep "[0-9]G" | sed "s/^.*\(pve.*\)-part.*$/\1 /")' >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#premier secteur :' >> /home/$username/RenommageMachine.sh
echo 'firstsector=$(sudo fdisk -l | grep "pve" | grep "part" | grep "[0-9]G" | sed "s/^.*  \([0-9][0-9][0-9]*\) .*$/\1 /")' >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#taille des secteurs :' >> /home/$username/RenommageMachine.sh
echo 'sectorsize=$(sudo fdisk -l | grep -2 $diskname | grep "Units: sectors" | sed "s/^Units.*= \([0-9][0-9][0-9]*\).*$/\1/")' >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#### Montage du disque' >> /home/$username/RenommageMachine.sh
echo "sudo mount -o loop,offset=\$((\$firstsector * \$sectorsize)) /dev/mapper/\$diskname /home/$username/PROJECTEST" >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#### Fichier nom de la machine' >> /home/$username/RenommageMachine.sh
echo "machinename=\$(cat /home/$username/NAME)" >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#### Exportation des clés REGEDIT' >> /home/$username/RenommageMachine.sh
echo "sudo reged -x /home/$username/PROJECTEST/Windows/System32/config/SYSTEM HKEY_LOCAL_MACHINE\\\\SYSTEM ControlSet001\\\\Services\\\\Tcpip\\\\Parameters /home/$username/KEY1.reg" >> /home/$username/RenommageMachine.sh
echo "sudo reged -x /home/$username/PROJECTEST/Windows/System32/config/SYSTEM HKEY_LOCAL_MACHINE\\\\SYSTEM ControlSet001\\\\Control\\\\ComputerName /home/$username/KEY2.reg" >> /home/$username/RenommageMachine.sh
echo ''  >> /home/$username/RenommageMachine.sh
echo '#### Recupération du nom de la machine a remplacer' >> /home/$username/RenommageMachine.sh
echo "oldname=\$(grep \"NV Hostname\" /home/$username/KEY1.reg | sed 's/^.*=\"\(.*\)\"/\1/')" >> /home/$username/RenommageMachine.sh
echo "oldname=\${oldname//$'\\r'}" >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#### Modification des CLES KEY1.reg & KEY2.reg' >> /home/$username/RenommageMachine.sh
echo "sed -i \"s/\$oldname/\$machinename/g\" /home/$username/KEY1.reg" >> /home/$username/RenommageMachine.sh
echo "oldname=\$(grep \"\\\"ComputerName\\\"\" /home/$username/KEY2.reg | sed 's/^.*=\"\(.*\)\"/\1/')" >> /home/$username/RenommageMachine.sh
echo "oldname=\${oldname//$'\\r'}" >> /home/$username/RenommageMachine.sh
echo "sed -i \"s/\$oldname/\$machinename/g\" /home/$username/KEY2.reg" >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '#### Importation des clés' >> /home/$username/RenommageMachine.sh
echo "sudo reged -I -C /home/$username/PROJECTEST/Windows/System32/config/SYSTEM HKEY_LOCAL_MACHINE\\\\SYSTEM /home/$username/KEY1.reg" >> /home/$username/RenommageMachine.sh
echo "sudo reged -I -C /home/$username/PROJECTEST/Windows/System32/config/SYSTEM HKEY_LOCAL_MACHINE\\\\SYSTEM /home/$username/KEY2.reg" >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
echo '### Démontage du disque' >> /home/$username/RenommageMachine.sh
echo "sudo umount /home/$username/PROJECTEST" >> /home/$username/RenommageMachine.sh
echo '' >> /home/$username/RenommageMachine.sh
chown $username:$username /home/$username/RenommageMachine.sh
chmod +x /home/$username/RenommageMachine.sh


#info=$(dmidecode | grep -A8 "System Information")
nbcpu=$(cat /proc/cpuinfo | grep -i "^processor" | wc -l)
memory=$(dmidecode | grep -A6 "^Memory Array Mapped Address" | grep "Range Size" | grep -o "[0-9]\+")
let memory=1024*$memory
cat /sys/firmware/acpi/tables/MSDM > /root/MSDM.bin

manufb64=$(dmidecode | grep -A8 "System Information"| grep Manufacturer | sed 's/^.*Manufacturer: //' | tr -d '\n' |base64)
productb64=$(dmidecode | grep -A8 "System Information"| grep "Product Name" | sed 's/^.*Product Name: //' | tr -d '\n' |base64)
versionb64=$(dmidecode | grep -A8 "System Information"| grep Version | sed 's/^.*Version: //' | tr -d '\n' |base64)
serialb64=$(dmidecode | grep -A8 "System Information"| grep "Serial Number" | sed 's/^.*Serial Number: //' | tr -d '\n' |base64)
UUID=$(dmidecode | grep -A8 "System Information"| grep UUID | sed 's/^.*UUID: //')
SKUb64=$(dmidecode | grep -A8 "System Information"| grep "SKU Number" | sed 's/^.*SKU Number: //' | tr -d '\n' |base64)
familyb64=$(dmidecode | grep -A8 "System Information"| grep Family | sed 's/^.*Family: //' | tr -d '\n' |base64)

#recuperation de l'addresse mac de la machine pour attribuer la fin a celui de la vm ex: @mac machine da:fe:qs:la:fi:nn @vm fa:ca:de:la:fi:nn
ifname=$(ls /sys/class/net/ | grep enp)
phymac=$(cat /sys/class/net/$ifname/address)
mac=$(echo $phymac | sed 's/^..:..:../fa:ca:de/')

#echo qm create 205 --sockets 1 --cores $nbcpu --memory $memory --net0 virtio,bridge=vmbr0,firewall=1,macaddr=$mac --agent 1 --balloon $memory --name testcli --onboot 1 --scsihw virtio-scsi-single --ostype win10 --vga virtio-gl --boot order=\'scsi0;ide2;net0\' --smbios1 uuid=$UUID,manufacturer=$manufb64,product=$productb64,version=$versionb64,serial=$serialb64,sku=$SKUb64,family=$familyb64,base64=1 --args \'-acpitable file=/root/MSDM.bin\'

qm create 205 --sockets 1 --cores $nbcpu --memory $memory --net0 virtio,bridge=vmbr0,firewall=1,macaddr=$mac --agent 1 --balloon $memory --name testcli --onboot 1 --scsihw virtio-scsi-pci --ostype win10 --vga qxl --boot order='scsi0;ide2;net0' --smbios1 uuid=$UUID,manufacturer=$manufb64,product=$productb64,version=$versionb64,serial=$serialb64,sku=$SKUb64,family=$familyb64,base64=1 --args '-acpitable file=/root/MSDM.bin'


/home/$username/MontageDisk.sh $DisqueChargement

# Lancement script renommageMachine
sudo -u $username /home/$username/RenommageMachine.sh


#Changement ip static en dynamique
sed -ri "s/iface vmbr0 inet static/iface vmbr0 inet dhcp/g" /etc/network/interfaces
sed -ri "s/address.*//g" /etc/network/interfaces
sed -ri "s/getway.*//g" /etc/network/interfaces


reboot now
