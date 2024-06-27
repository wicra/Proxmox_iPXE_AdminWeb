<?php
// Chemin vers le fichier de configuration existant
$config_file_path = 'answer.toml';

// Fonction pour remplacer les valeurs dans le fichier de configuration
function replace_config_value($content, $key, $value) {
    return preg_replace("/^(\s*$key\s*=\s*\").*(\"$)/m", "\$1$value\$2", $content);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lire le contenu du fichier de configuration
    $config_content = file_get_contents($config_file_path);

    // Collecter et assainir les données du formulaire
    $keyboard = htmlspecialchars($_POST['keyboard']);
    $country = htmlspecialchars($_POST['country']);
    $fqdn = htmlspecialchars($_POST['fqdn']);
    $mailto = htmlspecialchars($_POST['mailto']);
    $timezone = htmlspecialchars($_POST['timezone']);
    $root_password = htmlspecialchars($_POST['root_password']);
    $network_source = htmlspecialchars($_POST['network_source']);
    $filesystem = htmlspecialchars($_POST['filesystem']);
    $disk_list = htmlspecialchars($_POST['disk_list']);

    // Remplacer les valeurs dans le contenu du fichier de configuration
    $config_content = replace_config_value($config_content, 'keyboard', $keyboard);
    $config_content = replace_config_value($config_content, 'country', $country);
    $config_content = replace_config_value($config_content, 'fqdn', $fqdn);
    $config_content = replace_config_value($config_content, 'mailto', $mailto);
    $config_content = replace_config_value($config_content, 'timezone', $timezone);
    $config_content = replace_config_value($config_content, 'root_password', $root_password);
    $config_content = replace_config_value($config_content, 'source', $network_source);
    $config_content = replace_config_value($config_content, 'filesystem', $filesystem);
    $config_content = replace_config_value($config_content, 'disk_list', $disk_list);

    // Sauvegarder les modifications dans le fichier de configuration
    file_put_contents($config_file_path, $config_content);

    echo "Le fichier de configuration a été mis à jour avec succès.";
} else {
    // Lire les valeurs actuelles du fichier de configuration pour pré-remplir le formulaire
    $config_content = file_get_contents($config_file_path);

    // Extraire les valeurs actuelles du fichier de configuration pour pré-remplir le formulaire
    preg_match('/keyboard\s*=\s*"([^"]+)"/', $config_content, $matches);
    $keyboard = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/country\s*=\s*"([^"]+)"/', $config_content, $matches);
    $country = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/fqdn\s*=\s*"([^"]+)"/', $config_content, $matches);
    $fqdn = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/mailto\s*=\s*"([^"]+)"/', $config_content, $matches);
    $mailto = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/timezone\s*=\s*"([^"]+)"/', $config_content, $matches);
    $timezone = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/root_password\s*=\s*"([^"]+)"/', $config_content, $matches);
    $root_password = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/source\s*=\s*"([^"]+)"/', $config_content, $matches);
    $network_source = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/filesystem\s*=\s*"([^"]+)"/', $config_content, $matches);
    $filesystem = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    preg_match('/disk_list\s*=\s*"([^"]+)"/', $config_content, $matches);
    $disk_list = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';

    // Afficher le formulaire avec des styles intégrés
    echo <<<HTML

<form method="post"  class="container-formulaire">
    <div class="section-formulaire">
        <div class="row-formulaire">
            <label>Clavier :</label>
            <input class="input_form_answer_toml" type="text" name="keyboard" value="$keyboard" required>
        </div>
        <div class="row-formulaire">
            <label>Pays :</label>
            <input class="input_form_answer_toml" type="text" name="country" value="$country" required>
        </div>
        <div class="row-formulaire">
            <label>FQDN :</label>
            <input class="input_form_answer_toml" type="text" name="fqdn" value="$fqdn" required>
        </div>
    </div>

    <div class="section-formulaire">
        <div class="row-formulaire">
            <label>Email :</label>
            <input class="input_form_answer_toml" type="email" name="mailto" value="$mailto" required>
        </div>
        <div class="row-formulaire">
            <label>Source :</label>
            <input class="input_form_answer_toml" type="text" name="network_source" value="$network_source" required>
        </div>
        <div class="row-formulaire">
            <label>Fuseau horaire :</label>
            <input class="input_form_answer_toml" type="text" name="timezone" value="$timezone" required>
        </div>
    </div>

    <div class="section-formulaire">
        <div class="row-formulaire">
            <label>Système de fichiers :</label>
            <input class="input_form_answer_toml" type="text" name="filesystem" value="$filesystem" required>
        </div>
        <div class="row-formulaire">
            <label>Liste des disques :</label>
            <input class="input_form_answer_toml" type="text" name="disk_list" value="$disk_list" required>
        </div>
        <div class="row-formulaire">
            <label>Mot de passe Root :</label>
            <input class="input_form_answer_toml" type="password" name="root_password" value="$root_password" required>
        </div>
    </div>

    <input class="button_form_answer_toml" type="submit" value="Mettre à jour le fichier TOML">
</form>
HTML;}
?>
