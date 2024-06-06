<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Switch persistant avec PHP et fichier</title>
    <style>
        .checkbox-wrapper-35 {
            display: inline-block;
        }
        .switch {
            display: none;
        }
        .switch + label {
            cursor: pointer;
            display: inline-block;
            position: relative;
        }
        .switch + label .switch-x-toggletext {
            display: inline-block;
        }
        .switch:checked + label .switch-x-checked {
            display: inline;
        }
        .switch:not(:checked) + label .switch-x-unchecked {
            display: inline;
        }
        .switch:checked + label .switch-x-unchecked,
        .switch:not(:checked) + label .switch-x-checked {
            display: none;
        }
    </style>
    <script>
        function submitForm() {
            document.getElementById('switchForm').submit();
        }
    </script>
</head>
<body>
    <?php
        $file_path = "/AdminWeb/interface/include/add_boot_ipxe_unknown.conf";
        $new = "#include";
        $new2 = "include";
        $checkboxState = false;

        if (file_exists($file_path)) {
            $file_content = file_get_contents($file_path);
            $checkboxState = strpos($file_content, "#include") !== false;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['switch'])) {
                if ($_POST['switch'] === 'on') {
                    $file_content = str_replace($new2, $new, $file_content);
                    $checkboxState = true;
                } else {
                    $file_content = str_replace($new, $new2, $file_content);
                    $checkboxState = false;
                }
                file_put_contents($file_path, $file_content);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    ?>

    <form id="switchForm" action="" method="post">
        <div class="checkbox-wrapper-35">
            <input name="switch" id="switch" type="checkbox" class="switch" value="on" <?php if ($checkboxState) echo 'checked'; ?> onchange="submitForm()">
            <label for="switch">
                <span class="switch-x-text"></span>
                <span class="switch-x-toggletext">
                    <span class="switch-x-unchecked"><span class="switch-x-hiddenlabel">Unchecked: </span>Reseau</span>
                    <span class="switch-x-checked"><span class="switch-x-hiddenlabel">Checked: </span>local</span>
                </span>
            </label>
        </div>
    </form>
</body>
</html>