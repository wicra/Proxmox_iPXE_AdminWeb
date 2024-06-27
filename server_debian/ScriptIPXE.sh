#!/bin/bash

##############################################
#                                            #
#            installation iPXE               #
#                                            #
##############################################

##############################################
#                    DHCP                    #
##############################################

apt -y install ipcalc
apt -y install nmap

# Installation du service DHCP
apt -y install isc-dhcp-server
dnsIP=$(cat /etc/resolv.conf | sed "s/^nameserver //")
RouteIP=$(ip route | grep "^[0-9]." | sed "s/\(^.*\) dev.*/\1/")
Mask=$(ipcalc -b $RouteIP | grep Netmask | sed "s/^.*:   \([0-9]*.[0-9]*.[0-9]*.[0-9]*\) .*$/\1/")
ipmachine=$(ip route | grep "^[0-9]." | sed "s/^.* src //" | sed "s/\s[a-z]*//")
interfaceNetwork=$(ip route | grep "^[0-9]." | sed "s/^.* dev //" | sed "s/\s[a-z]*.*//")
gateway=$(ip route | grep "default" | sed "s/^.* via //" | sed "s/\s[a-z]*.*//")
ipreseaux=$(ipcalc -b $RouteIP | grep "Address" | sed "s/Address:   //")
broadcast=$(ipcalc -b $RouteIP | grep "Broadcast" | sed "s/Broadcast: //")
ipip=$(echo $ipmachine | sed "s/^.*\.\([0-9]*\)$/\1/")

# Modification du fichier /etc/dhcp/dhcpd.conf #
sed -i "s/option subnet-mask 255.255.255.0;/option subnet-mask $Mask;/g" /root/dhcpd.conf
sed -i "s/subnet 10.10.62.0 netmask 255.255.255.0/subnet $ipreseaux netmask $Mask/g" /root/dhcpd.conf
sed -i "s/option broadcast-address 10.10.62.255;/option broadcast-address $broadcast;/g" /root/dhcpd.conf
sed -i "s/option routers 10.10.62.254;/option routers $gateway;/g" /root/dhcpd.conf
sed -i "s/option domain-name-servers 8.8.8.8, 1.1.1.1;/option domain-name-servers $dnsIP;/g" /root/dhcpd.conf
sed -i "s/next-server 10.10.62.29;/next-server $ipmachine;/g" /root/dhcpd.conf

sed -i "s/filename \"http:\/\/10.10.62.29\/install.ipxe\";/filename \"http:\/\/$ipmachine\/install.ipxe\";/g" /root/dhcpd.conf
sed -i "s/filename \"http:\/\/10.10.62.29\/boot_choix.ipxe\";/filename \"http:\/\/$ipmachine\/boot_choix.ipxe\";/g" /root/condition_pxe_boot_choix.conf
sed -i "s/filename \"http:\/\/10.10.62.29\/boot_local.ipxe\";/filename \"http:\/\/$ipmachine\/boot_local.ipxe\";/g" /root/condition_pxe_boot_local.conf
sed -i "s/filename \"http:\/\/10.10.62.29\/install.ipxe\";/filename \"http:\/\/$ipmachine\/install.ipxe\";/g" /root/condition_pxe_boot_unknown.conf

sed -i "s/INTERFACESv4=\"\"/INTERFACESv4=\"$interfaceNetwork\"/g" /etc/default/isc-dhcp-server

Lastip=$(echo $(($ipip+50)))
if [[ $Lastip -lt 253 ]];
then 
        Firstip=$(echo $(($ipip+1)))
else
        Lastip=$(echo $(($ipip-1)))
        Firstip=$(echo $(($ipip-50)))
fi

debutip=$(echo $ipreseaux | sed "s/\.0$/\./")
Firstip="$debutip""$Firstip"
Lastip="$debutip""$Lastip"

#read -p "Saisissez la première adresse IP du pool DHCP" FirstIP
#read -p "Saisissez la dernière adresse IP du pool DHCP" LastIP


echo "range $Firstip $Lastip;" > /root/dhcpd_range.conf
cp /root/dhcpd_range.conf /etc/dhcp/
cp /root/add_boot_ipxe_unknown.conf /etc/dhcp/
cp /root/dhcpd.conf /etc/dhcp/
cp /root/dhcpd_hosts.conf /etc/dhcp/
cp /root/condition_pxe_boot_choix.conf /etc/dhcp/ 
cp /root/condition_pxe_boot_local.conf /etc/dhcp/
cp /root/condition_pxe_boot_unknown.conf /etc/dhcp/
chmod +w /etc/dhcp/*

#changer de proprietaire de dhcpd_range.conf
chown www-data:www-data /etc/dhcp/dhcpd_range.conf
chown www-data:www-data /etc/dhcp/add_boot_ipxe_unknown.conf

# Redémarrer le services DHCP
systemctl restart isc-dhcp-server

##############################################
#                    TFTP                    #
##############################################

# Installation du serveur TFTP #
apt -y install tftpd-hpa 

# Modification du fichier /etc/default/tftpd-hpa #
sed -i "s/TFTP_ADDRESS=\"10.10.62.29:69\"/TFTP_ADDRESS=\"$ipmachine:69\"/g" /root/tftpd-hpa
cp /root/tftpd-hpa /etc/default/

# creer le repertoire tftpboot #
mkdir /var/lib/tftpboot
mkdir /var/lib/tftpboot/ipxe
# /Redémarrer le service TFTP #
systemctl restart tftpd-hpa

# Configuration WGET
aptproxy=$(cat /etc/apt/apt.conf | grep "Proxy")


if [ -z "${aptproxy}" ]; then 
	echo vide;
	else
	aptproxy=$(cat /etc/apt/apt.conf | grep "Proxy" | sed "s/^.*\"\(.*\)\".*/\1/" )/;

        sed -i -r "s@.*#https_proxy = http.*@https_proxy = $aptproxy@g" /etc/wgetrc
        sed -i -r "s@.*#http_proxy = http.*@http_proxy = $aptproxy@g" /etc/wgetrc
        sed -i -r "s@.*#ftp_proxy = http.*@ftp_proxy = $aptproxy@g" /etc/wgetrc
fi

##############################################
#                    NFS                     #
##############################################

# Installation du server NFS #
apt -y install nfs-kernel-server

# Installation d'un server LAMP pour le iPXE #
apt -y install apache2 php libapache2-mod-php php-mysql php-curl php-gd php-intl php-json php-mbstring php-xml php-zip

# Création d'un lien symbolique de TFTPBOOT vers le server LAMP
ln -s /var/lib/tftpboot /var/www/html/tftpboot 

# Préparation des fichiers de démarrage iPXE # 
cd /var/lib/tftpboot
wget http://boot.ipxe.org/undionly.kpxe
cd /var/lib/tftpboot/ipxe
wget http://boot.ipxe.org/ipxe.efi


# Modification du fichier iPXE - /var/www/html/install.ipxe
sed -i "s/set serverip http:\/\/10.10.62.29/set serverip http:\/\/$ipmachine/g" /root/install.ipxe
sed -i "s/set serverip http:\/\/10.10.62.29/set serverip http:\/\/$ipmachine/g" /root/boot_choix.ipxe
sed -i "s/set serverip http:\/\/10.10.62.29/set serverip http:\/\/$ipmachine/g" /root/boot_local.ipxe
cp /root/install.ipxe /var/www/html/
cp /root/boot_choix.ipxe /var/www/html/
cp /root/boot_local.ipxe /var/www/html/

###############################
#           Script            # 
#        Machine              #
#            Stockage         # 
###############################


# Installation de "NFS-KERNEL-SERVER"
# apt install nfs-kernel-server

# Création du répertoire "/images" et changement du propriétaire en "modele"
mkdir /images
chown modele:modele /images

# Modification du fichier /etc/exports
echo "/images *(rw,sync,no_subtree_check)" >> /etc/exports

# Fichier initrd & Linux26 a déplacer dans /var/www/proxmox
mkdir /var/www/html/proxmox
cp /root/initrd /var/www/html/proxmox
cp /root/linux26 /var/www/html/proxmox

# Redémarrer le services NFS
systemctl restart nfs-kernel-server



###############################
#      Deplacement site       # 
###############################

#insatallation de sudo et conig www-data pour les droits d'execution du site avec les commandes autorisé
apt install sudo
echo "www-data ALL=(ALL) NOPASSWD: /usr/bin/chown modele:modele /image/*,/usr/bin/nmap,/usr/bin/ls,/usr/bin/mv /var/www/html/AdminWeb/upload_new_disk_tmp/* /images/,/usr/bin/systemctl restart isc-dhcp-server,/usr/bin/systemctl stop isc-dhcp-server,/usr/bin/systemctl is-active isc-dhcp-server" >> /etc/sudoers
chown www-data:www-data /etc/dhcp/dhcpd_hosts.conf

#deplacement du site et changer les droits
cp -r /root/AdminWeb/ /var/www/html/

###############################
#      Modif droit ipScan     # 
###############################
chmod 555 /var/www/html/AdminWeb/shell/ipScan.sh
chmod 777 /var/www/html/AdminWeb/shell/ipScan.txt

###############################
#      Modif droit diskScan   # 
###############################
chmod 555 /var/www/html/AdminWeb/shell/diskScan.sh
chmod 777 /var/www/html/AdminWeb/shell/diskScan.txt

###############################
#   Modif droit boot , stop   #
#    status dhcp              # 
###############################
chmod 555 /var/www/html/AdminWeb/shell/boot_server_dhcp.sh
chmod 555 /var/www/html/AdminWeb/shell/stop_server_dhcp.sh
chmod 555 /var/www/html/AdminWeb/shell/status_server_dhcp.sh

##################################
# Modif propte range_ip_fixe.php # 
##################################
chown www-data:www-data /var/www/html/AdminWeb/interface/include/range_ip_fixe.php

##################################
#  Modif droit script ajout disk # 
##################################
chmod 555 /var/www/html/AdminWeb/interface/conf/conf_upload_new_disk.php
chmod 555 /var/www/html/AdminWeb/shell/upload_new_disk.sh
chown www-data:www-data /var/www/html/AdminWeb/ipload_new_disk_tmp

##################################
# Modif proprietaire dhcpd.leases # 
##################################
#chown www-data:www-data /var/lib/dhcp/dhcpd.leases

##################################
#       Modif droit login       # 
##################################
chown www-data:www-data /var/www/html/AdminWeb/interface/connexion/users.env.php
chmod 644 /var/www/html/AdminWeb/interface/connexion/users.env.php

########################################
#  Configuration de la connexion SSH  # 
########################################
# Générer une paire de clés SSH avec les paramètres par défaut
ssh-keygen -t rsa -b 4096 -N "" -f ~/.ssh/id_rsa

# Ca permet de ne pas avoir le pass phrase au premier connexion
echo "Host *" > /root/.ssh/config
echo "  StrictHostKeyChecking no" >> /root/.ssh/config

# Lire le contenu de la clé publique
SSH_KEY=$(cat ~/.ssh/id_rsa.pub)

# Créer le fichier answer.toml avec le contenu souhaité
cat <<EOT > /var/www/html/proxmox/answer.toml
[global]
keyboard = "fr"
country = "fr"
fqdn = "teste.fr"
mailto = "exemple@gmail.com"
timezone = "Europe/Paris"
root_password = "Password"
root_ssh_keys = [
    "$SSH_KEY"
]

[network]
source = "from-dhcp"

[disk-setup]
filesystem = "ext4"
disk_list = ["nvme0n1"]
EOT


