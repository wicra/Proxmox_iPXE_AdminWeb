<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disable Checkbox for VM</title>
    <style>
        .disabled-checkbox {
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <form action="path_to_your_php_script.php" method="POST">
        <label for="host_name">Host Name:</label>
        <input type="text" id="host_name" name="host_name" required>
        <br>
        <label for="mac_address">MAC Address:</label>
        <input type="text" id="mac_address" name="mac_address" required>
        <br>
        <label for="ip_address">IP Address:</label>
        <input type="text" id="ip_address" name="ip_address" required>
        <br>
        <label for="is_vm">Is VM:</label>
        <input type="checkbox" id="is_vm" name="is_vm" onchange="checkVM()">
        <br>
        <label for="option">Option:</label>
        <input type="checkbox" id="option" name="option">
        <br>
        <button type="submit">Submit</button>
    </form>

    <script>
        function checkVM() {
            var isVM = document.getElementById('is_vm').checked;
            var optionCheckbox = document.getElementById('option');
            if (isVM) {
                optionCheckbox.disabled = true;
                optionCheckbox.classList.add('disabled-checkbox');
            } else {
                optionCheckbox.disabled = false;
                optionCheckbox.classList.remove('disabled-checkbox');
            }
        }

        // Initial check on page load in case the form retains state
        window.onload = function() {
            checkVM();
        };
    </script>
</body>
</html>