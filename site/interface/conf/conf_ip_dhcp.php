<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données transmises via POST
    $host_name = $_POST['host_name'];
    $mac_address = $_POST['mac_address'];
    $new_ip = $_POST['new_ip'];

    // Chemin vers le fichier de configuration DHCP
    $file_path = '../../../dhcp/dhcpd.conf';

    // Lire le contenu du fichier
    $config = file_get_contents($file_path);

    // Remplacer l'adresse IP existante avec la nouvelle adresse IP
    $config = preg_replace('/host\s+' . $host_name . '\s*{[^}]*hardware\s+ethernet\s+' . $mac_address . ';[^}]*fixed-address\s+[0-9.]+;[^}]*}/mi', 'host ' . $host_name . ' {' . PHP_EOL . '    hardware ethernet ' . $mac_address . ';' . PHP_EOL . '    fixed-address ' . $new_ip . ';' . PHP_EOL . '}', $config);

    // Écrire les modifications dans le fichier
    $result = file_put_contents($file_path, $config);

    // Vérifier si l'écriture a réussi
    if ($result !== false) {
        echo "L'adresse IP a été modifiée avec succès.";
    } else {
        echo "Une erreur s'est produite lors de la modification de l'adresse IP.";
    }
} else {
    echo "Méthode non autorisée.";
}
?>
