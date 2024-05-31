
<?php
/////////////////////////////////////////////////////////
//                        SESSION                     //
/////////////////////////////////////////////////////////
session_start();
// Verif si user connecter si la variable $_SESSION comptien le username 
if(!isset($_SESSION["login"])){
    header("location: ../index.php");
exit(); 
}

// déconnection
if(isset($_POST['deconnection'])){
    session_destroy();
    header('location: ../index.php');
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=, initial-scale=1.0">
        <title>Interface Conf</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- ICON -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <!-- CSS -->
        <link rel="stylesheet" href="styles/style.css">

        <!-- AJAX -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>

    <body>
        <!-- NAV BARRE -->
        <div class="execute_scrip_conteneur">
            <h1 class="execute_titre">déploiement d'images</h1>

            <div class="nav_barre">
                <!-- ATTRIBUTION MASQUAGE/ AFFICHAGE TABLEAU -->
                <button class="nav_button_attribution" id="nav_button_attribution" type="button"  onclick="masquer()">Attribution ip</button>
                <script>
                    function masquer(){
                        var element = document.getElementById('tableau_historique_dhcp');
                        if(element.style.display === 'none'){
                            element.style.display = 'table';

                        }else{
                            element.style.display = 'none'
                        }
                    }
                </script>
                <!-- REFRESH PAGE -->
                <form action="" method="post">
                    <button class="nav_refresh" name="refresh" id='refresh'>
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <?php
                        if(isset($_POST['refresh'])){
                            header("Refresh:0");
                        }
                    ?>
                </form>

                <!-- DECONNECTION -->
                <form  method='POST'>
                    <button type="submit" class="button_deconnection"  name='deconnection' >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>       
                
                <!-- USER CONNECTE -->
                <?php
                    include('connection/connection_db.php');
                
                    $username = mysqli_real_escape_string($conn, $_SESSION["login"]);
                    $requete = "SELECT login = '$username' FROM users_admin  ";
                    $result = mysqli_query($conn, $requete);
                    $user_connect =$_SESSION["login"];
                    if ($result) {
                        echo "
                            <div class=\"nav_user\">
                                <i class=\"fa-solid fa-user-tie\"></i>
                                <h1 class=\"nav_user_connect\">$user_connect</h1>
                            </div>                            
                        " ;
                    };
                ?>
            </div>
        </div>  
        
        <!-- CONTENEUR ANIMATION MODIF IP REUSSI -->
        <div id="emoji-container"></div>  

        <!-- CONTENEUR NOTIF -->
        <div id="notification-container"></div>
        
        <!-- AJOUT TABLEAU DISTORIQUE DHCP -->
        <?php include("dhcpd_attribution_auto.php");?>
        
        <?php
        
            /////////////////////////////////////////////////////////
            //                   LES NOTIFICATIONS                 //
            /////////////////////////////////////////////////////////
            function notif($message) {
                echo "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var notificationContainer = document.getElementById('notification-container');
                        if (notificationContainer) {
                            var notif = document.createElement('div');
                            notif.className = 'error';
                            notif.innerHTML = `
                                <div class=\"error__icon\">
                                    <svg fill=\"none\" height=\"24\" viewBox=\"0 0 24 24\" width=\"24\" xmlns=\"http://www.w3.org/2000/svg\">
                                        <path d=\"m13 13h-2v-6h2zm0 4h-2v-2h2zm-1-15c-1.3132 0-2.61358.25866-3.82683.7612-1.21326.502
                                        55-2.31565 1.23915-3.24424 2.16773-1.87536 1.87537-2.92893 4.41891-2.92893 7.07107 0 2.6522
                                        1.05357 5.1957 2.92893 7.0711.92859.9286 2.03098 1.6651 3.24424 2.1677 1.21325.5025 2.51363
                                        .7612 3.82683.7612 2.6522 0 5.1957-1.0536 7.0711-2.9289 1.8753-1.8754 2.9289-4.4189 2.9289-
                                        7.0711 0-1.3132-.2587-2.61358-.7612-3.82683-.5026-1.21326-1.2391-2.31565-2.1677-3.24424-.92
                                        86-.92858-2.031-1.66518-3.2443-2.16773-1.2132-.50254-2.5136-.7612-3.8268-.7612z\" fill=\"#393a37\">
                                        </path>
                                    </svg>
                                </div>
                                <div class=\"error__title\">$message</div>
                            `;
                            notificationContainer.appendChild(notif);
                            setTimeout(function() { notif.style.display = 'none'; }, 4000); // Fermer la notification après 2 secondes
                        }
                    });
                </script>
                ";
            }
            // recuperer les notif stocker dans la sessions 
            if (!empty($_SESSION['notifications'])) {
                foreach ($_SESSION['notifications'] as $message) {
                    notif($message);
                }
                unset($_SESSION['notifications']);
            }
            
            /////////////////////////////////////////////////////////
            //            TABLEAU D'AFFICHAGE DES HOSTS           //
            /////////////////////////////////////////////////////////
            include("connection/link.php");

            $config = file_get_contents($file_path_admin);

            if ($config === false) {
                notif("Impossible de lire le fichier de configuration.");
            }
            //recupère le nom , mac , ip
            $pattern = '/host\s+([a-zA-Z0-9_-]+)\s*\{[^}]*hardware\s+ethernet\s+([0-9a-f:]+);[^}]*fixed-address\s+([0-9.]+);[^}]*\}/mi';

            if (preg_match_all($pattern, $config, $matches, PREG_SET_ORDER)) {
                echo "<div class=\"tableau_conteneur\">
                        
                        <table >
                            <tr class=\"tableau\">
                                <th class=\"col_header_name\" >hôte</th>
                                <th class=\"col_header_etat\">Etat</th>
                                <th class=\"col_header_os\">OS</th>
                                <th class=\"col_header_mac\">@ MAC</th>
                                <th class=\"col_header_ip_fixe\">@ IP_fixe</th>
                                <th class=\"col_header_modif_ip\">@ IP_conf</th>
                                <th class=\"col_header_demarage\">Demarage</th>
                                <th class=\"col_header_delete_host\">Sup</th>
                            </tr>";

                /////////////////////////////////////////////////////////
                //             FONCTION DE VERIF VM OU PAS            //
                /////////////////////////////////////////////////////////
                function verify_mac_address($host_name, $mac_prefix, $file_path_admin) {
                    $file_content = file_get_contents($file_path_admin);

                    if ($file_content === false) {
                        return "Impossible de lire le fichier de configuration.";
                    }

                    // Rechercher l'adresse MAC dans le fichier
                    $pattern = "/host\s+{$host_name}\s*{[^}]*hardware ethernet\s+([0-9a-f:]{17});/";
                    if (preg_match($pattern, $file_content, $matches)) {
                        $found_mac_address = $matches[1];

                        // Vérifier si l'adresse MAC commence par la séquence spécifiée
                        if (strpos($found_mac_address, $mac_prefix) === 0) {
                            return "oui";
                        } else {
                            return "non";
                        }
                    }
                }
                
                /////////////////////////////////////////////////////////
                //            FONCTION DE VERIF ETAT SWITCH          //
                /////////////////////////////////////////////////////////
                function checkIncludeAndHostName($file_path_admin, $host_name_to_find, $include_to_find) {
                    $content = file_get_contents($file_path_admin);
                    $pattern = '/host\s+' . preg_quote($host_name_to_find, '/') . '\s*{[^}]+include\s+"([^"]+)"[^}]*}/s';
                    if (preg_match($pattern, $content, $match)) {
                        $host_include = $match[1];
                        if ($host_include === $include_to_find) {
                            return 1;
                        } else {
                            return 0;
                        }
                    } else {
                        return "hôte non trouvé";
                    }
                }

                /////////////////////////////////////////////////////////
                //            FONCTION DE VERIF ETAT MACHINE           //
                /////////////////////////////////////////////////////////
                //shell_exec('../shell/ipScan.sh');//Changer le propriétaire du dossier projet
                $file = file("../shell/ipScan.txt");

                function verifEtat($file,$ip_address,$actif ,$eteint){
                    $pc_state = $eteint;
                    foreach($file as $ligne){
                        $ligne = trim($ligne);
                        if($ligne == trim($ip_address)){
                            $pc_state = $actif;
                            break;
                        }
                    }
                    return $pc_state;
                }

                //AFFICHAGE COMPTENUE DANS LE TABLEAU
                foreach ($matches as $match) {
                    $host_name = $match[1];
                    $hardware_ethernet = $match[2];
                    $fixed_address = $match[3];

                    $actif="<i style = \"color : var(--Couleur4);\" class=\"fa-solid fa-circle-check\"></i>";
                    $eteint="<i style = \"color : var(--CouleurSecondaire);\" class=\"fa-solid fa-plug\"></i>";

                    $pc_state = verifEtat($file ,$fixed_address,$actif,$eteint);
                    $result = checkIncludeAndHostName($file_path_admin, $host_name, "condition_pxe_boot_local.conf");
                    $verif_vm = verify_mac_address($host_name,"bc:24:11", $file_path_admin);

                    $link = ($pc_state == $actif) ? "<a href=\"https://{$fixed_address}:8006\">" : "";
                    $link_close = ($pc_state == $actif) ? "</a>" : "";

                    echo "<tr class=\"tableau\" >
                            <td class=\"col_name\">
                                <i class=\"fa-solid fa-pen-to-square\"></i>
                                <h4 class=\"host_name\">$link{$host_name}$link_close</h4>
                        
                                <form class=\"edit_host_form\" method=\"post\" id=\"edit_host_form_{$host_name}\">
                                    <input type=\"hidden\" name=\"old_host_name\" value=\"{$host_name}\">
                                    <input class=\"new_host_name\" type=\"text\" name=\"new_host_name\" placeholder=\"New\"  minlength=\"1\" maxlength=\"15\" required>
                                    <button type=\"submit\" name=\"edit_host_button\" style=\"display: none;\"></button>
                                    <i class=\"fa-solid fa-check\" style=\"cursor: pointer;\" data-host-name=\"{$host_name}\"></i>
                                    <i class=\"fa-solid fa-hand-point-left\"></i>
                                </form>
                            </td>

                            <td class=\"col_etat\">$pc_state</td>

                            <td class=\"col_os\"><i class=\"fa-brands fa-windows\"></i></td>

                            <td class=\"col_mac\">{$hardware_ethernet}</td>

                            <td class=\"col_ip_fixe\">{$fixed_address}</td>

                            <td class=\"col_ip_fixe\">
                                <i class=\"fa-solid fa-gears\"></i>
                                <div class=\"formulaire_ip_conteneur\">
                                    <form class=\"ip_change_form\">
                                        <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                        <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                        <input class=\"formulaire_ip\" type=\"text\" name=\"new_ip\" placeholder=\"Nouvelle IP\">
                                        <button class=\"button\" type=\"submit\">
                                            <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-arrow-repeat\" viewBox=\"0 0 16 16\">
                                                <path d=\"M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25
                                                0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z\"></path>
                                                <path fill-rule=\"evenodd\" d=\"M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0
                                                0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z\"></path>
                                            </svg>
                                            Modifier
                                        </button>
                                        <i class=\"fa-solid fa-xmark\"></i>
                                    </form>
                                </div>
                            </td>

                            <td class=\"col_demarage\">";
                                // VM OU PAS
                                if ($verif_vm === "oui") {
                                    echo "<i class=\"fa-solid fa-triangle-exclamation\"></i>"; 
                                }
                                else{
                                    echo  " 
                                    <form class=\"demarage_choix_admin\" method=\"post\" id=\"demarage_choix_admin_form_{$host_name}\">
                                        <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                        <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                        <input type=\"hidden\" name=\"ip_address\" value=\"{$fixed_address}\">
                                        <button class=\"col_choix_admin\" type=\"submit\" name=\"demarage_choix_admin\" style=\"display: none;\"></button>
                                        <div class=\"checkbox-wrapper-35\">
                                                <input name=\"switch\" id=\"switch_{$host_name}\" type=\"checkbox\" class=\"switch\" data-host-name=\"{$host_name}\" ";
                                                if ($result === 1) {
                                                    echo "checked"; // Si $result est 1, le switch est activé par défaut
                                                }
                                                echo ">
                                                <label for=\"switch_{$host_name}\">
                                                    <span class=\"switch-x-text\"></span>
                                                    <span class=\"switch-x-toggletext\">
                                                        <span class=\"switch-x-unchecked\"><span class=\"switch-x-hiddenlabel\">Unchecked: </span>default</span>
                                                        <span class=\"switch-x-checked\"><span class=\"switch-x-hiddenlabel\">Checked: </span>local</span>
                                                    </span>
                                                </label>
                                        </div>
                                    </form>";} 
                            echo "</td>

                            <td class=\"col_delete_host\">
                                <form class=\"delete_host_form\" method=\"post\" id=\"delete_host_form\">
                                    <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                    <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                    <input type=\"hidden\" name=\"ip_address\" value=\"{$fixed_address}\">
                                    <button class=\"col_delete_host_form\" type=\"submit\" name=\"delete_host_button\" style=\"display: none;\"></button>
                                    <i class=\"fa-solid fa-trash\" style=\"cursor: pointer;\" data-host-name=\"{$host_name}\"></i>
                                </form>
                            </td>
                        </tr>";
                }
                echo "</table>
                    </div>";
            } else {
                notif("Aucun hôte trouvé dans le fichier de configuration.");
            }

        ?>

        <script>
 
                
            $(document).ready(function() {
                /////////////////////////////////////////////////////////
                //    AFFICHER LE FORMULAIRE DE CHANGEMENT HOST NAME   //
                /////////////////////////////////////////////////////////
                $('.fa-pen-to-square').click(function() {
                    // Masquer tous les autres formulaires et réafficher les icônes et noms d'hôtes
                    $('.edit_host_form').hide();
                    $('.fa-pen-to-square').show();
                    $('.host_name').show();

                    var $icon = $(this);
                    var $hostName = $icon.siblings('.host_name');
                    var $form = $icon.siblings('.edit_host_form');

                    $icon.hide();
                    $hostName.hide();
                    $form.show();
                });

                /////////////////////////////////////////////////////////
                //    MASUQER LE FORMULAIRE DE CHANGEMENT HOST NAME    //
                /////////////////////////////////////////////////////////
                $('.edit_host_form').on('submit', function(event) {
                    event.preventDefault();
                    var $form = $(this);
                    var $icon = $form.siblings('.fa-pen-to-square');
                    var $hostName = $form.siblings('.host_name');

                    $form.hide();
                    $icon.show();
                    $hostName.show();
                });

                /////////////////////////////////////////////////////////
                //            ANNULER LE CHANGEMENT HOST NAME          //
                /////////////////////////////////////////////////////////
                $('.fa-hand-point-left').click(function() {
                    var $form = $(this).closest('.edit_host_form');
                    var $icon = $form.siblings('.fa-pen-to-square');
                    var $hostName = $form.siblings('.host_name');

                    $form.hide();
                    $icon.show();
                    $hostName.show();
                });

                /////////////////////////////////////////////////////////
                //       AFFICHER LE FORMULAIRE DE CHANGEMENT IP       //
                /////////////////////////////////////////////////////////
                $('.fa-gears').click(function(event) {
                    event.preventDefault();
                    var $icon = $(this);
                    var $formContainer = $icon.next('.formulaire_ip_conteneur');
            
                    //changement Ip une a la fois
                    $('.formulaire_ip_conteneur').hide();
                    $('.fa-gears').show();
            
                    $formContainer.show();
                    $icon.hide();
                });
            
                /////////////////////////////////////////////////////////
                //         MASQUAGE FORMULAIRE DE CHANGEMENT IP        //
                /////////////////////////////////////////////////////////
                $('.fa-xmark').click(function(event) {
                    event.preventDefault();
                    var $closeIcon = $(this);
                    var $formContainer = $closeIcon.closest('.formulaire_ip_conteneur');
                    var $icon = $formContainer.prev('.fa-gears');
                    $formContainer.hide();
                    $icon.show();
                });
            
                /////////////////////////////////////////////////////////
                //        GESTION DE SOUMISSION FORMULAIRE EN AJAX     //
                /////////////////////////////////////////////////////////
                $('.ip_change_form').submit(function(event) {
                    event.preventDefault();
                    var formData = $(this).serialize();
                    $.ajax({
                        type: "POST",
                        url: "conf/conf_ip_dhcp.php",
                        data: formData,
                        success: function(response) {
                            // Mettre à jour le contenu de la balise avec l'emoji
                            $('#emoji-container').html(response);
                            // Ajouter la classe pour l'animation
                            $('.emoji-container').addClass('animate');
                            // Actualiser la page après 2 secondes
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr, status, error) {
                            // Gérer les erreurs
                            alert("Une erreur s'est produite lors de la modification de l'adresse IP.");
                            console.error(error);
                        }
                    });
                });
            
                /////////////////////////////////////////////////////////
                //   GESTION DE SWITCH  FORMULAIRE DEMARRAGE EN AJAX   //
                /////////////////////////////////////////////////////////
                $(document).ready(function() {
                    $('.switch').change(function() {
                        var $form = $(this).closest('form');
                        var formData = $form.serialize();
                        var url = $(this).is(':checked') ? "conf/conf_local_demarage.php" : "conf/conf_choix_user_demarage.php";
                        
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: formData,
                            success: function(response) {
                                $('#emoji-container').html(response);
                                $('.emoji-container').addClass('animate');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            },
                            error: function(xhr, status, error) {
                                alert("Une erreur s'est produite lors du démarrage.");
                                console.error(error);
                            }
                        });
                    });
                });

            
                /////////////////////////////////////////////////////////
                //     GESTION DE SUPPRESSION DE HOST NAME  EN AJAX    //
                /////////////////////////////////////////////////////////
                $(document).ready(function() {
                    // Fonction pour soumettre le formulaire lorsqu'on clique sur l'icône
                    $('i.fa-trash').click(function() {
                        var hostName = $(this).data('host-name');
                        $('#delete_host_form').submit();
                    });

                    // Gestionnaire de soumission du formulaire
                    $('.delete_host_form').submit(function(event) {
                        event.preventDefault();
                        var formData = $(this).serialize();
                        $.ajax({
                            type: "POST",
                            url: "conf/conf_delete_machine.php",
                            data: formData,
                            success: function(response) {
                                // Mettre à jour le contenu de la balise avec l'emoji
                                $('#emoji-container').html(response);
                                // Ajouter la classe pour l'animation
                                $('.emoji-container').addClass('animate');

                                // Actualiser la page après une suppression 
                                document.getElementById('refresh').click();
                            },
                            error: function(xhr, status, error) {
                                // Gérer les erreurs
                                alert("Une erreur s'est produite lors de la modification de l'adresse IP.");
                                console.error(error);
                            }
                        });
                    });
                });



                /////////////////////////////////////////////////////////
                //     GESTION DE CHANGEMENT DE HOST NAME  EN AJAX    //
                /////////////////////////////////////////////////////////

                $(document).ready(function() {
                    // Fonction pour soumettre le formulaire lorsqu'on clique sur l'icône
                    $('i.fa-check').click(function() {
                        var hostName = $(this).data('host-name');
                        $('#edit_host_form_' + hostName).submit();
                    });

                    // Gestionnaire de soumission du formulaire
                    $('.edit_host_form').submit(function(event) {
                        event.preventDefault();
                        var formData = $(this).serialize();
                        $.ajax({
                            type: "POST",
                            url: "conf/conf_change_name_host.php",
                            data: formData,
                            success: function(response) {
                                // Mettre à jour le contenu de la balise avec le message
                                $('#message-container').html(response);

                                // Actualiser la page après la modification du nom d'hôte
                                document.getElementById('refresh').click();
                            },
                            error: function(xhr, status, error) {
                                // Gérer les erreurs
                                alert("Une erreur s'est produite lors de la modification du nom d'hôte.");
                                console.error(error);
                            }
                        });
                    });
                });

            });
        </script>
    </body>
</html>