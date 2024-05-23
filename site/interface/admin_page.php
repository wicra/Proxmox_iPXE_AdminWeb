


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="styles/style.css">
        <!-- ICON -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        <?php

        $file_path = '../../dhcp/dhcpd_hosts.conf';

        $config = file_get_contents($file_path);

        if ($config === false) {
            die("Impossible de lire le fichier de configuration.");
        }

        $pattern = '/host\s+(\w+)\s*\{[^}]*hardware\s+ethernet\s+([0-9a-f:]+);[^}]*fixed-address\s+([0-9.]+);[^}]*\}/mi';

        if (preg_match_all($pattern, $config, $matches, PREG_SET_ORDER)) {
            echo "<div class=\"tableau_conteneur\">
                    <h1 class=\"titre_tableau\">Liste des hôtes DHCP</h1>
                    <table border='1'>
                        <tr class=\"tableau\">
                            <th class=\"col_name\" >Nom de l'hôte</th>
                            <th class=\"col_os\">Hist OS</th>
                            <th class=\"col_mac\">@ MAC</th>
                            <th class=\"col_ip_fixe\">@ IP fixe</th>
                            <th class=\"col_modif_ip\">@ IP conf</th>
                        </tr>";

            foreach ($matches as $match) {
                $host_name = $match[1];
                $hardware_ethernet = $match[2];
                $fixed_address = $match[3];

                echo "<tr class=\"tableau\" >
                        <td class=\"col_name\"><i class=\"fa-solid fa-desktop\"></i>{$host_name}</td>
                        <td class=\"col_os\">
                            <i class=\"fa-brands fa-windows\"></i>
                        </td>
                        <td class=\"col_mac\">{$hardware_ethernet}</td>
                        <td class=\"col_ip_fixe\">{$fixed_address}</td>
                        <td class=\"col_ip_fixe\">
                            <i class=\"fa-solid fa-gears\"></i>
                            <div class=\"formulaire_ip_conteneur\">
                                <form class=\"ip_change_form\">
                                    <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                    <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                    <input class=\"formulaire_ip\" type=\"text\" name=\"new_ip\" placeholder=\"Nouvelle IP\">
                                    <button type=\"submit\">Modifier</button>
                                    <i class=\"fa-solid fa-xmark\"></i>
                                </form>
                            </div>
                        </td>
                    </tr>";
            }

            echo "</table>
                </div>";
        } else {
            echo "Aucun hôte trouvé dans le fichier de configuration.";
        }
        ?>

        <script>
            $(document).ready(function() {
                $('.fa-gears').click(function(event) {
                    event.preventDefault();
                    var $icon = $(this);
                    var $formContainer = $icon.next('.formulaire_ip_conteneur');
                    $formContainer.show();
                    $icon.hide();
                });

                $('.fa-xmark').click(function(event) {
                    event.preventDefault();
                    var $closeIcon = $(this);
                    var $formContainer = $closeIcon.closest('.formulaire_ip_conteneur');
                    var $icon = $formContainer.prev('.fa-gears');
                    $formContainer.hide();
                    $icon.show();
                });

                // Gestion de la soumission du formulaire
                $('.ip_change_form').submit(function(event) {
                    event.preventDefault();
                    var formData = $(this).serialize();
                    $.ajax({
                        type: "POST",
                        url: "conf/conf_ip_dhcp.php",
                        data: formData,
                        success: function(response) {
                            alert(response);
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            alert("Une erreur s'est produite lors de la modification de l'adresse IP.");
                            console.error(error);
                        }
                    });
                });
            });
        </script>

        <style>
        /*TABLEAU D'HOTE*/
        .tableau_conteneur{
            display: flex;
            flex-direction: column;
            justify-content:center;
            align-items:center;
        }

        th{
            text-transform:uppercase;
        }

        .titre_tableau{ 
            font-size: 80px;
            text-transform: uppercase;
        }

        .tableau{
            border: solid var(--CouleurFont);
        }

        .col_name{
            justify-content: center;
            padding: 1vh 2vw;
            font-size: 35px;
            align-items: center;
            display: flex;
        }

        .col_name i{
            padding: 1vh 2vw;
            text-align: center;
            font-size: 35px;
        }

        .col_mac,.col_ip_fixe,.col_modif_ip,.col_os{
            padding: 1vh 2vw;
            text-align: center;
            font-size: 35px;
        }


        .ip_change_form{
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2vw;
        }

        .formulaire_ip_conteneur{
            display:none;
        }

        </style>
     </body>
</html>