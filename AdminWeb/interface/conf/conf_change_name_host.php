<?php
    /////////////////////////////////////////////////////////
    //            SCRIP CHANGEMENT DE NOM DE HOST          //
    /////////////////////////////////////////////////////////
    session_start();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $old_host_name = $_POST['old_host_name'];
        $new_host_name = $_POST['new_host_name'];

        // Validation du nom d'hôte sécurité
        if (preg_match('/^[a-zA-Z0-9_-]{1,15}$/', $new_host_name) === 0) {
            echo "Nom d'hôte invalide.";
            exit;
        }
        // Chemin vers le fichier dhcpd_hosts.conf
        include("../include/link.php");

        // Lecture du contenu du fichier
        $file_content = file_get_contents($file_path_conf);

        // Vérification
        if ($file_content === false) {
            $_SESSION['notifications'][] = "Impossible de lire le fichier de configuration.";
            exit;
        }
        // Remplacement de l'ancien nom d'hôte par le nouveau
        $pattern = "/host\s+" . preg_quote($old_host_name, '/') . "\s*{/";
        $replacement = "host " . $new_host_name . " {";
        $file_content = preg_replace($pattern, $replacement, $file_content);

        // Écriture du nouveau contenu dans le fichier
        if (file_put_contents($file_path_conf, $file_content) !== false) {
            #redemarage du server dhcp après modif
            shell_exec('../../shell/boot_server_dhcp.sh');
            $_SESSION['notifications'][] = "Le nom d'hôte a été mis à jour avec succès.";
        } else {
            $_SESSION['notifications'][] = "Une erreur s'est produite lors de la mise à jour du nom d'hôte.";
        }
    }
?>
