<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'upload de fichier RAW</title>
    <style>
        #drop_zone {
            border: 2px dashed #bbb;
            border-radius: 5px;
            padding: 25px;
            text-align: center;
            font: 20pt bold 'Vollkorn';
            color: #bbb;
        }
    </style>
</head>
<body>
    <h2>Upload de fichier RAW</h2>
    <form action="conf_upload_new_disk.php" method="post" enctype="multipart/form-data">
        <label for="fileUpload">Sélectionnez un fichier .raw à télécharger :</label><br>
        <input type="file" name="fileUpload" id="fileUpload" accept=".raw"><br><br>
        <div id="drop_zone">Ou déposez votre fichier .raw ici</div><br><br>
        <input type="submit" value="Télécharger">
    </form>

    <script>
        /////////////////////////////////////////////////////////
        //                  GLISSER DEPOSER                    //
        /////////////////////////////////////////////////////////
        const dropZone = document.getElementById('drop_zone');
        const fileInput = document.getElementById('fileUpload');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'green';
        });

        dropZone.addEventListener('dragleave', (e) => {
            dropZone.style.borderColor = '#bbb';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#bbb';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        });
    </script>
</body>
</html>


<?php
// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileUpload"])) {
    $uploadDir = "../../upload_new_disk_tmp/"; // Dossier où les fichiers seront téléchargés

    // Récupérer les informations du fichier
    $fileName = $_FILES["fileUpload"]["name"];
    $fileTmp = $_FILES["fileUpload"]["tmp_name"];
    $fileSize = $_FILES["fileUpload"]["size"];
    $fileError = $_FILES["fileUpload"]["error"];

    // Vérifier s'il y a une erreur avec le fichier
    if ($fileError == UPLOAD_ERR_OK) {
        // Vérifier si le fichier est bien un fichier .raw
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($fileExtension === "raw") {
            // Déplacer le fichier téléchargé vers le dossier de destination
            $destination = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                //deplacer vers le repertoir /images/
                chell_exec('../../shell/upload_new_disk.sh');

                echo "Le fichier $fileName a été téléchargé avec succès dans $uploadDir.";
            } else {
                echo "Erreur lors du téléchargement du fichier.";
            }
        } else {
            echo "Erreur : Seuls les fichiers .raw sont autorisés.";
        }
    } else {
        echo "Erreur lors du téléchargement du fichier : ";
        switch ($fileError) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo "Le fichier est trop gros.";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "Aucun fichier n'a été téléchargé.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "Erreur de dossier temporaire.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Échec de l'écriture du fichier sur le disque.";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "Téléchargement arrêté par l'extension.";
                break;
            default:
                echo "Erreur inconnue.";
                break;
        }
    }
} else {
    echo "Erreur : veuillez soumettre un fichier.";
}
?>
