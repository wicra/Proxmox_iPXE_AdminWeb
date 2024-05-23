<?php

// Fichier de log DHCP
$LEASES_FILE = "dhcpd.leases";
// Fichier de configuration DHCP
$DHCP_CONF = "dhcpd.conf";
// Ligne à partir de laquelle commencer à écrire les hôtes dans le fichier dhcpd.conf
$start_line = 69;

// Extraction des adresses MAC des leases DHCP
$mac_addresses = [];
exec("grep 'hardware ethernet' $LEASES_FILE | awk '{print $3}' | sort | uniq", $mac_addresses);

// Fonction pour générer une adresse IP unique
function generate_unique_ip() {
    $ip = "10.10.62." . rand(2, 159);
    return $ip;
}

// Stocker les lignes actuelles du fichier dhcpd.conf
$current_config_lines = file($DHCP_CONF);

// Compteur pour suivre les lignes parcourues
$current_line = 0;

// Parcourir les lignes du fichier dhcpd.conf
foreach ($current_config_lines as $line) {
    // Incrémenter le compteur de lignes
    $current_line++;
    // Si on atteint la ligne à partir de laquelle commencer à écrire les hôtes
    if ($current_line == $start_line) {
        // Ajouter les adresses MAC au fichier de configuration DHCP
        foreach ($mac_addresses as $mac) {
            // Récupérer le nom d'hôte associé à l'adresse MAC depuis le leases DHCP
            exec("grep -B 5 \"$mac\" $LEASES_FILE | grep 'client-hostname' | awk '{print $2}'", $hostname_output);
            $hostname = isset($hostname_output[0]) ? $hostname_output[0] : "";
            if (empty($hostname)) {
                echo "Aucun nom d'hôte trouvé pour l'adresse MAC $mac, attribution d'un nom aléatoire";
                // Générer un nom d'hôte aléatoire
                $hostname = "machine_" . rand(1, 100);
            } else {
                // Vérifier si le nom d'hôte contient des caractères spéciaux et les remplacer par _
                $hostname = preg_replace("/[^A-Za-z0-9_]/", "_", $hostname);
            }
            // Générer une adresse IP fixe
            $ip_address = generate_unique_ip();

            // Ajouter l'entrée au tableau des lignes actuelles du fichier dhcpd.conf
            $entry = "host $hostname {\n";
            $entry .= "    hardware ethernet $mac;\n";
            $entry .= "    fixed-address $ip_address;\n";
            // Ajouter la partie PXE Boot
            $entry .= "    # PXE Boot\n";
            $entry .= "    if option arch = 00:07 or option arch = 00:09 {\n";
            $entry .= "        if exists user-class and option user-class = \"iPXE\" {\n";
            $entry .= "            filename \"http://10.10.62.210/menu_known2.ipxe\";\n";
            $entry .= "        } else {\n";
            $entry .= "            filename \"ipxe/ipxe.efi\";\n";
            $entry .= "        }\n";
            $entry .= "    }\n";
            $entry .= "    else if option arch = 00:06 {\n";
            $entry .= "        if exists user-class and option user-class = \"iPXE\" {\n";
            $entry .= "            filename \"http://10.10.62.210/menu_known2.ipxe\";\n";
            $entry .= "        } else {\n";
            $entry .= "            filename \"ipxe/ipxe32.efi\";\n";
            $entry .= "        }\n";
            $entry .= "    }\n";
            $entry .= "    else {\n";
            $entry .= "        if exists user-class and option user-class = \"iPXE\" {\n";
            $entry .= "            filename \"http://10.10.62.210/menu_known2.ipxe\";\n";
            $entry .= "        } else {\n";
            $entry .= "            filename \"undionly.kpxe\";\n";
            $entry .= "        }\n";
            $entry .= "    }\n";
            $entry .= "}\n\n";
            array_splice($current_config_lines, $current_line, 0, $entry); // Insérer l'entrée à la ligne actuelle
            // Incrémenter le compteur de lignes
            $current_line++;
        }
    }
}

// Réécrire le fichier dhcpd.conf avec les lignes actuelles et les nouvelles entrées d'hôte
file_put_contents($DHCP_CONF, implode("", $current_config_lines));

// Redémarrage du service DHCP
exec("systemctl restart isc-dhcp-server");

?>
