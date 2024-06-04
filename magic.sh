#!/bin/bash

#installation des servers iPXE
echo "Install tout les services iPXE"
chmod +x ./server_debian/install_ipxe_all_service.sh
bash /server_debian/install_ipxe_all_service.sh
echo "installation terminer"

#deplacement des fichiers dhcp
echo "deplacement dhcp"
cp /dhcp/dhcpd.conf /etc/dhcp/
cp /dhcp/dhcpd_hosts.conf /etc/dhcp/
cp /dhcp/condition_pxe_boot_choix.conf /etc/dhcp/ 
cp /dhcp/condition_pxe_boot_local.conf /etc/dhcp/
echo "deplacement termirné"

#deplacer les menus ipxe
echo "deplacement ipxe"
cp /ipxe/install.ipxe /var/www/html/
cp /ipxe/boot_choix.ipxe /var/www/html/
cp /ipxe/boot_local.ipxe /var/www/html/
echo "deplacement ipxe termirné"

#insatallation de sudo et conig www-data pour les droits d'execution du site
echo "installation de sudo" 
sudo apt update
sudo apt install sudo
echo "install fini"

echo "configuration de sudo et WWW-data"
echo "www-data ALL = (ALL) NOPASSWD : /usr/bin/nmap" >> /etc/sudoers
echo "config www-data terminer"

#deplacement du site et changer les droits
echo "deplacement du site"
cp -r /site_v3/ /var/www/html/
echo "changement de droit"
sudo chown -R www-data:www-data /var/www/html/site_v3
echo "deplacement du site fini"

#telecharger l'iso original de prox
echo "telechargement d'iso original"
wget "https://proxmox.com/en/downloads/proxmox-virtual-environment/iso/proxmox-ve-8-2-iso-installer"
echo "telechargement réussi"

#Crée l'image proxmox l'image proxmox 
echo "création de l'image proxmox"
chmod +x /conf_proxmox/conf_iso_proxmox_v1.sh
bash /conf_proxmox/conf_iso_proxmox_v1.sh proxmox-ve_8.2-1.iso

#déplacer l'image
echo "deplacement de l'image"
mkdir proxmox /var/www/html/
cp /conf_proxmox/pxeboot/initrd /var/www/html/proxmox/
cp /conf_proxmox/pxeboot/linux26 /var/www/html/proxmox/
echo "deplacement fait"




