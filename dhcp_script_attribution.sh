#!/bin/bash

#####################################
# /etc/dhcp/dhcpd_attribution.sh #
####################################



# Variable de lecture du fichier des logs d'attribution
LEASES_FILE="/var/lib/dhcp/dhcpd.leases"
RESERVATIONS_FILE="/etc/dhcp/dhcpd_reservations.conf"

# Fonction pour analyser les baux DHCP et générer des réservations
parse_leases() {
    awk '
    BEGIN { lease_ip=""; mac=""; hostname=""; }
    /lease / { lease_ip=$2; }
    /hardware ethernet/ { mac=$3; }
    /client-hostname/ { gsub(/"/, "", $2); hostname=$2; }
    {
        if (lease_ip != "" && mac != "") {
            printf("host %s {\n  hardware ethernet %s;\n  fixed-address %s;\n}\n", (hostname != "" ? hostname : mac), mac, lease_ip);
            lease_ip=""; mac=""; hostname="";
        }
    }
    ' $LEASES_FILE
}

# Générer les réservations et les sauvegarder dans un fichier séparé
generate_reservations() {
    parse_leases > $RESERVATIONS_FILE
}

# Inclure le fichier de réservations dans le fichier de configuration DHCP
include_reservations() {
    DHCP_CONF="/etc/dhcp/dhcpd.conf"
    if ! grep -q "include \"$RESERVATIONS_FILE\";" $DHCP_CONF; then
        echo "include \"$RESERVATIONS_FILE\";" >> $DHCP_CONF
    fi
}

# Générer les réservations et les inclure
generate_reservations
include_reservations
