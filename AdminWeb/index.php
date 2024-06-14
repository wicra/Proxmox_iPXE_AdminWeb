<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Connection</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- ICON -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <!-- CSS -->
        <link rel="stylesheet" href="interface/styles/style.css">

        <!-- FAVICON -->
        <link id="favicon" rel="icon"  href="interface/img/connexion.png">
    </head>

    <body>
        <?php
        //echo password_hash("admin", PASSWORD_DEFAULT);
        session_start();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $login = htmlspecialchars(trim($_POST["login"]));
            $password = htmlspecialchars(trim($_POST["password"]));

            $file = fopen('interface/connexion/users.env.php', 'r');
            $is_valid_user = false;

            if ($file) {
                while (($line = fgets($file)) !== false) {
                    list($stored_login, $stored_password_hash) = explode(':', trim($line));

                    if ($login === $stored_login && password_verify($password, $stored_password_hash)) {
                        $is_valid_user = true;
                        break;
                    }
                }
                fclose($file);
            }

            if ($is_valid_user) {
                $_SESSION['login'] = $login;
                header("location: interface/chargement.php");
                exit();
            } else {
                header("location: index.php");
                exit();
            }
        }
        ?>

        <!--- FORMULAIRE DE CONNEXION -->
        <div class="bienvenue">
            <i class="fa-solid fa-eye-low-vision"></i>

            <div class="formulaire_conteneur">
                <form class="formulaire" action="index.php" method="post">
                    <div class="login_password">
                        <input class="formulaire_login" type="text" id="login" name="login" placeholder="Login" />
                        <input class="formulaire_password" type="password" id="password" name="password" placeholder="Password" required />
                    </div>

                    <button class="button" type="submit" style="background-color: var(--Couleur5);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25
                            0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"></path>
                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0
                            0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"></path>
                        </svg>
                        CONNEXION
                    </button>
                </form>
            </div>

            <a id="afficherFormulaire" href="interface/include/formulaire_connection.php">me connecter</a>
        </div>


            <script>
                /////////////////////////////////////////////////////////
                //     SCRIP JS AFFICHANGE FORMULAIRE DE CONNEXION     //
                /////////////////////////////////////////////////////////        
            $(document).ready(function() {
                $('#afficherFormulaire').click(function(event) {
                    event.preventDefault();
                    $('.formulaire_conteneur').show();
                    $(this).hide();
                });
            });
        </script>
    </body>
</html>
