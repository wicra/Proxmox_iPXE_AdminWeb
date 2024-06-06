
<?php
$servername = "sql.freedb.tech"; 
$port = '3306' ;
$dbname = "freedb_outils";  
$username = "freedb_wicra"; 
$password = 'UA3Xjqt$m?NWBFe';  

/*
$servername = "localhost"; 
$port = '3306' ;
$dbname = "iot_db";  
$username = "root"; 
$password = "";  
*/

$conn = mysqli_connect($servername,$username, $password,$dbname,$port );
if (!$conn) {
	die("Connection failed : " . mysqli_connect_error());
}
?>