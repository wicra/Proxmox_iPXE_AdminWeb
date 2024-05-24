<?php
    // Adresse IP du PC à vérifier
    $ip_address = "192.168.1.100";

    // Exécute la commande ping
    exec("ping -c 1 $ip_address", $output, $result);

    // Vérifie le résultat du ping
    if ($result == 0) {
        echo "<p>L'adresse IP $ip_address est active.</p>";
    } else {
        echo "<p>L'adresse IP $ip_address est inactive.</p>";
    }
    ?>