<?php
/////////////////////////////////////////////////////////
//                        SESSION                     //
/////////////////////////////////////////////////////////
session_start();
// Verif si user connecter si la variable $_SESSION comptien le username 
if(!isset($_SESSION["login"])){
    header("location: ../index.php");
exit(); 
}

// déconnection
if(isset($_POST['deconnection'])){
    session_destroy();
    header('location: ../index.php');
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données transmises via POST
    $host_name = $_POST['host_name'];
    $mac_address = $_POST['mac_address'];
    $new_ip = $_POST['new_ip'];

    // Chemin vers le fichier de configuration DHCP
    $file_path = '../../../dhcp/dhcpd_hosts.conf';

    // Lire le contenu du fichier
    $config = file_get_contents($file_path);

    // Définir le modèle de recherche pour l'hôte spécifié
    $pattern = '/(host\s+' . preg_quote($host_name, '/') . '\s*{[^}]*hardware\s+ethernet\s+' . preg_quote($mac_address, '/') . ';[^}]*fixed-address\s+)[0-9.]+(;[^}]*})/mi';

    // Remplacer uniquement l'adresse IP existante par la nouvelle adresse IP
    $replacement = '${1}' . $new_ip . '${2}';
    $new_config = preg_replace($pattern, $replacement, $config);

    // Écrire les modifications dans le fichier
    $result = file_put_contents($file_path, $new_config);

    // Vérifier si l'écriture a réussi
    if ($result !== false) {
        echo '<div class="emoji-container"><i class="fa-solid fa-thumbs-up fa-shake"></i></div>'; // Emoji de célébration
    } else {
        echo '<div class="emoji-container" style="color: blue;">😢</div>'; // Emoji de tristesse

    }
}
?>
