
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

    
        


       
        <div class="execute_scrip_conteneur">
            <h1 class="execute_titre">déploiement d'images</h1>

            <div class="nav_barre">

                <button class="nav_button_attribution" id="nav_button_attribution" type="submit"  name="historique_dhcp">Attribution ip</button>
                  
                <form action="" method="post">
                    <button class="nav_refresh" name="refresh">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <?php
                        if(isset($_POST['refresh'])){
                            header("Refresh:0");
                        }
                    ?>
                </form>

                <form  method='POST'>
                    <button type="submit" class="button_deconnection"  name='deconnection' >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>       
                

                <?php
                    include('connection/connection_db.php');
                        // Assurez-vous que $_SESSION["username"] est protégé contre les injections SQL
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
        
        <!-- CONTENEUR ANIMATION -->
        <div id="emoji-container"></div>   

        <!-- Ajout du  tableau contenant l'historique DHCP -->
        <?php include("dhcpd_attribution_auto.php");?>
      



        <?php
            // TABLEAU D'AFFICHAGE DES HOSTS
            $file_path = '../../dhcp/dhcpd_hosts.conf';

            $config = file_get_contents($file_path);

            if ($config === false) {
                die("Impossible de lire le fichier de configuration.");
            }

            $pattern = '/host\s+(\w+)\s*\{[^}]*hardware\s+ethernet\s+([0-9a-f:]+);[^}]*fixed-address\s+([0-9.]+);[^}]*\}/mi';

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
                            </tr>";


                //Fonction pour exécuter un ping vers une adresse IP et renvoyer l'état
                 
                //exec('../shell/ipScan.sh');//Changer le propriétaire du dossier projet
                $file = file("../shell/ipScan.txt");

                function verifEtat($file,$ip_address,$actif ,$eteint){
                    $pc_state = $eteint;
                    foreach($file as $ligne)
                    {
                        $ligne = trim($ligne);
                        if($ligne == trim($ip_address)){
                            $pc_state = $actif;
                            break;
                        }
                    }
                    return $pc_state;
                }



                foreach ($matches as $match) {
                    $host_name = $match[1];
                    $hardware_ethernet = $match[2];
                    $fixed_address = $match[3];

                    $actif="<i style = \"color : var(--Couleur4);\" class=\"fa-solid fa-circle-check\"></i>";
                    $eteint="<i style = \"color : var(--CouleurSecondaire);\" class=\"fa-solid fa-plug\"></i>";

                    $pc_state = verifEtat($file ,$fixed_address,$actif,$eteint);
                    
                    $link = ($pc_state == $actif) ? "<a href=\"https://{$fixed_address}:8006\">" : "";
                    $link_close = ($pc_state == $actif) ? "</a>" : "";

                    echo "<tr class=\"tableau\" >
                            <td class=\"col_name\"><i class=\"fa-solid fa-desktop\"></i>$link{$host_name}$link_close</td>
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
                        </tr>";
                }
                echo "</table>
                    </div>";
            } else {
                echo "Aucun hôte trouvé dans le fichier de configuration.";
            }
        ?>


        <script>
            //MASQUER LE TABLEAU HISTORIQUE OU NON
            let bouton = document.getElementById("nav_button_attribution");
            let tableau = document.getElementById("tableau_historique_dhcp");
            bouton.addEventListener("click", () => {
            if(getComputedStyle(tableau).display != "none"){
                tableau.style.display = "none";
            } else {
                tableau.style.display = "table";
            }
            })



            //SCRIP D'EXECUTION ATTRIBUTION IP AJAX
            // $(document).ready(function() {
            //     $('#execute_script_bouton').submit(function(event) {
            //         event.preventDefault();
            //         $.ajax({
            //             type: 'POST',
            //             url: $(this).attr('action'),
            //             data: $(this).serialize(),
            //             success: function(response) {
            //                 console.log(response);
            //                 // Recharger la page pour afficher les mises à jour
            //                 window.location.reload();
            //             },
            //             error: function(xhr, status, error) {
            //                 console.error(error);
            //                 alert("Une erreur s'est produite lors de l'exécution du script DHCP.");
            //             }
            //         });
            //     });
            // });

            // AFFICHAGE FORMULAIRE DE CHANGEMENT IP
            $(document).ready(function() {
                $('.fa-gears').click(function(event) {
                    event.preventDefault();
                    var $icon = $(this);
                    var $formContainer = $icon.next('.formulaire_ip_conteneur');
                    $formContainer.show();
                    $icon.hide();
                });

                // MASQUAGE FORMULAIRE DE CHANGEMENT IP
                $('.fa-xmark').click(function(event) {
                    event.preventDefault();
                    var $closeIcon = $(this);
                    var $formContainer = $closeIcon.closest('.formulaire_ip_conteneur');
                    var $icon = $formContainer.prev('.fa-gears');
                    $formContainer.hide();
                    $icon.show();
                });

                $(document).ready(function() {
                    // Gestion de la soumission du formulaire en AJAX
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
                                }, 2000);
                            },
                            error: function(xhr, status, error) {
                                // Gérer les erreurs
                                alert("Une erreur s'est produite lors de la modification de l'adresse IP.");
                                console.error(error);
                            }
                        });
                    });
                });
            });
        </script>


    </body>
</html>