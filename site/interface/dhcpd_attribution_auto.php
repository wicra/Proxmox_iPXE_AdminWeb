<?php
// Fichier de log DHCP/var/lib/dhcp/
$LEASES_FILE = "../../dhcp/dhcpd.leases";
// Fichier de configuration DHCP
$DHCP_CONF = "../../dhcp/dhcpd_hosts.conf";

// Plage d'adresses IP (à adapter selon votre réseau)
$IP_RANGE_START = '10.10.62.10';
$IP_RANGE_END = '10.10.62.150';

// Vérification de l'existence des fichiers
if (!file_exists($LEASES_FILE)) {
    die("Erreur : Le fichier $LEASES_FILE n'existe pas.\n");
}
if (!file_exists($DHCP_CONF)) {
    die("Erreur : Le fichier $DHCP_CONF n'existe pas.\n");
}

// Lire le contenu du fichier de bail DHCP
$leases_content = file_get_contents($LEASES_FILE);

// Vérifier si la lecture du fichier a réussi
if ($leases_content === false) {
    die("Erreur : Impossible de lire le fichier $LEASES_FILE.\n");
}

// Analyser les baux DHCP par adresse MAC
preg_match_all('/lease ([0-9.]+) {([^}]*)}/s', $leases_content, $matches);

// Tableau pour stocker la connexion la plus récente pour chaque adresse MAC
$recent_connections = [];

// Lire le contenu du fichier de configuration DHCP
$dhcp_conf_content = file_get_contents($DHCP_CONF);

// Vérifier si la lecture du fichier a réussi
if ($dhcp_conf_content === false) {
    die("Erreur : Impossible de lire le fichier $DHCP_CONF.\n");
}

// Extraire les adresses MAC déjà présentes dans le fichier de configuration
preg_match_all('/hardware ethernet ([0-9a-f:]+);/i', $dhcp_conf_content, $existing_mac_matches);
$existing_macs = $existing_mac_matches[1];

// Extraire les adresses IP déjà attribuées
preg_match_all('/fixed-address ([0-9.]+);/i', $dhcp_conf_content, $existing_ip_matches);
$existing_ips = $existing_ip_matches[1];

// Fonction pour vérifier si une adresse IP est déjà attribuée
function is_ip_taken($ip, $existing_ips) {
    return in_array($ip, $existing_ips);
}

// Fonction pour obtenir la prochaine IP disponible dans la plage
function get_next_available_ip($start_ip, $end_ip, $existing_ips) {
    $start = ip2long($start_ip);
    $end = ip2long($end_ip);

    for ($ip = $start; $ip <= $end; $ip++) {
        $current_ip = long2ip($ip);
        if (!is_ip_taken($current_ip, $existing_ips)) {
            return $current_ip;
        }
    }
    return false;
}

// Boucler à travers les baux DHCP pour chaque adresse MAC
foreach ($matches[1] as $index => $ip_address) {
    $lease_info = $matches[2][$index];

    // Extraire la date de début du bail
    preg_match('/starts \d+ (\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2});/', $lease_info, $start_matches);
    $start_time = strtotime($start_matches[1]);

    // Extraire l'adresse MAC
    preg_match('/hardware ethernet (.*?);/', $lease_info, $mac_matches);
    $mac_address = $mac_matches[1];

    // Ignorer cette adresse MAC si elle est déjà présente dans le fichier de configuration
    if (in_array($mac_address, $existing_macs)) {
        continue;
    }

    // Extraire le nom d'hôte
    preg_match('/client-hostname "(.*?)";/', $lease_info, $hostname_matches);
    $hostname = isset($hostname_matches[1]) ? $hostname_matches[1] : "";

    // Si aucun nom d'hôte n'est trouvé, attribuer un nom générique
    if (empty($hostname)) {
        $hostname = "machine_" . substr(str_replace(":", "", $mac_address), -6);
    }

    // Vérifier si cette connexion est plus récente que celle précédemment enregistrée pour cette adresse MAC
    if (!isset($recent_connections[$mac_address]) || $start_time > $recent_connections[$mac_address]['start_time']) {
        // Enregistrer cette connexion comme la plus récente
        $recent_connections[$mac_address] = [
            'ip_address' => $ip_address,
            'start_time' => $start_time,
            'hostname' => $hostname
        ];
    }
}

// Si le formulaire est soumis pour attribuer une IP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mac_address'])) {
    $mac_address = $_POST['mac_address'];
    if (isset($recent_connections[$mac_address])) {
        $connection = $recent_connections[$mac_address];
        $hostname = $connection['hostname'];
        
        // Obtenir la prochaine IP disponible
        $ip_address = get_next_available_ip($IP_RANGE_START, $IP_RANGE_END, $existing_ips);
        if ($ip_address === false) {
            echo "Erreur : Aucune adresse IP disponible dans la plage définie.\n";
        } else {
            // Ajouter l'entrée au fichier de configuration DHCP
            $file_handle = fopen($DHCP_CONF, 'a');
            if ($file_handle !== false) {
                fwrite($file_handle, "host $hostname {\n");
                fwrite($file_handle, "    hardware ethernet $mac_address;\n");
                fwrite($file_handle, "    fixed-address $ip_address;\n");
                fwrite($file_handle, "    # PXE Boot\n");
                fwrite($file_handle, "    include(\"condition_pxe_boot.conf\");\n");
                fwrite($file_handle, "};\n");

                // fwrite($file_handle, "    if option arch = 00:07 or option arch = 00:09 {\n");
                // fwrite($file_handle, "        if exists user-class and option user-class = \"iPXE\" {\n");
                // fwrite($file_handle, "            filename \"http://10.10.62.210/menu_known2.ipxe\";\n");
                // fwrite($file_handle, "        } else {\n");
                // fwrite($file_handle, "            filename \"ipxe/ipxe.efi\";\n");
                // fwrite($file_handle, "        }\n");
                // fwrite($file_handle, "    }\n");
                // fwrite($file_handle, "    else if option arch = 00:06 {\n");
                // fwrite($file_handle, "        if exists user-class and option user-class = \"iPXE\" {\n");
                // fwrite($file_handle, "            filename \"http://10.10.62.210/menu_known2.ipxe\";\n");
                // fwrite($file_handle, "        } else {\n");
                // fwrite($file_handle, "            filename \"ipxe/ipxe32.efi\";\n");
                // fwrite($file_handle, "        }\n");
                // fwrite($file_handle, "    }\n");
                // fwrite($file_handle, "    else {\n");
                // fwrite($file_handle, "        if exists user-class and option user-class = \"iPXE\" {\n");
                // fwrite($file_handle, "            filename \"http://10.10.62.210/menu_known2.ipxe\";\n");
                // fwrite($file_handle, "        } else {\n");
                // fwrite($file_handle, "            filename \"undionly.kpxe\";\n");
                // fwrite($file_handle, "        }\n");
                // fwrite($file_handle, "    }\n");
                // fwrite($file_handle, "}\n\n");

                fclose($file_handle);
                // echo "IP $ip_address attribuée pour le client $hostname : $mac_address avec succès.<br>";
                // Ajouter l'IP à la liste des IPs existantes
                $existing_ips[] = $ip_address;
                // Retirer l'entrée des connexions récentes pour ne pas l'afficher de nouveau
                unset($recent_connections[$mac_address]);
            } else {
                echo "Erreur : Impossible d'ouvrir le fichier $DHCP_CONF pour écriture.\n";
            }
        }
    }
}

?>


<table class="tableau_historique_dhcp" id="tableau_historique_dhcp">
    <thead>
        <tr>
            <th class="tab_historique_header_nom">Nom d'hôte</th>
            <th class="tab_historique_header_mac">Adresse MAC</th>
            <th class="tab_historique_header_bouton">Attribuer une IP</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recent_connections as $mac_address => $connection): ?>
            <tr>
                <td class="tab_historique_nom"><?= htmlspecialchars($connection['hostname']) ?></td>
                <td class="tab_historique_mac"><?= htmlspecialchars($mac_address) ?></td>
                
                <td class="tab_historique_bouton">
                    <form  method="POST">
                        <input type="hidden" name="mac_address" value="<?= htmlspecialchars($mac_address) ?>">
                        <button  type="submit">Attribuer IP</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>