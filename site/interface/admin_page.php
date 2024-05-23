<?php
// Chemin vers le fichier de configuration DHCP
$file_path = '/etc/dhcp/dhcpd.conf';

// Lire le fichier de configuration
$config = file_get_contents($file_path);

if ($config === false) {
    die("Impossible de lire le fichier de configuration.");
}

// Expression régulière pour extraire les informations des hôtes
$pattern = '/host\s+(\w+)\s*\{[^}]*hardware\s+ethernet\s+([0-9a-f:]+);[^}]*fixed-address\s+([0-9.]+);[^}]*\}/mi';

// Trouver tous les hôtes correspondants
if (preg_match_all($pattern, $config, $matches, PREG_SET_ORDER)) {
    echo "<h1>Liste des hôtes DHCP</h1>";
    echo "<table border='1'>
            <tr>
                <th>Nom de l'hôte</th>
                <th>Adresse MAC</th>
                <th>Adresse IP fixe</th>
            </tr>";
    
    // Parcourir chaque hôte trouvé
    foreach ($matches as $match) {
        $host_name = $match[1];
        $hardware_ethernet = $match[2];
        $fixed_address = $match[3];
        
        echo "<tr>
                <td>{$host_name}</td>
                <td>{$hardware_ethernet}</td>
                <td>{$fixed_address}</td>
              </tr>";
    }
    
    echo "</table>";
} else {
    echo "Aucun hôte trouvé dans le fichier de configuration.";
}
?>
