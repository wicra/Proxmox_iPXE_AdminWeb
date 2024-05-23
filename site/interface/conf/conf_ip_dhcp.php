<?php
// Vérifier si les données POST existent
if (isset($_POST['host_name']) && isset($_POST['mac_address']) && isset($_POST['new_ip'])) {
    $host_name = $_POST['host_name'];
    $mac_address = $_POST['mac_address'];
    $new_ip = $_POST['new_ip'];

    // Chemin vers le fichier de configuration DHCP
    //$file_path = '/etc/dhcp/dhcpd.conf';
    $file_path = '../../dhcp/dhcpd.conf';
    
    // Lire le fichier de configuration
    $config = file_get_contents($file_path);

    if ($config === false) {
        die("Impossible de lire le fichier de configuration.");
    }

    // Expression régulière pour trouver et remplacer l'adresse IP fixe
    $pattern = '/(host\s+' . preg_quote($host_name) . '\s*\{[^}]*hardware\s+ethernet\s+' . preg_quote($mac_address) . ';[^}]*fixed-address\s+)[0-9.]+(;[^}]*\})/mi';
    $replacement = '${1}' . $new_ip . '${2}';

    // Remplacer l'adresse IP fixe
    $new_config = preg_replace($pattern, $replacement, $config);

    // Écrire le nouveau fichier de configuration
    if (file_put_contents($file_path, $new_config) === false) {
        die("Impossible d'écrire dans le fichier de configuration.");
    }

    echo "Adresse IP fixe mise à jour avec succès.";
} else {
    echo "Données manquantes.";
}
?>
