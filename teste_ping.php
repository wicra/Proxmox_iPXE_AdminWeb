<?php
    // Adresse IP du PC à vérifier
    $ip_address = "192.168.1.100";
    $port = 80; // Port HTTP par défaut

    // Tentative de connexion au PC
    $connection = @fsockopen($ip_address, $port, $errno, $errstr, 2); // Timeout de 2 secondes

    // Vérifie si la connexion a réussi
    if ($connection) {
        echo "<p>L'adresse IP $ip_address est active.</p>";
        fclose($connection);
    } else {
        echo "<p>L'adresse IP $ip_address est inactive.</p>";
    }
    ?>



<?php
    // Adresse IP du PC à vérifier
    $ip_address = "192.168.1.100";

    // Exécute la commande ping et récupère le résultat
    exec("ping -c 1 $ip_address", $output, $result);

    // Vérifie si la commande ping a réussi (0) ou non (autre chose que 0)
    if ($result == 0) {
        echo "<p>L'adresse IP $ip_address est active.</p>";
    } else {
        echo "<p>L'adresse IP $ip_address est inactive.</p>";
    }
    ?>