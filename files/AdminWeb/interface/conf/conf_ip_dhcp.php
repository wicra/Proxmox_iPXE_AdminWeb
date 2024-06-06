<?php
/////////////////////////////////////////////////////////
//                        SESSION                     //
/////////////////////////////////////////////////////////
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données transmises via POST
    $host_name = $_POST['host_name'];
    $mac_address = $_POST['mac_address'];
    $new_ip = $_POST['new_ip'];

    //Verif securité ip
    if (filter_var($new_ip, FILTER_VALIDATE_IP) === false) {
        $_SESSION['notifications'][] = "Adresse IP invalide.";
        exit;
    }
    // Chemin vers le fichier de configuration DHCP
    include("../include/link.php");

    $config = file_get_contents($file_path_conf);

    $pattern = '/(host\s+' . preg_quote($host_name, '/') . '\s*{[^}]*hardware\s+ethernet\s+' . preg_quote($mac_address, '/') . ';[^}]*fixed-address\s+)[0-9.]+(;[^}]*})/mi';

    // Remplacer uniquement l'adresse IP existante par la nouvelle adresse IP
    $replacement = '${1}' . $new_ip . '${2}';
    $new_config = preg_replace($pattern, $replacement, $config);

    // Écrire les modifications dans le fichier
    $result = file_put_contents($file_path_conf, $new_config);

    if ($result !== false) {
        #redemarage du server dhcp après modif
        shell_exec('../../shell/boot_server_dhcp.sh');
        echo '<div class="emoji-container"><i class="fa-solid fa-thumbs-up fa-shake"></i></div>'; // Emoji de célébration
    } else {
        echo '<div class="emoji-container" style="color: blue;">😢</div>'; // Emoji de tristesse

    }
}
?>
