<?php
// Récupération des données du formulaire
$host_name = $_POST['host_name'];
$mac_address = $_POST['mac_address'];
$ip_address = $_POST['ip_address'];

// Chemin vers le fichier dhcpd_hosts.conf
include("../connection/link.php");

// Lecture du contenu du fichier
$file_content = file_get_contents($file_path);

// Vérification si le fichier a été lu avec succès
if ($file_content === false) {
    echo "Impossible de lire le fichier de configuration.";
    exit;
}

// Recherche de l'entrée du host à supprimer
$pattern = "/^host\s+{$host_name}\s*{[^}]+hardware ethernet\s+{$mac_address};[^}]+fixed-address\s+{$ip_address};[^}]*\s*};\s/mi";

// Suppression de l'entrée du host
$file_content = preg_replace($pattern,'', $file_content);

// Écriture du nouveau contenu dans le fichier
if (file_put_contents($file_path, $file_content) !== false) {
    echo "L'entrée du host a été supprimée avec succès.";

} else {
    echo "Une erreur s'est produite lors de la suppression de l'entrée du host.";
}

?>
