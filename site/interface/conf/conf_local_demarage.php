<?php
// Récupération des données du formulaire
$host_name = $_POST['host_name'];
$mac_address = $_POST['mac_address'];
$ip_address = $_POST['ip_address'];
// Nouveau contenu à ajouter dans le fichier dhcpd_hosts.conf
$new_host_entry = "host {$host_name} {
    hardware ethernet {$mac_address};
    fixed-address {$ip_address};
    # PXE Boot
    include \"condition_pxe_boot_local.conf\";
}";

// Chemin vers le fichier dhcpd_hosts.conf
include("../connection/link.php");

// Lecture du contenu du fichier
$file_content = file_get_contents($file_path);

// Vérification si le fichier a été lu avec succès
if ($file_content === false) {
    echo "Impossible de lire le fichier de configuration.";
    exit;
}

// Remplacement de l'ancienne entrée du host par la nouvelle
$file_content = preg_replace("/host {$host_name}.*?}/s", $new_host_entry, $file_content);

// Écriture du nouveau contenu dans le fichier
if (file_put_contents($file_path, $file_content) !== false) {
    echo "Le fichier dhcpd_hosts.conf a été mis à jour avec succès.";
} else {
    echo "Une erreur s'est produite lors de la mise à jour du fichier dhcpd_hosts.conf.";
}
?>
