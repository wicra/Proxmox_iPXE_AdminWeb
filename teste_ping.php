<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de l'état d'un PC</title>
    <style>
        #status {
            font-size: 1.5em;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .on {
            color: white;
            background-color: green;
        }
        .off {
            color: white;
            background-color: red;
        }
        .invalid {
            color: white;
            background-color: gray;
        }
    </style>
</head>
<body>
    <h1>Vérification de l'état d'un PC</h1>
    <p>Adresse IP du PC : <span id="ip_address">10.10.62.161</span></p>
    <p>État du PC : <span id="status" class="off">Vérification...</span></p>

    <script>
        const ipAddress = document.getElementById('ip_address').textContent;
        const statusElement = document.getElementById('status');

        function checkPcStatus() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `teste_ping.php?ip_address=${ipAddress}`, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'on') {
                        statusElement.textContent = 'Allumé';
                        statusElement.className = 'on';
                    } else if (data.status === 'off') {
                        statusElement.textContent = 'Éteint';
                        statusElement.className = 'off';
                    } else {
                        statusElement.textContent = 'Adresse IP invalide';
                        statusElement.className = 'invalid';
                    }
                } else {
                    statusElement.textContent = 'Erreur lors de la vérification';
                    statusElement.className = 'invalid';
                }
            };
            xhr.send();
        }

        // Vérifiez l'état du PC toutes les secondes
        setInterval(checkPcStatus, 1000);

        // Vérifiez l'état du PC au chargement de la page
        checkPcStatus();
    </script>

    <?php
    if (isset($_GET['ip_address'])) {
        $ip_address = $_GET['ip_address'];

        if (filter_var($ip_address, FILTER_VALIDATE_IP)) {
            // Exécutez la commande ping
            $ping_result = shell_exec("ping -c 1 -W 1 $ip_address");

            // Vérifiez le résultat du ping
            if (strpos($ping_result, '1 received') !== false) {
                echo json_encode(['status' => 'on']);
            } else {
                echo json_encode(['status' => 'off']);
            }
        } else {
            echo json_encode(['status' => 'invalid']);
        }
        exit;
    }
    ?>
</body>
</html>
