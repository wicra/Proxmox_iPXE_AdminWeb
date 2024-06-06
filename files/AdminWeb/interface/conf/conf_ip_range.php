<?php


// Fichier de log DHCP/var/lib/dhcp/ et /etc/dhcp/
include("include/link.php");


// Fonction pour mettre à jour les variables de configuration
function update_auto_config($file, $new_config) {
    $config_content = file_get_contents($file);
    if ($config_content === false) {
        notif("Erreur : Impossible de lire le fichier de configuration.");
    }

    // Remplacer les valeurs existantes par les nouvelles valeurs
    $config_content = preg_replace('/\$IP_RANGE_START = \'[^\']+\';/', "\$IP_RANGE_START = '{$new_config['ip_range_start']}';", $config_content);
    $config_content = preg_replace('/\$IP_RANGE_END = \'[^\']+\';/', "\$IP_RANGE_END = '{$new_config['ip_range_end']}';", $config_content);

    // Écrire les nouvelles valeurs dans le fichier de configuration
    if (file_put_contents($file, $config_content) === false) {
        notif("Erreur : Impossible de mettre à jour le fichier de configuration.");
    }
}

// Fonction pour mettre à jour la plage d'adresses IP dans le fichier de configuration DHCP
function update_dhcp_config($file, $start_ip, $end_ip) {
    $config_content = file_get_contents($file);
    if ($config_content === false) {
        notif("Erreur : Impossible de lire le fichier de configuration.");
    }

    // Rechercher et remplacer la plage d'adresses IP
    $config_content = preg_replace('/range\s+\d+\.\d+\.\d+\.\d+\s+\d+\.\d+\.\d+\.\d+;/', "range $start_ip $end_ip;", $config_content);

    // Écrire les nouvelles valeurs dans le fichier de configuration
    if (file_put_contents($file, $config_content) === false) {
        notif("Erreur : Impossible de mettre à jour le fichier de configuration.");
    }
}


// Lire le contenu actuel du fichier de configuration pour préremplir le formulaire
$config_content_auto = file_get_contents($config_file_auto);
preg_match('/\$IP_RANGE_START = \'([^\']+)\';/', $config_content_auto, $start_matches_auto);
preg_match('/\$IP_RANGE_END = \'([^\']+)\';/', $config_content_auto, $end_matches_auto);
$current_start_ip_auto = $start_matches_auto[1];
$current_end_ip_auto = $end_matches_auto[1];

// Lire le contenu actuel du fichier de configuration DHCP pour préremplir le formulaire
$config_content_dhcp = file_get_contents($config_file_dhcp);
preg_match('/range\s+(\d+\.\d+\.\d+\.\d+)\s+(\d+\.\d+\.\d+\.\d+);/', $config_content_dhcp, $range_matches_dhcp);
$current_start_ip_dhcp = $range_matches_dhcp[1];
$current_end_ip_dhcp = $range_matches_dhcp[2];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_auto'])) {
        $ip_range_start = $_POST['ip_range_start'];
        $ip_range_end = $_POST['ip_range_end'];

        // Mettre à jour les variables de configuration
        update_auto_config($config_file_auto, [
            'ip_range_start' => $ip_range_start,
            'ip_range_end' => $ip_range_end
        ]);

        shell_exec('../../shell/boot_server_dhcp.sh');
        notif( "La plage d'adresses IP pour les attributions automatiques a été mise à jour avec succès.");
        echo "<script>document.getElementById('refresh').click();</script>";
        

    } elseif (isset($_POST['update_dhcp'])) {
        $dhcp_range_start = $_POST['dhcp_range_start'];
        $dhcp_range_end = $_POST['dhcp_range_end'];

        // Mettre à jour la plage d'adresses IP dans le fichier DHCP
        update_dhcp_config($config_file_dhcp, $dhcp_range_start, $dhcp_range_end);
        shell_exec('../../shell/boot_server_dhcp.sh');
        notif( "La plage d'adresses IP pour le DHCP a été mise à jour avec succès.");
        echo "<script>document.getElementById('refresh').click();</script>";
        
    }
}

?>

<div class="conteneur_formulaire_range_ip" id="conteneur_formulaire_range_ip">
    <form class="formulaire_range_ip" action="" method="POST" onsubmit="return validateForm()">
        <label class="label_range_ip" for="ip_range_start">Début Ip Fixe:</label>
        <input class="input_range_ip" type="text" id="ip_range_start" name="ip_range_start" value="<?= htmlspecialchars($current_start_ip_auto) ?>" required>
        <br>
        <label class="label_range_ip" for="ip_range_end">Fin Ip Fixe:</label>
        <input class="input_range_ip" type="text" id="ip_range_end" name="ip_range_end" value="<?= htmlspecialchars($current_end_ip_auto) ?>" required>
        <br>
        <button class="bouton_range_ip" type="submit" name="update_auto">Changer</button>
    </form>

    <form class="formulaire_range_ip" action="" method="POST" onsubmit="return validateForm()">
        <label class="label_range_ip" for="dhcp_range_start">Début Ip dynamique:</label>
        <input class="input_range_ip" type="text" id="dhcp_range_start" name="dhcp_range_start" value="<?= htmlspecialchars($current_start_ip_dhcp) ?>" required>
        <br>
        <label class="label_range_ip" for="dhcp_range_end">Fin Ip dynamique:</label>
        <input class="input_range_ip" type="text" id="dhcp_range_end" name="dhcp_range_end" value="<?= htmlspecialchars($current_end_ip_dhcp) ?>" required>
        <br>
        <button class="bouton_range_ip" type="submit" name="update_dhcp">Changer</button>
    </form>
</div>

<script>
    function validateForm() {
        // Récupérer les valeurs des champs d'entrée
        var ipRangeStart = document.getElementById('ip_range_start').value;
        var ipRangeEnd = document.getElementById('ip_range_end').value;
        var dhcpRangeStart = document.getElementById('dhcp_range_start').value;
        var dhcpRangeEnd = document.getElementById('dhcp_range_end').value;

        // Convertir les adresses IP en entiers pour comparaison
        var ipStart = ip_to_long(ipRangeStart);
        var ipEnd = ip_to_long(ipRangeEnd);
        var dhcpStart = ip_to_long(dhcpRangeStart);
        var dhcpEnd = ip_to_long(dhcpRangeEnd);

        // Vérifier si les plages d'adresses IP se chevauchent
        if (ipEnd >= dhcpStart && dhcpEnd >= ipStart) {
            // Les plages se chevauchent, afficher un message d'erreur
            alert("Erreur : Les plages d'adresses IP se chevauchent.");

            // Attendre 4 secondes avant d'actualiser la page
            setTimeout(function() {
                location.reload();
            }, 2000);
            return false; // Empêcher l'envoi du formulaire
        }
        return true; // Autoriser l'envoi du formulaire si les plages d'adresses IP ne se chevauchent pas
    }

    // Fonction pour convertir une adresse IP en entier
    function ip_to_long(ip) {
        var parts = ip.split('.');
        return parts.reduce(function (acc, part) {
            return (acc << 8) + parseInt(part);
        }, 0);
    }
</script>