<?php
function wake_on_lan($mac_address, $broadcast_ip = '255.255.255.255', $port = 9) {
    $mac_hex = str_replace(':', '', $mac_address);
    if (strlen($mac_hex) !== 12) {
        return false;
    }

    $magic_packet = str_repeat(chr(0xFF), 6) . str_repeat(pack('H*', $mac_hex), 16);

    if (!$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
        return false;
    }

    if (!socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true)) {
        socket_close($socket);
        return false;
    }

    $result = socket_sendto($socket, $magic_packet, strlen($magic_packet), 0, $broadcast_ip, $port);
    socket_close($socket);

    return $result !== false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données du formulaire
    $host_name = $_POST['host_name'];
    $mac_address = $_POST['mac_address'];
    $ip_address = $_POST['ip_address'];

    // Envoi du paquet magique
    if (wake_on_lan($mac_address)) {
        echo "Magic packet sent to {$mac_address} to wake up {$host_name} at IP {$ip_address}.";
    } else {
        echo "Failed to send magic packet to {$mac_address}.";
    }
}
?>
