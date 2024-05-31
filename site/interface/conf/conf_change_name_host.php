<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_host_name = $_POST['old_host_name'];
    $new_host_name = $_POST['new_host_name'];

    // Chemin vers le fichier dhcpd_hosts.conf
    include("../connection/link.php");

    // Lecture du contenu du fichier
    $file_content = file_get_contents($file_path);

    // Vérification si le fichier a été lu avec succès
    if ($file_content === false) {
        echo "Impossible de lire le fichier de configuration.";
        exit;
    }

    // Remplacement de l'ancien nom d'hôte par le nouveau
    $pattern = "/host\s+" . preg_quote($old_host_name, '/') . "\s*{/";
    $replacement = "host " . $new_host_name . " {";
    $file_content = preg_replace($pattern, $replacement, $file_content);

    // Écriture du nouveau contenu dans le fichier
    if (file_put_contents($file_path, $file_content) !== false) {
        echo "Le nom d'hôte a été mis à jour avec succès.";
    } else {
        echo "Une erreur s'est produite lors de la mise à jour du nom d'hôte.";
    }
}
?>
