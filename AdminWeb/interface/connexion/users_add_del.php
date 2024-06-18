<?php
    /////////////////////////////////////////////////////////
    //            SCRIP AJOUT USERS ET SUP USERS           //
    /////////////////////////////////////////////////////////
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($_POST['action'] === 'register') {
            $login = htmlspecialchars(trim($_POST["login"]));
            $password = htmlspecialchars(trim($_POST["password"]));
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $file = fopen('users.env.php', 'a');
            if ($file) {
                fwrite($file, "$login:$password_hash\n");
                fclose($file);
                echo "<p>Utilisateur ajouté avec succès.</p>";
            } else {
                echo "<p>Erreur: Impossible d'ajouter l'utilisateur.</p>";
            }
        } elseif ($_POST['action'] === 'delete') {
            $login_delete = htmlspecialchars(trim($_POST["login_delete"]));
            $users = [];
            $file = fopen('users.env.php', 'r+');
            if ($file) {
                while (($line = fgets($file)) !== false) {
                    list($stored_login, $password_hash) = explode(':', trim($line), 2);
                    $users[$stored_login] = $password_hash;
                }
                if (array_key_exists($login_delete, $users)) {
                    ftruncate($file, 0);
                    rewind($file);
                    foreach ($users as $login => $password_hash) {
                        if ($login !== $login_delete) {
                            fwrite($file, "$login:$password_hash\n");
                        }
                    }
                    echo "<p>Utilisateur supprimé avec succès.</p>";
                } else {
                    echo "<p>Utilisateur non trouvé.</p>";
                }
                fclose($file);
            } else {
                echo "<p>Erreur: Impossible de lire le fichier d'utilisateurs.</p>";
            }
        }
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
        <link rel="stylesheet" href="../styles/style.css">

        <!-- AJAX -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- FAVICON -->
        <link id="favicon" rel="icon"  href="../img/user-interface.png">
    </head>

    <body>

        <!-- FORMULAIRE INSCRIPTION ET SUPPRESSION -->
        <div class="formulaire_inscription_suppression">
            <!-- inscription -->
            <div class="formulaire_inscription">
                <h3 class="titre_inscription"><i class="fa-solid fa-user-tie"></i></h3>
                <form action="users_add_del.php" method="post">
                    <div class="inscription_login_password">
                        <input class="formulaire_login" type="text" id="login" name="login" placeholder="Login" required><br><br>
                        <input class="formulaire_password" type="password" id="password" name="password" placeholder="password" required><br><br>
                    </div>
                    
                    <div class="conteneur_bouton_inscription">
                        
                        <i id="icon_suppression" class="fa-solid fa-user-slash"></i>
                        <button type="submit" name="action" value="register">S'INSCRIRE</button>
                        <i id="retour_page_admin_inscription" class="fa-solid fa-house-user"></i>
                    </div>
                </form>                
            </div>

            <!-- suppression -->
            <div class="formulaire_suppression" id="formulaire_suppression">
                <h3 class="titre_suppression"><i class="fa-solid fa-user-slash"></i></h3>
                <form action="users_add_del.php" method="post">
                    <div class="suppression_login_password">
                        <input class="formulaire_login" type="text" id="login_delete" name="login_delete"  placeholder="Login" required><br><br>
                    </div>
                    <div class="conteneur_bouton_inscription">
                        
                        <i id="icon_inscription" class="fa-solid fa-user-tie"></i>
                        <button type="submit" name="action" value="delete">SUPPRIMER</button>
                        <i id="retour_page_admin_suppression" class="fa-solid fa-house-user"></i>
                    </div>
                </form>
            </div>
        </div>

        <script>
            /////////////////////////////////////////////////////////
            //      SCRIPT JS AFFICHAGE ET MASQUAGE FORMULAIRE     //
            /////////////////////////////////////////////////////////
            $(document).ready(function() {
                $('#icon_suppression').click(function(event) {
                    event.preventDefault();
                    $('#formulaire_suppression').css('display', 'flex');
                    $('.formulaire_inscription').css('display', 'none');
                });

                $('#icon_inscription').click(function(event) {
                    event.preventDefault();
                    $('.formulaire_inscription').css('display', 'flex');
                    $('#formulaire_suppression').css('display', 'none');
                });

                $('#retour_page_admin_inscription, #retour_page_admin_suppression').click(function() {
                    window.location.href = '../chargement.php';
                });
            });
        </script>
    </body>
</html>
