<?php

// Fichier de log DHCP
$LEASES_FILE = "dhcpd.leases";
// Fichier de configuration DHCP
$DHCP_CONF = "dhcpd_hosts.conf";

// Date à partir de laquelle commencer à traiter (au format Y/m/d H:i:s)
$start_date = strtotime("2024/02/00 00:00:00");

// Vérification de l'existence des fichiers
if (!file_exists($LEASES_FILE)) {
    die("Erreur : Le fichier $LEASES_FILE n'existe pas.\n");
}
if (!file_exists($DHCP_CONF)) {
    die("Erreur : Le fichier $DHCP_CONF n'existe pas.\n");
}

// Extraction des adresses MAC des leases DHCP
$mac_addresses = [];
exec("grep 'hardware ethernet' $LEASES_FILE | awk '{print $3}' | sort | uniq", $mac_addresses);

if (empty($mac_addresses)) {
    die("Erreur : Aucune adresse MAC trouvée dans le fichier $LEASES_FILE.\n");
}

// Fonction pour générer une adresse IP unique
function generate_unique_ip() {
    $ip = "10.10.62." . rand(2, 159);
    return $ip;
}

// Stocker les lignes actuelles du fichier dhcpd_host.conf
$current_config_lines = file($DHCP_CONF);

if ($current_config_lines === false) {
    die("Erreur : Impossible de lire le fichier $DHCP_CONF.\n");
}

// Compteur pour suivre les lignes parcourues
$current_line = 0;
$entries_added = 0;

// Parcourir les lignes du fichier dhcpd_host.conf
foreach ($current_config_lines as $line) {
    // Si une ligne commence par "host", augmenter le compteur d'entrées ajoutées
    if (strpos(trim($line), "host") === 0) {
        $entries_added++;
    }
}

// Si aucune entrée n'a été ajoutée, commencer à écrire à la fin du fichier
if ($entries_added == 0) {
    $current_config_lines[] = "\n"; // Ajouter une ligne vide pour la lisibilité
}

// Ajouter les adresses MAC au fichier de configuration DHCP
foreach ($mac_addresses as $mac) {
    // Récupérer les informations de lease pour cette adresse MAC
    exec("grep -B 8 \"$mac\" $LEASES_FILE", $lease_info);

    // Extraire la date de début du lease
    preg_match('/starts \d+ (\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2});/', implode("\n", $lease_info), $matches);
    $start_time = strtotime($matches[1]);

    // Vérifier si la date de début est postérieure à la date spécifiée
    if ($start_time >= $start_date) {
        // Récupérer le nom d'hôte associé à l'adresse MAC depuis le leases DHCP
        exec("grep -B 5 \"$mac\" $LEASES_FILE | grep 'client-hostname' | awk '{print $2}'", $hostname_output);
        $hostname = isset($hostname_output[0]) ? $hostname_output[0] : "";
        if (empty($hostname)) {
            echo "Aucun nom d'hôte trouvé pour l'adresse MAC $mac, attribution d'un nom aléatoire\n";
            // Générer un nom d'hôte aléatoire
            $hostname = "machine_" . rand(1, 100);
        } else {
            // Vérifier si le nom d'hôte contient des caractères spéciaux et les remplacer par _
            $hostname = preg_replace("/[^A-Za-z0-9_]/", "_", $hostname);
        }
        // Générer une adresse IP fixe
        $ip_address = generate_unique_ip();

        // Ajouter l'entrée au tableau des lignes actuelles du fichier dhcpd_host.conf
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
        $current_config_lines[] = $entry;
        $entries_added++;
        echo "Ajout de l'entrée pour MAC $mac avec hostname $hostname et IP $ip_address\n";
    }
}

// Réécrire le fichier dhcpd_host.conf avec les lignes actuelles et les nouvelles entrées d'hôte
if (file_put_contents($DHCP_CONF, implode("", $current_config_lines)) === false) {
    die("Erreur : Impossible d'écrire dans le fichier $DHCP_CONF.\n");
}
echo "$entries_added entrées ajoutées au fichier $DHCP_CONF.\n";



?>
