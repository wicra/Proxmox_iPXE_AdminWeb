<?php

// Lire le contenu du fichier
$filename = '../../../dhcp/dhcpd_hosts.conf'; // Remplacez par le chemin de votre fichier
$file_content = file_get_contents($filename);

if ($file_content === false) {
    die("Erreur de lecture du fichier $filename\n");
}

// Extraire les blocs de hosts
preg_match_all('/host\s+[^\}]+\}/', $file_content, $matches);
$hosts = $matches[0];

if (empty($hosts)) {
    die("Aucun bloc 'host' trouvé dans le fichier $filename\n");
}

// Fonction pour obtenir la fin de l'adresse MAC
function getMacEnding($mac) {
    $parts = explode(':', $mac);
    return end($parts);
}

// Tableau associatif pour stocker les hosts par fin d'adresse MAC
$hosts_by_mac_end = [];

foreach ($hosts as $host) {
    preg_match('/hardware ethernet ([^;]+);/', $host, $mac_match);
    
    if (empty($mac_match)) {
        echo "Adresse MAC non trouvée pour le host suivant:\n$host\n";
        continue;
    }

    $mac_end = getMacEnding($mac_match[1]);
    
    // Stocker le host dans le tableau associatif
    if (!isset($hosts_by_mac_end[$mac_end])) {
        $hosts_by_mac_end[$mac_end] = [];
    }
    $hosts_by_mac_end[$mac_end][] = $host;
}

// Réorganiser les hosts en fonction de la fin de l'adresse MAC
$updated_hosts = [];
foreach ($hosts_by_mac_end as $mac_end => $hosts_group) {
    $updated_hosts = array_merge($updated_hosts, $hosts_group);
}

// Écrire le résultat dans le même fichier
$result_content = implode("\n\n", $updated_hosts);
if (file_put_contents($filename, $result_content) === false) {
    die("Erreur lors de l'écriture du fichier $filename\n");
}

echo "Les hosts ont été triés par la fin de l'adresse MAC et écrits dans $filename avec succès.\n";

?>