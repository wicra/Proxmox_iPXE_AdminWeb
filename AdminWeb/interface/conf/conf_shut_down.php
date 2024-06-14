<?php
    /////////////////////////////////////////////////////////
    //                SCRIP ETEINDRE MACHINE               //
    /////////////////////////////////////////////////////////
    function remote_shutdown($hostname, $username, $password) {
        // SSH command to shut down the machine
        $ssh_command = 'sudo shutdown -h now';

        // Initialize a new SSH connection
        $connection = ssh2_connect($hostname, 22);
        if (!$connection) {
            return false;
        }

        // Authenticate the connection
        if (!ssh2_auth_password($connection, $username, $password)) {
            return false;
        }

        // Execute the shutdown command
        $stream = ssh2_exec($connection, $ssh_command);
        if (!$stream) {
            return false;
        }

        // Wait for the command to complete
        stream_set_blocking($stream, true);
        stream_get_contents($stream);

        // Close the SSH connection
        fclose($stream);

        return true;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve form data
        $host_name = $_POST['host_name'];
        $ip_address = $_POST['ip_address'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Send the shutdown command
        if (remote_shutdown($ip_address, $username, $password)) {
            echo "Shutdown command sent to {$host_name} at IP {$ip_address}.";
        } else {
            echo "Failed to send shutdown command to {$host_name} at IP {$ip_address}.";
        }
    }
?>
