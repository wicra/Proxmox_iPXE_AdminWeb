<?php
// Récupérer les données du formulaire
$host_name = $_POST['host_name'];
$ip_address = $_POST['ip_address'];
$choice = $_POST['choice'];

echo $host_name . " " . $ip_address ." " . $choice;
?>