<?php
$mac = $_GET['mac'];
$file = '/var/www/html/mac_addresses.txt';

if (strpos(file_get_contents($file), $mac) !== false) {
    header('Location: http://<ip_server>/client_deja_enregistre.ipxe');
    exit;
}

file_put_contents($file, $mac.PHP_EOL, FILE_APPEND);
header('Location: http://<ip_server>/install.ipxe');
?>
