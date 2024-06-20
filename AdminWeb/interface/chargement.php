<!DOCTYPE html>
<html lang="en">
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
        <link id="favicon" rel="icon"  href="img/chargement.png">
    </head>

    <body>
        <div class="conteneur_loadingspinner">
            <div class="loadingspinner">
                <div id="square1"></div>
                <div id="square2"></div>
                <div id="square3"></div>
                <div id="square4"></div>
                <div id="square5"></div>
            </div>            
        </div>


        <script> // Attendre 0 secondes avant de rediriger 
            setTimeout(function() {
                window.location.href = 'admin_page.php';
            }),1000; 
        </script>


        </style>
    </body>
</html>

