<?php
// Chemin vers le fichier shell existant
$script_file_path = '../../conf_proxmox/configenNFS_v3.sh';

// Fonction pour remplacer les valeurs dans le fichier shell avec des guillemets
function replace_script_value($content, $key, $value) {
    return preg_replace("/$key=\"(.*)\"/", "$key=\"$value\"", $content);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lire le contenu du fichier shell
    $script_content = file_get_contents($script_file_path);

    // Collecter et assainir les données du formulaire
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $proxysys = htmlspecialchars($_POST['proxysys']);
    $proxyport = htmlspecialchars($_POST['proxyport']);
    $vmname = htmlspecialchars($_POST['vmname']);
    $vmpool = htmlspecialchars($_POST['vmpool']);
    $vmsize = htmlspecialchars($_POST['vmsize']);
    $vmnetwork = htmlspecialchars($_POST['vmnetwork']);
    $vmnode = htmlspecialchars($_POST['vmnode']);

    // Remplacer les valeurs dans le contenu du fichier shell
    $script_content = replace_script_value($script_content, 'username', $username);
    $script_content = replace_script_value($script_content, 'password', $password);
    $script_content = replace_script_value($script_content, 'proxysys', $proxysys);
    $script_content = replace_script_value($script_content, 'proxyport', $proxyport);
    $script_content = replace_script_value($script_content, 'vmname', $vmname);
    $script_content = replace_script_value($script_content, 'vmpool', $vmpool);
    $script_content = replace_script_value($script_content, 'vmsize', $vmsize);
    $script_content = replace_script_value($script_content, 'vmnetwork', $vmnetwork);
    $script_content = replace_script_value($script_content, 'vmnode', $vmnode);

    // Sauvegarder les modifications dans le fichier shell
    file_put_contents($script_file_path, $script_content);

    echo "Le script de configuration a été mis à jour avec succès.";
} else {
    // Lire les valeurs actuelles du fichier shell pour pré-remplir le formulaire
    $script_content = file_get_contents($script_file_path);

    // Extraire les valeurs actuelles du fichier shell pour pré-remplir le formulaire
    preg_match('/username=\"(.*)\"/', $script_content, $matches);
    $username = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/password=\"(.*)\"/', $script_content, $matches);
    $password = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/proxysys=\"(.*)\"/', $script_content, $matches);
    $proxysys = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/proxyport=\"(.*)\"/', $script_content, $matches);
    $proxyport = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/vmname=\"(.*)\"/', $script_content, $matches);
    $vmname = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/vmpool=\"(.*)\"/', $script_content, $matches);
    $vmpool = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/vmsize=\"(.*)\"/', $script_content, $matches);
    $vmsize = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/vmnetwork=\"(.*)\"/', $script_content, $matches);
    $vmnetwork = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/vmnode=\"(.*)\"/', $script_content, $matches);
    $vmnode = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    // Afficher le formulaire avec des styles intégrés
    echo <<<HTML

<form method="post"  class="container-formulaire">
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
    </div>

    <div class="section-formulaire">
        <div class="row-formulaire">
            <label>Taille de la VM :</label>
            <input class="input_form_configen" type="text" name="vmsize" value="$vmsize" required>
        </div>
        <div class="row-formulaire">
            <label>Réseau VM :</label>
            <input class="input_form_configen" type="text" name="vmnetwork" value="$vmnetwork" required>
        </div>
        <div class="row-formulaire">
            <label>Nœud VM :</label>
            <input class="input_form_configen" type="text" name="vmnode" value="$vmnode" required>
        </div>
    </div>

    <input class="button_form_configen" type="submit" value="Mettre à jour le script Shell">
</form>
HTML;}
?>
