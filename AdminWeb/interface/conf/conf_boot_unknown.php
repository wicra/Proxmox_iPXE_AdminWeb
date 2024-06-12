<?php
    /////////////////////////////////////////////////////////
    //    SCRIP CHANGE ETAT SWITCH ET CHANGE LE FICHIER    //
    /////////////////////////////////////////////////////////
    // lien ver le fichier le link centralisé pour le $configFileUnknown
    include("include/link.php");
    $currentContent = file_get_contents($configFileUnknown);

    $commentedContent = '#include "/etc/dhcp/condition_pxe_boot_unknown.conf";';
    $uncommentedContent = 'include "/etc/dhcp/condition_pxe_boot_unknown.conf";';

    $checkboxState = (strpos($currentContent, '#include') === 0) ? '' : 'checked';

    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_boot_state'])) {
        // Vérifier si le checkbox est coché
        if (isset($_POST['boot']) && $_POST['boot'] === 'on') {
            $newContent = $uncommentedContent;
        } else {
            $newContent = $commentedContent ;
        }
        // Écrire le nouveau contenu 
        file_put_contents($configFileUnknown, $newContent);

        // Mettre à jour l'état du checkbox pour le recharger dans la page
        if (isset($_POST['boot']) && $_POST['boot'] === 'on') {
            $checkboxState = 'checked';
        } else {
            $checkboxState = '';
        }
    }
?>

<!-- FORMULAIRE DEMARRAGE MACHINE INCONNUE -->
<form id="configForm" method="post">
    <!-- input caché que l'etat du switch ne se soumet que si seulement ce champ est soumis (evite le bug avec dhcpd_attribution) -->
    <input type="hidden" name="change_boot_state" value="1">

    <h4>démarrer les machines inconnue en : </h4>
    <div class="checkbox-wrapper-unique">
        <input name="boot" id="boot_unique" type="checkbox" class="switch-unique" <?php echo $checkboxState; ?>>
        
        <label for="boot_unique">
            <span class="switch-x-text-unique"></span>
            <span class="switch-x-toggletext-unique">
                <span class="switch-x-unchecked-unique"><span class="switch-x-hiddenlabel-unique">Unchecked: </span>local</span>
                <span class="switch-x-checked-unique"><span class="switch-x-hiddenlabel-unique">Checked: </span>Reseau</span>
            </span>
        </label>
    </div>
</form>

<!-- JS SOMMISSION FORMULAIRE -->
<script>
    // Sélection de l'élément du switch
    const switchElement = document.getElementById('boot_unique');

    // Ajout d'un écouteur d'événements pour le changement d'état du switch
    switchElement.addEventListener('change', function() {
        // Soumettre automatiquement le formulaire lorsque l'état du switch change
        document.getElementById('configForm').submit();
    });
</script>
