<?php
// Fonction pour lire et parser le fichier dhcp.conf
function lireEtParserDhcpConf($filepath) {
    $pcsConnus = [];
    if (file_exists($filepath)) {
        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $currentHost = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Détection de la section d'un hôte
            if (preg_match('/^host\s+(\w+)\s*{/', $line, $matches)) {
                $currentHost = ['name' => $matches[1], 'mac' => '', 'ip' => ''];
            }

            // Extraction des adresses MAC et IP
            if ($currentHost) {
                if (preg_match('/hardware\s+ethernet\s+([\dA-Fa-f:]+);/', $line, $matches)) {
                    $currentHost['mac'] = $matches[1];
                }
                if (preg_match('/fixed-address\s+([\d.]+);/', $line, $matches)) {
                    $currentHost['ip'] = $matches[1];
                }
                // Fin de la section d'un hôte
                if (strpos($line, '}') !== false) {
                    $pcsConnus[] = $currentHost;
                    $currentHost = null;
                }
            }
        }
    } else {
        echo "Le fichier $filepath n'a pas été trouvé.";
    }

    return $pcsConnus;
}

// Chemin vers le fichier dhcp.conf
$fichierDhcp = '/etc/dhcp/dhcpd.conf';

// Lire et parser le fichier dhcp.conf
$pcsConnus = lireEtParserDhcpConf($fichierDhcp);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PCs Connus - dhcp.conf</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>PCs Connus</h1>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Adresse IP</th>
                <th>Adresse MAC</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pcsConnus)): ?>
                <?php foreach ($pcsConnus as $pc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pc['name']); ?></td>
                        <td><?php echo htmlspecialchars($pc['ip']); ?></td>
                        <td><?php echo htmlspecialchars($pc['mac']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Aucun PC connu trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
