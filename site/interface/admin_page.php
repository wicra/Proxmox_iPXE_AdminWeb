


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/style.css">
        <!-- ICON -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <?php
    // Chemin vers le fichier de configuration DHCP
    //$file_path = '/etc/dhcp/dhcpd.conf';
    $file_path = '../../dhcp/dhcpd.conf';
    // Lire le fichier de configuration
    $config = file_get_contents($file_path);

    if ($config === false) {
        die("Impossible de lire le fichier de configuration.");
    }

    // Expression régulière pour extraire les informations des hôtes
    $pattern = '/host\s+(\w+)\s*\{[^}]*hardware\s+ethernet\s+([0-9a-f:]+);[^}]*fixed-address\s+([0-9.]+);[^}]*\}/mi';

    // Trouver tous les hôtes correspondants
    if (preg_match_all($pattern, $config, $matches, PREG_SET_ORDER)) {
        echo "<div class=\"tableau_conteneur\">

            <h1 class=\"titre_tableau\">Liste des hôtes DHCP</h1>
            <table border='1'>
                <tr class=\"tableau\">
                    <th class=\"col_name\" >Nom de l'hôte</th>
                    <th class=\"col_mac\">@ MAC</th>
                    <th class=\"col_ip_fixe\">@ IP fixe</th>
                    <th class=\"col_modif_ip\">@ IP conf</th>
                </tr>";
        
                // Parcourir chaque hôte trouvé
                foreach ($matches as $match) {
                    $host_name = $match[1];
                    $hardware_ethernet = $match[2];
                    $fixed_address = $match[3];
                    
                    echo "<tr class=\"tableau\" >
                            <td class=\"col_name\"><i class=\"fa-solid fa-desktop\"></i>{$host_name}</td>
                            <td class=\"col_mac\">{$hardware_ethernet}</td>
                            <td class=\"col_ip_fixe\">{$fixed_address}</td>
                            <td class=\"col_ip_fixe\">
                                <i class=\"fa-solid fa-gears\"></i>
                                <div class=\"formulaire_ip_conteneur\">
                                    <div class=\"ip_check\">
                                        <input  class=\"formulaire_ip\" type=\"text\" id=\"formulaire_ip\" name=\"formulaire_ip\" placeholder=\"Nouvelle IP\" />
                                        <input type=\"checkbox\" class=\"ui-checkbox\">
                                        <i class=\"fa-solid fa-xmark\"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>";
                }
        
        echo "</table>
        </div>
        ";
    } else {
        echo "Aucun hôte trouvé dans le fichier de configuration.";
    }
    ?>



<script>
    $(document).ready(function() {
        $('.fa-gears').click(function(event) {
            event.preventDefault();
            var $icon = $(this);
            var $formContainer = $icon.next('.formulaire_ip_conteneur');
            $formContainer.show();
            $icon.hide();
        });
    });


</script>



<style>
/*TABLEAU D'HOTE*/
.tableau_conteneur{
    display: flex;
    flex-direction: column;
    justify-content:center;
    align-items:center;
}

th{
    text-transform:uppercase;

}

.titre_tableau{
        
    font-size: 80px;
    text-transform: uppercase;
}

.tableau{
    border: solid var(--CouleurFont);
}

.col_name{
    justify-content: center;
    padding: 1vh 2vw;
    font-size: 35px;
    align-items: center;
    display: flex;
}

.col_name i{
    padding: 1vh 2vw;
    text-align: center;
    font-size: 35px;
}

.col_mac{
    padding: 1vh 2vw;
    text-align: center;
    font-size: 35px;
}

.col_ip_fixe{
    padding: 1vh 2vw;
    text-align: center;
    font-size: 35px;
}
.col_modif_ip{
    padding: 1vh 2vw;
    text-align: center;
    font-size: 35px;
}


.ip_check{
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2vw;
}

.formulaire_ip_conteneur{
    display:none;
    
}

/* CHECKBOX*/

.ui-checkbox {
  --primary-color: #1677ff;
  --secondary-color: #fff;
  --primary-hover-color: #4096ff;
  /* checkbox */
  --checkbox-diameter: 20px;
  --checkbox-border-radius: 5px;
  --checkbox-border-color: #d9d9d9;
  --checkbox-border-width: 1px;
  --checkbox-border-style: solid;
  /* checkmark */
  --checkmark-size: 1.2;
}

.ui-checkbox, 
.ui-checkbox *, 
.ui-checkbox *::before, 
.ui-checkbox *::after {
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
}

.ui-checkbox {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: var(--checkbox-diameter);
  height: var(--checkbox-diameter);
  border-radius: var(--checkbox-border-radius);
  background: var(--secondary-color);
  border: var(--checkbox-border-width) var(--checkbox-border-style) var(--checkbox-border-color);
  -webkit-transition: all 0.3s;
  -o-transition: all 0.3s;
  transition: all 0.3s;
  cursor: pointer;
  position: relative;
}

.ui-checkbox::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  -webkit-box-shadow: 0 0 0 calc(var(--checkbox-diameter) / 2.5) var(--primary-color);
  box-shadow: 0 0 0 calc(var(--checkbox-diameter) / 2.5) var(--primary-color);
  border-radius: inherit;
  opacity: 0;
  -webkit-transition: all 0.5s cubic-bezier(0.12, 0.4, 0.29, 1.46);
  -o-transition: all 0.5s cubic-bezier(0.12, 0.4, 0.29, 1.46);
  transition: all 0.5s cubic-bezier(0.12, 0.4, 0.29, 1.46);
}

.ui-checkbox::before {
  top: 40%;
  left: 50%;
  content: "";
  position: absolute;
  width: 4px;
  height: 7px;
  border-right: 2px solid var(--secondary-color);
  border-bottom: 2px solid var(--secondary-color);
  -webkit-transform: translate(-50%, -50%) rotate(45deg) scale(0);
  -ms-transform: translate(-50%, -50%) rotate(45deg) scale(0);
  transform: translate(-50%, -50%) rotate(45deg) scale(0);
  opacity: 0;
  -webkit-transition: all 0.1s cubic-bezier(0.71, -0.46, 0.88, 0.6),opacity 0.1s;
  -o-transition: all 0.1s cubic-bezier(0.71, -0.46, 0.88, 0.6),opacity 0.1s;
  transition: all 0.1s cubic-bezier(0.71, -0.46, 0.88, 0.6),opacity 0.1s;
}

/* actions */

.ui-checkbox:hover {
  border-color: var(--primary-color);
}

.ui-checkbox:checked {
  background: var(--primary-color);
  border-color: transparent;
}

.ui-checkbox:checked::before {
  opacity: 1;
  -webkit-transform: translate(-50%, -50%) rotate(45deg) scale(var(--checkmark-size));
  -ms-transform: translate(-50%, -50%) rotate(45deg) scale(var(--checkmark-size));
  transform: translate(-50%, -50%) rotate(45deg) scale(var(--checkmark-size));
  -webkit-transition: all 0.2s cubic-bezier(0.12, 0.4, 0.29, 1.46) 0.1s;
  -o-transition: all 0.2s cubic-bezier(0.12, 0.4, 0.29, 1.46) 0.1s;
  transition: all 0.2s cubic-bezier(0.12, 0.4, 0.29, 1.46) 0.1s;
}

.ui-checkbox:active:not(:checked)::after {
  -webkit-transition: none;
  -o-transition: none;
  -webkit-box-shadow: none;
  box-shadow: none;
  transition: none;
  opacity: 1;
}
</style>
</body>
</html>