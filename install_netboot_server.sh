#!/bin/bash

####################################################
# Ce script installe et configure les services 
# nécessaires pour mettre en place un serveur 
# de démarrage réseau avec DHCP, TFTP, NFS et iPXE,
# et prépare également un serveur LAMP pour servir
# les fichiers iPXE.
#####################################################

######################
# Installation iPXE  #
######################

apt -y install ipcalc

############
#   DHCP   #
############

# Installation du service DHCP
apt -y install isc-dhcp-server
ipcalc `ip route | grep -v "$GW dev" | sed -n '/^default/{s/.* dev //;p}'` > /root/network
routeIP=$(ipcalc -p $ROUTEIP | grep 'Address' | sed 's/Address: //')
mask=$(ipcalc -p $ROUTEIP | grep 'Netmask' | sed 's/Netmask: //')
broadcast=$(ipcalc -p $ROUTEIP | grep 'Broadcast' | sed 's/Broadcast: //')
ip=echo $(ipcalc -b $ROUTEIP | grep 'Network' | sed 's/Network: //')

# Modification du fichier /etc/dhcp/dhcpd.conf
sed -i 's/#option subnet-mask 255.255.255.0;/option subnet-mask $mask;/g' /root/dhcpd.conf
sed -i 's/#option broadcast-address 255.255.255.255;/option broadcast-address $broadcast;/g' /root/dhcpd.conf
sed -i 's/#option domain-name-servers 8.8.8.8, 1.1.1.1;/option domain-name-servers $dnsIP;/g' /root/dhcpd.conf
sed -i 's/#filename "pxelinux.0";/filename "http:\/\/\$ipmachine\/install.ipxe";/g' /root/dhcpd.conf
sed -i 's/INTERFACESv4=""/INTERFACESv4="$interfaceNetwork"/g' /etc/default/isc-dhcp-server

Lastip=$(echo $(ip -o -f inet addr show | awk '/scope global/ {print $4}' | sed -n 's/^.*\([0-9]*\/\).*/\1/p') | awk '{print $2}')
if [[ $Lastip -lt 253 ]]; then
    Firstip=$(echo $(($ip + 1)))
else
    Firstip=$(echo $(($ip + 10)))
fi
debutsip=$(echo $ipreseaux | sed 's/.\{1\}$//')
Lastip=$debutsip$Lastip
Firstip=$debutsip$Firstip

echo "Saisissez la première adresse IP du pool DHCP" FirstIP
echo "Saisissez la dernière adresse IP du pool DHCP" LastIP

sed -i 's/range 10.0.2.15 10.0.2.255;/range $Firstip $Lastip;/g' /root/dhcpd.conf

# Redémarrer le service DHCP
systemctl restart isc-dhcp-server

############
#   TFTP   #
############

# Installation du serveur TFTP
apt -y install tftpd-hpa

############
#   NFS    #
############

# Installation du server NFS
apt -y install nfs-kernel-server

# Installation d'un server LAMP pour le iPXE
apt -y install apache2 php libapache2-mod-php php-mysql php-curl php-gd php-intl php-json php-mbstring php-xml php-zip

# Création d'un lien symbolique de TFTBOOT vers le serveur LAMP
ln -s /var/lib/tftpboot /var/www/html/tftpboot

# Préparation des fichiers de démarrage iPXE
cd /var/lib/tftpboot
wget http://boot.ipxe.org/undionly.kpxe
wget http://boot.ipxe.org/ipxe.efi

# Modification du fichier iPXE
sed -i 's#.*#set server http://10.10.62.199/boot.ipxe#g' /root/install.ipxe
cp /root/install.ipxe /var/www/html/

##########################
# Script Machine Stockage #
##########################

# Installation de NFS-KERNEL-SERVER
apt install nfs-kernel-server

# Création du répertoire /images et changement du propriétaire en "modele"
mkdir /images
chown modele:modele /images

# Modification du fichier /etc/exports
echo "/images *(rw,sync,no_subtree_check)" >> /etc/exports

# Fichier initrd & Linux26 à déplacer dans /var/www/proxmox
mkdir /var/www/proxmox
cp -r initrd* linux26* /var/www/proxmox

# Redémarrer le service NFS
systemctl restart nfs-kernel-server
