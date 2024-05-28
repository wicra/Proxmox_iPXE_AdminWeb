<?php
// Fichier de log DHCP/var/lib/dhcp/
$LEASES_FILE = "dhcp/dhcpd.leases";
// Fichier de configuration DHCP
$DHCP_CONF = "dhcp/dhcpd_hosts.conf";

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
        $ip_address = $connection['ip_address'];

        // Ajouter l'entrée au fichier de configuration DHCP
        $file_handle = fopen($DHCP_CONF, 'a');
        if ($file_handle !== false) {
            fwrite($file_handle, "host $hostname {\n");
            fwrite($file_handle, "    hardware ethernet $mac_address;\n");
            fwrite($file_handle, "    fixed-address $ip_address;\n");
            fwrite($file_handle, "    # PXE Boot\n");
            fwrite($file_handle, "    include(\"condition_pxe_boot.conf\");\n");
            fwrite($file_handle, "};\n");
            fclose($file_handle);
            echo "IP attribuée pour $hostname ($mac_address) avec succès.<br>";
            // Retirer l'entrée des connexions récentes pour ne pas l'afficher de nouveau
            unset($recent_connections[$mac_address]);
        } else {
            echo "Erreur : Impossible d'ouvrir le fichier $DHCP_CONF pour écriture.\n";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DHCP Lease Viewer</title>
</head>
<body>
    <h1>DHCP Lease Viewer</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Nom d'hôte</th>
                <th>Adresse MAC</th>
                <th>Adresse IP</th>
                <th>Attribuer une IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_connections as $mac_address => $connection): ?>
                <tr>
                    <td><?= htmlspecialchars($connection['hostname']) ?></td>
                    <td><?= htmlspecialchars($mac_address) ?></td>
                    <td><?= htmlspecialchars($connection['ip_address']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="mac_address" value="<?= htmlspecialchars($mac_address) ?>">
                            <button type="submit">Attribuer IP</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
