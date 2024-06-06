<?php
$configFile = 'include/add_boot_ipxe_unknown.conf';
$currentContent = file_get_contents($configFile);

$commentedContent = '#include "/etc/dhcp/condition_pxe_boot_unknown.conf";';
$uncommentedContent = 'include "/etc/dhcp/condition_pxe_boot_unknown.conf";';

$checkboxState = (strpos($currentContent, '#include') === 0) ? '' : 'checked';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si le checkbox est coché
    if (isset($_POST['boot']) && $_POST['boot'] === 'on') {
        // Mettre à jour le contenu du fichier pour le cas où le checkbox est coché
        $newContent = $uncommentedContent;
    } else {
        // Mettre à jour le contenu du fichier pour le cas où le checkbox n'est pas coché
        $newContent = $commentedContent ;
    }

    // Écrire le nouveau contenu dans le fichier
    file_put_contents($configFile, $newContent);

    // Mettre à jour l'état du checkbox pour le recharger dans la page
    if (isset($_POST['boot']) && $_POST['boot'] === 'on') {
        $checkboxState = 'checked';
    } else {
        $checkboxState = '';
    }
}
?>


    <form id="configForm" method="post">
        <h4>démarrer les machines inconnue en : </h4>
        <div class="checkbox-wrapper-unique">
            <!-- Input du type checkbox avec le nom, l'identifiant et la classe appropriés -->
            <input name="boot" id="boot_unique" type="checkbox" class="switch-unique" <?php echo $checkboxState; ?>>
            <!-- Label pour le switch -->
            <label for="boot_unique">
                <!-- Texte caché pour le switch -->
                <span class="switch-x-text-unique"></span>
                <!-- Texte pour les états du switch -->
                <span class="switch-x-toggletext-unique">
                    <span class="switch-x-unchecked-unique"><span class="switch-x-hiddenlabel-unique">Unchecked: </span>local</span>
                    <span class="switch-x-checked-unique"><span class="switch-x-hiddenlabel-unique">Checked: </span>Reseau</span>
                </span>
            </label>
        </div>
    </form>


    <script>
        // Sélection de l'élément du switch
        const switchElement = document.getElementById('boot_unique');

        // Ajout d'un écouteur d'événements pour le changement d'état du switch
        switchElement.addEventListener('change', function() {
            // Soumettre automatiquement le formulaire lorsque l'état du switch change
            document.getElementById('configForm').submit();
        });
    </script>

