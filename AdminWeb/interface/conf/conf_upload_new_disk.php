<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'upload de fichier RAW</title>
</head>
<body>
    <h2>Upload de fichier RAW</h2>
    <form action="conf_upload_new_disk.php" method="post" enctype="multipart/form-data">
        <label for="fileUpload">Sélectionnez un fichier .raw à télécharger :</label><br>
        <input type="file" name="fileUpload" id="fileUpload" accept=".raw"><br><br>
        <input type="submit" value="Télécharger">
    </form>
</body>
</html>


<?php
// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileUpload"])) {
    $uploadDir = "images/"; // Dossier où les fichiers seront téléchargés (doit être accessible en écriture)

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
