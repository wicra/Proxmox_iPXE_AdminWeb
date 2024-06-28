<?php
// Chemin vers le fichier shell existant
include("include/link.php");

// Fonction pour remplacer les valeurs dans le fichier shell en se basant sur le nom de la variable
function replace_script_value_by_name($content, $key, $value) {
    // Modèle pour rechercher et remplacer la valeur d'une variable
    $pattern = "/(?<=^$key=\")[^\"]*/ms";
    return preg_replace($pattern, $value, $content);
}

// Fonction pour extraire la valeur d'une variable dans le fichier shell
function extract_value_by_name($content, $key) {
    // Modèle pour extraire la valeur d'une variable
    $pattern = "/^$key=\"([^\"]*)\"/ms";
    preg_match($pattern, $content, $matches);
    return isset($matches[1]) ? htmlspecialchars($matches[1]) : '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'shell_config') {
    // Lire le contenu du fichier shell
    $script_content = file_get_contents($auto_config_proxmox);

    // Collecter et assainir les données du formulaire
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $proxysys = htmlspecialchars($_POST['proxysys']);
    $proxyport = htmlspecialchars($_POST['proxyport']);
    $vmname = htmlspecialchars($_POST['vmname']);
    $vmpool = htmlspecialchars($_POST['vmpool']);
    $ipstockage = htmlspecialchars($_POST['ipstockage']);

    // Remplacer les valeurs dans le contenu du fichier shell
    $script_content = replace_script_value_by_name($script_content, 'username', $username);
    $script_content = replace_script_value_by_name($script_content, 'password', $password);
    $script_content = replace_script_value_by_name($script_content, 'proxysys', $proxysys);
    $script_content = replace_script_value_by_name($script_content, 'proxyport', $proxyport);
    $script_content = replace_script_value_by_name($script_content, 'vname', $vmname); // Remplacer vname par le nom de la variable réelle
    $script_content = replace_script_value_by_name($script_content, 'DisqueChargement', $vmpool); // Assumant que vmpool correspond à DisqueChargement
    $script_content = replace_script_value_by_name($script_content, 'ipstockage', $ipstockage); // Ajout pour ipstockage

    // Sauvegarder les modifications dans le fichier shell
    file_put_contents($auto_config_proxmox, $script_content);

    echo "Le script de configuration a été mis à jour avec succès.";
} else {
    // Lire les valeurs actuelles du fichier shell pour pré-remplir le formulaire
    $script_content = file_get_contents($auto_config_proxmox);

    // Extraire les valeurs actuelles du fichier shell pour pré-remplir le formulaire
    $username = extract_value_by_name($script_content, 'username');
    $password = extract_value_by_name($script_content, 'password');
    $proxysys = extract_value_by_name($script_content, 'proxysys');
    $proxyport = extract_value_by_name($script_content, 'proxyport');
    $vmname = extract_value_by_name($script_content, 'vname'); // Remplacer vname par le nom de la variable réelle
    $vmpool = extract_value_by_name($script_content, 'DisqueChargement'); // Assumant que vmpool correspond à DisqueChargement
    $ipstockage = extract_value_by_name($script_content, 'ipstockage'); // Ajout pour ipstockage

    // Afficher le formulaire de configuration du script shell
    echo <<<HTML
<form method="post" class="container-formulaire">
    <input type="hidden" name="form_type" value="shell_config">
    <div class="section-formulaire">
        <div class="row-formulaire">
            <label>Nom d'utilisateur :</label>
            <input class="input_form_configen" type="text" name="username" value="$username" required>
        </div>
        <div class="row-formulaire">
            <label>Mot de passe :</label>
            <input class="input_form_configen" type="password" name="password" value="$password" required>
        </div>
        <div class="row-formulaire">
            <label>Système Proxy :</label>
            <input class="input_form_configen" type="text" name="proxysys" value="$proxysys" required>
        </div>
    </div>

    <div class="section-formulaire">
        <div class="row-formulaire">
            <label>Port Proxy :</label>
            <input class="input_form_configen" type="text" name="proxyport" value="$proxyport" required>
        </div>
        <div class="row-formulaire">
            <label>Nom de la VM :</label>
            <input class="input_form_configen" type="text" name="vmname" value="$vmname" required>
        </div>
        <div class="row-formulaire">
            <label>Piscine de VM :</label>
            <input class="input_form_configen" type="text" name="vmpool" value="$vmpool" required>
        </div>
        <div class="row-formulaire">
            <label>IP de stockage :</label>
            <input class="input_form_configen" type="text" name="ipstockage" value="$ipstockage" required>
        </div>
    </div>

    <input class="button_form_configen" type="submit" value="Mettre à jour le script Shell">
</form>
HTML;
}
?>