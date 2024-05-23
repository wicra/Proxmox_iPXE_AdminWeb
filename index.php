<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Interface Conf</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- ICON -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <!-- CSS -->
        <link rel="stylesheet" href="./site/interface/styles/style.css">

        <!-- FONT -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DotGothic16&display=swap" rel="stylesheet">
    </head>

    <body>

        <div class="bienvenue">
            <h1 class="bienvenue_titre">Bienvenue sur l'interface Admin de conf</h1>

            <div class="formulaire_conteneur">
                <form  class="formulaire" action="">
                    <div class="login_password">
                        <input  class="formulaire_login" type="text" id="username" name="username" placeholder="Login" />
                        <input  class="formulaire_password" type="password" id="pass" name="password" placeholder="Password" minlength="8" required />
                    </div>

                    <button>
                        <span>Connection</span>
                    </button>
                </form>                 
            </div>
               
  

            <a id="afficherFormulaire" href="site/interface/connection/formulaire_connection.php">me connecter</a>
        </div>
        
        <script>
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