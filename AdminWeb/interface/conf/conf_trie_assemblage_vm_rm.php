<?php

    // Lire le contenu du fichier
    include("include/link.php");

    $file_content = file_get_contents($file_path_trie);

    if ($file_content === false) {
        die("Erreur de lecture du fichier $file_path_trie\n");
    }

    // Extraire les blocs de hosts
    preg_match_all('/host\s+[^\}]+\}/', $file_content, $matches);
    $hosts = $matches[0];

    if (empty($hosts)) {
        die("Aucun bloc 'host' trouvé dans le fichier $file_path_trie\n");
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
            echo "<script>console.log(\"Adresse MAC non trouvée pour le host suivant\")</script>";
            continue;
        }

        $mac = trim($mac_match[1]);
        $mac_end = getMacEnding($mac);
        
        // Stocker le host dans le tableau associatif
        if (!isset($hosts_by_mac_end[$mac_end])) {
            $hosts_by_mac_end[$mac_end] = [];
        }
        $hosts_by_mac_end[$mac_end][] = $host;
    }

    // Réorganiser les hosts en fonction de la fin de l'adresse MAC et de l'adresse MAC commencant par fa:ca:de
    $updated_hosts = [];

    foreach ($hosts_by_mac_end as $mac_end => $hosts_group) {
        $fa_cade_hosts = [];
        $other_hosts = [];

        // Séparer les hosts en deux groupes
        foreach ($hosts_group as $host) {
            preg_match('/hardware ethernet ([^;]+);/', $host, $mac_match);
            $mac = trim($mac_match[1]);

            if (strpos($mac, 'fa:ca:de') === 0) {
                $fa_cade_hosts[] = $host;
            } else {
                $other_hosts[] = $host;
            }
        }

        // Ajouter d'abord les autres hosts, puis ceux commencant par fa:ca:de
        $updated_hosts = array_merge($updated_hosts, $other_hosts, $fa_cade_hosts);
    }

    // Écrire le résultat dans le même fichier
    $result_content = implode("\n\n", $updated_hosts);
    if (file_put_contents($file_path_trie, $result_content) === false) {
        die("Erreur lors de l'écriture du fichier $file_path_trie\n");
    }

    // Réecriture réussi
    echo "<script>
            console.log(\"Les hosts ont été triés par la fin de l'adresse 
            MAC et écrits dans dhcpd_hosts.conf avec succès.\")
        </script>";
?>

