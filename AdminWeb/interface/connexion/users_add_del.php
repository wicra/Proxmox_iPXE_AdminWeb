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
                        <input class="formulaire_login" type="text" id="login" name="login" required><br><br>
                        <input class="formulaire_password" type="password" id="password" name="password" required><br><br>
                    </div>
                    
                    <div class="conteneur_bouton_inscription">
                        <button type="submit" name="action" value="register">S'inscrire</button>
                        
                        <i id="icon_suppression" class=" fa-solid fa-user-xmark "></i>
                        
                        <i id="retour_page_admin" class="fa-solid fa-house-user "></i>
                    </div>
                </form>                
            </div>
            <div class="formulaire_suppression" id="formulaire_suppression">
                <!-- Formulaire de suppression -->
                <h3 class="titre_suppression">Suppression</h3>
                <form action="users_add_del.php" method="post">
                    <div class="suppression_login_password">
                        <input class="formulaire_login" type="text" id="login_delete" name="login_delete" required><br><br>
                        <input class="formulaire_password" type="password" id="password_delete" name="password_delete" required><br><br>
                    </div>
                    <div class="conteneur_bouton_inscription">
                        
                        <button  type="submit" name="action" value="delete">Supprimer</button>
                        
                        <i id="icon_inscription" class="  fa-solid fa-user-plus"></i>
                        <i  id="retour_page_admin" class="fa-solid fa-house-user " onclick= "<?php retour();?>"></i>
                        <?php
                            function retour(){
                                header("location: ../admin_page.php");
                            }
                        ?>
                    </div>
                </form>

            </div>
        </div>
        <style>

            .formulaire_inscription_suppression{
                padding: 12vh 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);
            }

            .titre_suppression,.titre_inscription{
                color: var(--Couleur5);
                font-size: 120px;
                margin: 0;
                padding: 0;
            }


            #formulaire_suppression{
                display: none;
                
            }
            .formulaire_suppression{
                
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
            }
            
            .formulaire_inscription{
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                

            }
            .suppression_login_password,.inscription_login_password{
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1vw;
                padding: 5vh 0;
            }


            .conteneur_bouton_inscription{
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 2vw;
            }
            #icon_inscription,#icon_suppression,.conteneur_bouton_inscription button,#retour_page_admin{
                background-color: var(--Couleur5);
                border: none;
                cursor: pointer;
                padding: 0.5vh 0.5vw;
                font-size: 20px;
                border-radius: 0.5rem;
                color: var(--CouleurFont);
            }
        </style>

        <script>
                /////////////////////////////////////////////////////////
                //     SCRIP JS AFFICHANGE FORMULAIRE DE CONNEXION     //
                /////////////////////////////////////////////////////////        
            $(document).ready(function() {
                $('#icon_suppression').click(function(event) {
                    event.preventDefault();
                    $('.formulaire_suppression').show();
                    $('.formulaire_inscription').hide();
                    
                });
            });

            $(document).ready(function() {
                $('#icon_inscription').click(function(event) {
                    event.preventDefault();
                    $('.formulaire_inscription').show();
                    $('.formulaire_suppression').hide();
                });
            });
            


        </script>

        <?php
        // Gestion de l'inscription et de la suppression d'utilisateur
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Si l'action est 'register', effectuer l'inscription
            if ($_POST['action'] === 'register') {
                $login = htmlspecialchars(trim($_POST["login"]));
                $password = htmlspecialchars(trim($_POST["password"]));

                // Hasher le mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Écrire dans le fichier
                $file = fopen('users.env.txt', 'a'); // Ouvre le fichier en mode 'append'
                if ($file) {
                    fwrite($file, "$login:$password_hash\n"); // Écrit la nouvelle ligne
                    fclose($file);
                    echo "<p>Utilisateur ajouté avec succès.</p>";
                } else {
                    echo "<p>Erreur: Impossible d'ajouter l'utilisateur.</p>";
                }
            }

            // Si l'action est 'delete', effectuer la suppression
            elseif ($_POST['action'] === 'delete') {
                $login_delete = htmlspecialchars(trim($_POST["login_delete"]));
                $password_delete = htmlspecialchars(trim($_POST["password_delete"]));

                // Lire le fichier et préparer le contenu mis à jour
                $users = [];
                $file = fopen('users.env.txt', 'r+'); // Ouvre le fichier en mode 'read/write'
                if ($file) {
                    while (($line = fgets($file)) !== false) {
                        // Explode avec limite 2 pour ne séparer qu'au premier ':'
                        list($stored_login, $password_hash) = explode(':', trim($line), 2);
                        $users[$stored_login] = $password_hash;
                    }

                    // Vérifier si l'utilisateur existe et supprimer s'il correspond
                    if (array_key_exists($login_delete, $users) && password_verify($password_delete, $users[$login_delete])) {
                        // Rembobiner le fichier pour réécrire sans l'utilisateur supprimé
                        ftruncate($file, 0); // Vide le fichier
                        rewind($file); // Remet le pointeur au début du fichier

                        // Réécrire les utilisateurs sauf celui à supprimer
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
