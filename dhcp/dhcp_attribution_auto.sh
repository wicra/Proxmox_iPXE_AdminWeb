#!/bin/bash

# Fichier de log DHCP
#LEASES_FILE="/var/lib/dhcp/dhcpd.leases"
LEASES_FILE="dhcpd.leases"

# Extraction des adresses MAC des leases DHCP
grep "hardware ethernet" $LEASES_FILE | awk '{print $3}' | sort | uniq > /tmp/mac_addresses.txt

# Déclarer un tableau pour suivre les adresses IP attribuées
declare -A assigned_ips

# Fonction pour générer une adresse IP unique
generate_unique_ip() {
    local ip
    while :; do
        ip="10.10.62.$(shuf -i 2-159 -n 1)"
        if [ -z "${assigned_ips[$ip]}" ]; then
            assigned_ips[$ip]=1
            echo "$ip"
            return
        fi
    done
}

# Ajout des adresses MAC au fichier de configuration DHCP
while read -r mac; do
    # Récupérer le nom d'hôte associé à l'adresse MAC depuis le leases DHCP
    hostname=$(grep -B 5 "$mac" $LEASES_FILE | grep "client-hostname" | awk '{print $2}')
    if [ -z "$hostname" ]; then
        echo "Aucun nom d'hôte trouvé pour l'adresse MAC $mac, attribution d'un nom aléatoire"
        # Générer un nom d'hôte aléatoire
        hostname="machine_$(shuf -i 1-100 -n 1)"
    else
        # Vérifier si le nom d'hôte contient des caractères spéciaux et les remplacer par _
        hostname=$(echo $hostname | tr -c '[:alnum:]' '_')
    fi
    # Générer une adresse IP fixe
    ip_address=$(generate_unique_ip)

    # Ajouter l'entrée au fichier dhcpd.conf
    echo "host $hostname {" >> $DHCP_CONF
    echo "    hardware ethernet $mac;" >> $DHCP_CONF
    echo "    fixed-address $ip_address;" >> $DHCP_CONF
    echo "}" >> $DHCP_CONF
    echo "" >> $DHCP_CONF
done < /tmp/mac_addresses.txt 

# Redémarrage du service DHCP
systemctl restart isc-dhcp-server
