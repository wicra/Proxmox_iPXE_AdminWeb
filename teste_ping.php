<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des PCs</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .tableau_conteneur {
            width: 100%;
            max-height: 400px;
            overflow-y: auto;
            position: relative;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            position: sticky;
            top: 0;
            background-color: #f1f1f1;
            z-index: 1;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="tableau_conteneur">
        <table>
            <thead>
                <tr>
                    <th>hôte</th>
                    <th>Etat</th>
                    <th>OS</th>
                    <th>@ MAC</th>
                    <th>@ IP_fixe</th>
                    <th>@ IP_conf</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fichier de log DHCP/var/lib/dhcp/
                $LEASES_FILE = "../../../dhcp/dhcpd.leases";
                // Fichier de configuration DHCP
                $DHCP_CONF = "../../../dhcp/dhcpd_hosts.conf";

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

                // Afficher les entrées dans le tableau
                foreach ($recent_connections as $mac_address => $connection) {
                    $ip_address = $connection['ip_address'];
                    $hostname = $connection['hostname'];
                    echo "<tr>
                            <td>{$hostname}</td>
                            <td><i class=\"fa-solid fa-circle-check\"></i></td>
                            <td><i class=\"fa-brands fa-windows\"></i></td>
                            <td>{$mac_address}</td>
                            <td>{$ip_address}</td>
                            <td>
                                <form method=\"POST\" action=\"\">
                                    <input type=\"hidden\" name=\"hostname\" value=\"{$hostname}\">
                                    <input type=\"hidden\" name=\"mac_address\" value=\"{$mac_address}\">
                                    <input type=\"hidden\" name=\"ip_address\" value=\"{$ip_address}\">
                                    <button type=\"submit\" name=\"add_entry\">Ajouter</button>
                                </form>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    // Ajouter l'entrée au fichier de configuration DHCP si le bouton est cliqué
    if (isset($_POST['add_entry'])) {
        $hostname = $_POST['hostname'];
        $mac_address = $_POST['mac_address'];
        $ip_address = $_POST['ip_address'];

        // Ouvrir le fichier de configuration DHCP en écriture
        $file_handle = fopen($DHCP_CONF, 'a');
        if ($file_handle === false) {
            die("Erreur : Impossible d'ouvrir le fichier $DHCP_CONF pour écriture.\n");
        }

        // Écrire l'entrée dans le fichier de configuration DHCP
        fwrite($file_handle, "host $hostname {\n");
        fwrite($file_handle, "    hardware ethernet $mac_address;\n");
        fwrite($file_handle, "    fixed-address $ip_address;\n");
        // Ajouter la configuration PXE Boot
        fwrite($file_handle, "    # PXE Boot\n");
        fwrite($file_handle, "    include(\"condition_pxe_boot.conf\");\n");
        fwrite($file_handle, "};\n");

        // Fermer le fichier de configuration DHCP
        fclose($file_handle);

        // Redirection pour éviter les resoumissions de formulaire
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
    ?>
</body>
</html>
