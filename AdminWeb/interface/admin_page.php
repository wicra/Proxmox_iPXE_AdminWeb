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

    /////////////////////////////////////////////////////////
    //                        REFRESH                     //
    /////////////////////////////////////////////////////////
    if(isset($_POST['refresh'])){
        //header("Refresh:0");
        header('location: chargement.php');
        exit();
    }

    /////////////////////////////////////////////////////////
    //              DHCP STATUS / STOP / START             //
    /////////////////////////////////////////////////////////
    function getDHCPStatusClass() {
        $status = shell_exec('../shell/status_server_dhcp.sh');
        return trim($status) == 'active' ? 'active' : 'inactive';
    }

    if (isset($_POST['reshell'])) {
        $currentStatus = trim(shell_exec('../shell/status_server_dhcp.sh'));
        if ($currentStatus == 'active') {
            shell_exec('../shell/stop_server_dhcp.sh');
        } else {
            shell_exec('../shell/boot_server_dhcp.sh');
        }
        // Rediriger pour éviter la resoumission du formulaire
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Interface Conf</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- ICON -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <!-- CSS -->
        <link rel="stylesheet" href="styles/style.css">

        <!-- AJAX -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- FAVICON -->
        <link id="favicon" rel="icon"  href="img/administrateu.png">
    </head>

    <body>
        <!-- NAV BARRE -->
        <div class="execute_scrip_conteneur">
            <h1 class="execute_titre">déploiement d'images</h1>

            <div class="nav_barre">
                <!-- INTERFACE CONF MASQUAGE/ AFFICHAGE  -->
                <button class="nav_button_ip_range" id="nav_button_ip_range" type="button"  onclick="masquer_interface_conf()">Range ip</button>
                <script>
                    // MASQUER AU CLICK DU BOUTON OU AFFICHER 
                    function masquer_interface_conf(){
                        var element = document.getElementById('conteneur_admin_conf');
                        if(element.style.display === 'none'){
                            element.style.display = 'block';

                        }else{
                            element.style.display = 'none'
                        }
                    }
                    
                    // MASQUER APRES INACTIVITE
                    document.addEventListener("DOMContentLoaded", function() {
                        var targetElement = document.getElementById('conteneur_admin_conf');
                        var timeout;

                        function hideElement() {
                            targetElement.style.display = 'none';
                        }
                        function resetTimer() {
                            clearTimeout(timeout);
                            timeout = setTimeout(hideElement, 50000);
                        }
                        targetElement.addEventListener('mouseover', function() {
                            resetTimer();
                        });
                        targetElement.addEventListener('mouseout', function() {
                            resetTimer();
                        });
                        resetTimer();
                    });
                </script>

                <!-- ATTRIBUTION MASQUAGE/ AFFICHAGE TABLEAU -->
                <button class="nav_button_attribution" id="nav_button_attribution" type="button"  onclick="masquer()">Attribution ip</button>
                <script>
                    function masquer(){
                        var element = document.getElementById('conteneur_tableau_historique_dhcp');
                        if(element.style.display === 'none'){
                            element.style.display = 'block';

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
                </form>

                <!-- DECONNECTION -->
                <form  method='POST'>
                    <button type="submit" class="button_deconnection"  name='deconnection' >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>    
                
                <!-- REBOOT SERVER DHCP -->
                <form method="post">
                    <div class="tooltip_container">
                        <button type="submit" class="nav_button_shell <?php echo getDHCPStatusClass(); ?>" name="reshell">
                            <i class="fa-solid fa-power-off"></i>
                        </button>

                        <div class="tooltip">
                            <p>Server Dhcp</p>
                        </div>
                    </div>
                </form>

                <?php 

                ?> 
                
                <!-- AJOUT USERS OU SUPPRESSION USERS -->  
                <a class="user_add_del" href="connexion/users_add_del.php"><i class="fa-solid fa-user-plus"></i></a>

                <!-- THEME -->		
                <div class="theme">
                    <div class="container">
                        <label class="toggle" for="switch">
                            <input id="switch" class="input" type="checkbox">

                            <div class="icon icon--moon">
                                <svg height="32" width="32" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" fill-rule="evenodd"></path>
                                </svg>
                            </div>

                            <div class="icon icon--sun">
                                <svg height="32" width="32" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"></path>
                                </svg>
                            </div>
                        </label>
                    </div>
                    <script>
                        /////////////////////////////////////////////////////////
                        //                    THEMES SCRIPT                    //
                        /////////////////////////////////////////////////////////
                        document.addEventListener('DOMContentLoaded', function () {
                            const themeSwitch = document.getElementById('switch');

                            themeSwitch.addEventListener('change', function () {
                                if (this.checked) {
                                    // Thème sombre
                                    document.documentElement.style.setProperty('--CouleurFont', '#f0ead2');
                                    document.documentElement.style.setProperty('--CouleurPrimaire', '#393e41');
                                    document.documentElement.style.setProperty('--CouleurSecondaire', '#d7716b');
                                    document.documentElement.style.setProperty('--Couleur4', '#6ede8a');
                                    document.documentElement.style.setProperty('--Couleur5', '#a98467');
                                    document.documentElement.style.setProperty('--Couleur6', '#a9927d');
                                    document.documentElement.style.setProperty('--CouleurHover', '#d68b45d0');
                                } else {
                                    // Thème clair
                                    document.documentElement.style.setProperty('--CouleurFont', '#ffffff');
                                    document.documentElement.style.setProperty('--CouleurPrimaire', '#393e41');
                                    document.documentElement.style.setProperty('--CouleurSecondaire', '#a44a3f');
                                    document.documentElement.style.setProperty('--Couleur4', '#67b77b');
                                    document.documentElement.style.setProperty('--Couleur5', '#afbb77');
                                    document.documentElement.style.setProperty('--Couleur6', '#cbdfbd');
                                    document.documentElement.style.setProperty('--CouleurHover', '#f19c79');
                                }
                            });
                        });
                    </script>
                </div>
            </div>
        </div>  
        
        <!-- CONFIGURATION SERVER -->
        <div id="conteneur_admin_conf">
            <h1 class="admin_conf_titre">Configuration serveur</h1>
            <?php include("conf/conf_ip_range.php");?>
            <?php include("conf/conf_boot_unknown.php");?>
            <?php include("conf/conf_upload_new_disk.php");?>
        </div>

        <!-- CONTENEUR ANIMATION MODIF IP REUSSI -->
        <div id="emoji-container"></div>  

        <!-- CONTENEUR NOTIF -->
        <div id="notification-container"></div>
        
        <!-- CONFIGURATION ANSWER.TOML -->
        <div id="conteneur_answer_toml_conf">
            <h1 class="answer_toml_conf_titre">Configuration de Answer.toml</h1>
            <?php include("conf/conf_answer_toml.php");?>
        </div>
        
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
                            notif.className = \"error\";
                            notif.innerHTML = \"
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
                            \";
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
            include("include/link.php");
            
            $config = file_get_contents($file_path_admin);

            if ($config === false) {
                notif("Impossible de lire le fichier de configuration.");
            }
            //recupère le nom , mac , ip
            $pattern = '/host\s+([a-zA-Z0-9_-]+)\s*\{[^}]*hardware\s+ethernet\s+([0-9a-f:]+);[^}]*fixed-address\s+([0-9.]+);[^}]*\}/mi';

            if (preg_match_all($pattern, $config, $matches, PREG_SET_ORDER)) {
                echo "<div class=\"tableau_hosts_dhcp\">
                        <h1 class=\"tableau_hosts_dhcp_titre\">Les clients connus</h1>
                        <div class=\"tableau_conteneur\">
                            <table >
                                <tr class=\"tableau\">
                                    <th class=\"col_header_name\" >hôte</th>
                                    <th class=\"col_header_etat\">Etat</th>
                                    <th class=\"col_header_disk\">Disk</th>
                                    <th class=\"col_header_mac\">@ MAC</th>
                                    <th class=\"col_header_ip_fixe\">@ IP_fixe</th>
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
                $file = file("../shell/ipScan.txt");

                function verifEtat($file,$ip_address,$actif ,$eteint){
                    shell_exec('../shell/ipScan.sh');
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

                /////////////////////////////////////////////////////////
                //            SCANNER LES IMAGES DISPONIBLE            //
                /////////////////////////////////////////////////////////
                shell_exec('../shell/diskScan.sh');
                $disks = file('../shell/diskScan.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 

                /////////////////////////////////////////////////////////
                //          AFFICHAGE COMPTENUE DANS LE TABLEAU        //
                /////////////////////////////////////////////////////////
                foreach ($matches as $match) {
                    $host_name = $match[1];
                    $hardware_ethernet = $match[2];
                    $fixed_address = $match[3];

                    // variable de pc_state
                    $actif="<i style = \"color : var(--Couleur4);\" class=\"fa-solid fa-circle-check\"></i>";
                    $eteint="<i style = \"color : var(--CouleurSecondaire);\" class=\"fa-solid fa-plug\"></i>";

                    // verif etat du pc
                    $pc_state = verifEtat($file ,$fixed_address,$actif,$eteint);

                    // Verif demarage pc inconnue
                    $result = checkIncludeAndHostName($file_path_admin, $host_name, "/etc/dhcp/condition_pxe_boot_choix.conf");

                    // Vm ou pas
                    $verif_vm = verify_mac_address($host_name,"fa:ca:de", $file_path_admin);

                    // Ajouter un lien si pc actif 
                    $link = ($pc_state == $actif) ? "<a href=\"https://{$fixed_address}:8006\" target=\"_blank\">" : "";
                    $link_close = ($pc_state == $actif) ? "</a>" : "";

                    echo    "<tr class=\"tableau\" >
                                <td class=\"col_name\">
                                    <i class=\"fa-solid fa-pen-to-square open_edit_host_name\"></i>
                                    <h4 class=\"host_name\">$link{$host_name}$link_close</h4>
                            
                                    <form class=\"edit_host_form\" method=\"post\" id=\"edit_host_form_{$host_name}\">
                                        <input type=\"hidden\" name=\"old_host_name\" value=\"{$host_name}\">
                                        <input class=\"new_host_name\" type=\"text\" name=\"new_host_name\" placeholder=\"New\"  minlength=\"1\" maxlength=\"15\" required>
                                        <i class=\"fa-solid fa-check valide_edit_host_name\" style=\"cursor: pointer;\" data-host-name=\"{$host_name}\"></i>
                                        <i class=\"fa-solid fa-hand-point-left close_host_name_form\"></i>
                                    </form>
                                </td>

                                <td class=\"col_etat\">";
                                    if($pc_state == $eteint){
                                        // ALLUMER LE PC
                                        echo"
                                            <div class=\"col_etat_conteneur\">
                                                <h4 class=\"etat\">$pc_state</h4>
                                                <form method=\"post\" action=\"conf/conf_wake_on_lan.php\" id=\"demarage_choix_admin_form_{$host_name}\">
                                                    <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                                    <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                                    <input type=\"hidden\" name=\"ip_address\" vvalue=\"{$fixed_address}\">
                                                    <button class=\"col_etat_bouton\"  type=\"submit\"><i class=\"fa-solid fa-power-off\"></i></button>
                                                </form>
                                            </div>
                                        </td>";
                                    }
                                else{
                                    // ETEINDRE LE PC
                                    echo "  
                                        <div class=\"col_etat_conteneur\">
                                                <h4 class=\"etat\">$pc_state</h4>
                                                <form method=\"post\" action=\"conf/conf_shut_down.php\" id=\"demarage_choix_admin_form_{$host_name}\">
                                                    <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                                    <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                                    <input type=\"hidden\" name=\"ip_address\" vvalue=\"{$fixed_address}\">
                                                    <button class=\"col_shut_down_bouton\"  type=\"submit\"><i class=\"fa-solid fa-power-off\"></i></button>
                                                </form>
                                            </div>
                                        </td>";
                                    ;
                                }

                        echo"   <td class=\"col_disk\">";
                                    // VM OU PAS
                                    if ($verif_vm === "oui") {
                        echo           "<div class=\"tooltip_container\">
                                                <i class=\"fa-solid fa-circle-arrow-up\"></i>
                                                <div class=\"tooltip\">
                                                    <p>Disk sur sa machine physique au dessus <i class=\"fa-regular fa-heart\"></i></p>
                                                </div>
                                        </div>"; 
                                    }
                                    else{
                        echo  "
                                        <form class=\"disk_choix\" action=\"conf/conf_choix_disk.php\" method=\"post\">
                                            <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                            <input type=\"hidden\" name=\"ip_address\" vvalue=\"{$fixed_address}\">

                                            <select id=\"choices\" name=\"choice\">";
                                                if (empty($disks)) {
                                                    echo "<option value=\"\">indisponible</option>";
                                                } 
                                                else {
                                                    // Afficher chaque element du fichier diskSan.txt en option
                                                    foreach ($disks as $disk) {
                                                        echo "<option value=\"{$disk}\">" . ucfirst($disk) . "</option>";
                                                    }
                                                }
                        echo"               </select>
                                            <i class=\"fa-solid fa-check option_disk_submit style=\"cursor: pointer;\"></i>
                                        </form>";
                                    }
                        echo"   </td>

                                <td class=\"col_mac\">{$hardware_ethernet}</td>

                                <td class=\"col_ip_fixe\">
                                    <div class=\"col_conteneur_ip_conf\">
                                        <h4 class=\"ip_address\">{$fixed_address}</h4>

                                        <i class=\"fa-solid fa-pen-to-square open_change_ip\"></i>
                                        <div class=\"formulaire_ip_conteneur\">
                                            <form class=\"ip_change_form\">
                                                <input type=\"hidden\" name=\"host_name\" value=\"{$host_name}\">
                                                <input type=\"hidden\" name=\"mac_address\" value=\"{$hardware_ethernet}\">
                                                <input class=\"formulaire_ip\" type=\"text\" name=\"new_ip\" placeholder=\"Nouvelle IP\">
                                                <i class=\"fa-solid fa-check valide_change_ip\" style=\"cursor: pointer;\" data-host-name=\"{$host_name}\"></i>
                                                <i class=\"fa-solid fa-hand-point-left close_change_ip\"></i>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                                <td class=\"col_demarage\">";
                                    // VM OU PAS
                                    if ($verif_vm === "oui") {
                                        echo "<div class=\"tooltip_container\">
                                            <i class=\"fa-solid fa-circle-arrow-up\"></i>
                                                <div class=\"tooltip\">
                                                    <p>Demarage sur sa machine physique, au dessus <i class=\"fa-regular fa-heart\"></i></p>
                                                </div>
                                            </div>"; 
                                        

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
                                                            <span class=\"switch-x-unchecked\"><span class=\"switch-x-hiddenlabel\">Unchecked: </span>local</span>
                                                            <span class=\"switch-x-checked\"><span class=\"switch-x-hiddenlabel\">Checked: </span>reseau</span>
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
                echo "      </table>
                        </div>
                    </div>";
            } else {
                notif("Aucun hôte trouvé dans le fichier de configuration.");
            }
        ?>

        <!-- LES SCRIPTS JS EN AJAX --->
        <script>
            $(document).ready(function() {
                /////////////////////////////////////////////////////////
                //       AFFICHER LE FORMULAIRE DE CHANGEMENT IP       //
                /////////////////////////////////////////////////////////
                $('.open_change_ip').click(function(event) {
                    event.preventDefault();
                    var $icon = $(this);
                    var $formContainer = $icon.next('.formulaire_ip_conteneur');
                    var $ip_address = $icon.siblings('.ip_address');

                    
                    //changement Ip une a la fois
                    $('.formulaire_ip_conteneur').hide();
                    $('.open_change_ip').show();
            
                    $formContainer.show();
                    $ip_address.hide();
                    $icon.hide();
                });
            
                /////////////////////////////////////////////////////////
                //         MASQUAGE FORMULAIRE DE CHANGEMENT IP        //
                /////////////////////////////////////////////////////////
                $('.close_change_ip').click(function(event) {
                    event.preventDefault();
                    var $closeIcon = $(this);
                    var $formContainer = $closeIcon.closest('.formulaire_ip_conteneur');
                    var $icon = $formContainer.prev('.open_change_ip');
                    var $ip_address = $icon.siblings('.ip_address');
                    $formContainer.hide();
                    $ip_address.show();
                    $icon.show();
                });

                $('i.valide_change_ip').click(function() {
                    // Déclencher le clic sur le bouton de soumission du formulaire
                    $(this).closest('.ip_change_form').submit();
                });

                /////////////////////////////////////////////////////////
                //      GESTION DE SOUMISSION FORMULAIRE IP EN AJAX    //
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
                        var url = $(this).is(':checked') ? "conf/conf_choix_user_demarage.php" :  "conf/conf_local_demarage.php";
                        
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: formData,
                            success: function(response) {
                                $('#emoji-container').html(response);
                                $('.emoji-container').addClass('animate');
                            },
                            error: function(xhr, status, error) {
                                alert("Une erreur s'est produite lors du démarrage.");
                                console.error(error);
                            }
                        });
                    });
                });

                /////////////////////////////////////////////////////////
                //         GESTION DE SUPPRESSION DE HOST EN AJAX      //
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
                //    AFFICHER LE FORMULAIRE DE CHANGEMENT HOST NAME   //
                /////////////////////////////////////////////////////////
                $('.open_edit_host_name').click(function() {
                    // Masquer tous les autres formulaires et réafficher les icônes et noms d'hôtes
                    $('.edit_host_form').hide();
                    $('.open_edit_host_name').show();
                    $('.host_name').show();

                    var $icon = $(this);
                    var $hostName = $icon.siblings('.host_name');
                    var $form = $icon.siblings('.edit_host_form');

                    $icon.hide();
                    $hostName.hide();
                    $form.show();
                });

                /////////////////////////////////////////////////////////
                //    MASQUER LE FORMULAIRE DE CHANGEMENT HOST NAME    //
                /////////////////////////////////////////////////////////
                $('.edit_host_form').on('submit', function(event) {
                    event.preventDefault();
                    var $form = $(this);
                    var $icon = $form.siblings('.open_edit_host_name');
                    var $hostName = $form.siblings('.host_name');

                    $form.hide();
                    $icon.show();
                    $hostName.show();
                });

                /////////////////////////////////////////////////////////
                //            ANNULER LE CHANGEMENT HOST NAME          //
                /////////////////////////////////////////////////////////
                $('.close_host_name_form').click(function() {
                    var $form = $(this).closest('.edit_host_form');
                    var $icon = $form.siblings('.open_edit_host_name');
                    var $hostName = $form.siblings('.host_name');

                    $form.hide();
                    $icon.show();
                    $hostName.show();
                });

                /////////////////////////////////////////////////////////
                //     GESTION DE CHANGEMENT DE HOST NAME  EN AJAX    //
                /////////////////////////////////////////////////////////
                $(document).ready(function() {
                    // Fonction pour soumettre le formulaire lorsqu'on clique sur l'icône
                    $('i.valide_edit_host_name').click(function() {
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

                /////////////////////////////////////////////////////////
                //           SOUMMISSION FORMULAIRE CHOIX DISK         //
                /////////////////////////////////////////////////////////
                $('i.option_disk_submit').click(function() {
                    // Déclencher le clic sur le bouton de soumission du formulaire
                    $(this).closest('.disk_choix').submit();
                });
            });
        </script>

        <!-- FOOTER -->
        <footer>
            <h4>Interface d'administration réseaux et de distribution d'image et de disque.</h4>
            <a href="https://github.com/wicra/Proxmox_iPXE_AdminWeb" target="_blank">Wicramachine sergio</a>
        </footer>
    </body>
</html>
