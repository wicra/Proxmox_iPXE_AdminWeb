<div class="conf_boot_ipxe_unknown">
        
        <h4>Démarer les machines unknown :</h2>
        <form  method="post">
            <button type="submit" name="bouton_boot_unknown_ajout">ajout</button>
            <button type="submit" name="bouton_boot_unknown_del">del</button>
            <!-- <input type="hidden" name="host_name" value="unknown">
            <input type="hidden" name="mac_address" value="unknown">
            <input type="hidden" name="ip_address" value="unknown"> -->

            <?php
            
                $file_path = "include/add_boot_ipxe_unknown.conf";
                $commente = '#include "/etc/dhcp/condition_pxe_boot_unknown.conf";';
                $decommente = 'include "/etc/dhcp/condition_pxe_boot_unknown.conf";';
                $file_content = file_get_contents($file_path);
                
                if($file_content == $decommente){
                    $checkboxState = "checked";
                }else{
                    $checkboxState = "";
                }
                


                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if (isset($_POST['boot'])) {
                        $replace = str_replace($decommente, $commente, $file_content);
                        file_put_contents($file_path, $replace);
                        $checkboxState = "checked";
                        echo "<pre>" ;
                        
                        print_r($_POST);
                        echo "</pre>";
                    }
                    elseif (isset($_POST['bouton_boot_unknown_del'])) {
                        $replace = str_replace($commente, $decommente, $file_content);
                        file_put_contents($file_path, $replace);
                        $checkboxState = "";
                    }
                    
                }

            ?>

            <div class="checkbox-wrapper-35">
                <input name="boot" id="boot_unknown" type="checkbox" class="switch" value="" <?php  echo $checkboxState  ; ?>>
                <label  for="boot_unknown">
                    <span class="switch-x-text"></span>
                    <span class="switch-x-toggletext">
                        <span class="switch-x-unchecked"><span class="switch-x-hiddenlabel">Unchecked: </span>local</span>
                        <span class="switch-x-checked"><span class="switch-x-hiddenlabel">Checked: </span>Reseau</span>
                    </span>
                </label>
            </div>
        </form>


    </div>