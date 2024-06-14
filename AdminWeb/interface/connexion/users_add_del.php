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
    </head>
    <body>
        <div class="formulaire_inscription_suppression">
            <div class="formulaire_inscription">
                <!-- Formulaire d'inscription -->
                <h3 class="titre_inscription">Inscription</h3>
                <form action="users_add_del.php" method="post">
                    <div class="inscription_login_password">
                        <input class="formulaire_login" type="text" id="login" name="login" placeholder="Login" required><br><br>
                        <input class="formulaire_password" type="password" id="password" name="password" placeholder="password" required><br><br>
                    </div>
                    
                    <div class="conteneur_bouton_inscription">
                        
                        <i id="icon_suppression" class="fa-solid fa-user-xmark"></i>
                        <button type="submit" name="action" value="register">S' inscrire</button>
                        <i id="retour_page_admin_inscription" class="fa-solid fa-house-user"></i>
                    </div>
                </form>                
            </div>
            <div class="formulaire_suppression" id="formulaire_suppression">
                <!-- Formulaire de suppression -->
                <h3 class="titre_suppression">Suppression</h3>
                <form action="users_add_del.php" method="post">
                    <div class="suppression_login_password">
                        <input class="formulaire_login" type="text" id="login_delete" name="login_delete"  placeholder="Login" required><br><br>
                        <input class="formulaire_password" type="password" id="password_delete" name="password_delete" placeholder="password" required><br><br>
                    </div>
                    <div class="conteneur_bouton_inscription">
                        
                        <i id="icon_inscription" class="fa-solid fa-user-plus"></i>
                        <button type="submit" name="action" value="delete">Supprimer</button>
                        <i id="retour_page_admin_suppression" class="fa-solid fa-house-user"></i>
                    </div>
                </form>
            </div>
        </div>

        <style>
            .formulaire_inscription_suppression {
                padding: 12vh 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            .titre_suppression, .titre_inscription {
                color: var(--Couleur5);
                font-size: 120px;
                margin: 0;
                padding: 0;
            }

            #formulaire_suppression {
                display: none;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
            }

            .formulaire_inscription {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
            }

            .suppression_login_password, .inscription_login_password {
                display: flex;
                justify-content: center;
                align-items: center;
                
                padding: 5vh 0;
            }

            .conteneur_bouton_inscription {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 2vw;
            }

            #icon_inscription, #icon_suppression, .conteneur_bouton_inscription button, #retour_page_admin_inscription, #retour_page_admin_suppression {
                background-color: var(--Couleur5);
                border: none;
                cursor: pointer;
                padding: 0.5vh 0.5vw;
                font-size: 20px;
                border-radius: 0.5rem;
                color: var(--CouleurFont);
            }

            .suppression_login_password input,.inscription_login_password input{
                margin: 0.5vw;
            }
        </style>

        <script>
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
                    window.location.href = '../admin_page.php';
                });
            });
        </script>

        <?php



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
                $password_delete = htmlspecialchars(trim($_POST["password_delete"]));
                $users = [];
                $file = fopen('users.env.php', 'r+');
                if ($file) {
                    while (($line = fgets($file)) !== false) {
                        list($stored_login, $password_hash) = explode(':', trim($line), 2);
                        $users[$stored_login] = $password_hash;
                    }
                    if (array_key_exists($login_delete, $users) && password_verify($password_delete, $users[$login_delete])) {
                        ftruncate($file, 0);
                        rewind($file);
                        foreach ($users as $login => $password_hash) {
                            if ($login !== $login_delete) {
                                fwrite($file, "$login:$password_hash\n");
                            }
                        }
                        echo "<p>Utilisateur supprimé avec succès.</p>";
                    } else {
                        echo "<p>Identifiants incorrects pour la suppression de l'utilisateur.</p>";
                    }
                    fclose($file);
                } else {
                    echo "<p>Erreur: Impossible de lire le fichier d'utilisateurs.</p>";
                }
            }
        }
        ?>
    </body>
</html>
