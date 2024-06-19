
<?php
    /////////////////////////////////////////////////////////
    //                SCRIPT UPLOAD NEW DISK               //
    /////////////////////////////////////////////////////////
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileUpload"])) {
        $uploadDir = "../upload_new_disk_tmp/"; // Dossier où les fichiers seront téléchargés

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
                    shell_exec('../../shell/upload_new_disk.sh');
                    notif( "Le fichier $fileName a été téléchargé avec succès dans $uploadDir.");
                    
                } else {
                    echo "Erreur lors du téléchargement du fichier.";
                }
            } else {
                echo "Erreur : Seuls les fichiers .raw sont autorisés.";
            }
        } 
        else {
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
    }
?>

<!--- FORMULAIRE UPLOAD -->
<form class="formulaire_upload"action="" method="post" enctype="multipart/form-data">
    <div id="drop_zone">
        <i class="fa-solid fa-file-arrow-up"></i>
        <h3>déposez votre fichier .raw ici Ou </h3>
        
        <div class="upload_input_contenaire">
            <input type="file" name="fileUpload" id="fileUpload" accept=".raw"><br><br>
            <input type="submit" value="Télécharger" id="upload_submit">            
        </div>
    </div><br><br>
</form>

<script>
        /////////////////////////////////////////////////////////
        //                  SUBMIT EN AJAX                    //
        /////////////////////////////////////////////////////////
        $('#upload_submit').click(function() {
            // Déclencher le clic sur le bouton de soumission du formulaire
            $(this).closest('.formulaire_upload').submit();
        });

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