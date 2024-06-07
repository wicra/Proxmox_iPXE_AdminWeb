<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</title>
</head>

<div id="conteneur" style="display:none; background-color:transparent; position:absolute; top:100px; left:5%; margin-right:5%; height:50px; width:90%; border:1px solid #000000;">
	<div id="barre" style="display:block; background-color:#CCCCCC; width:0%; height:100%;">
		<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
			&nbsp;
		</div>
	</div>
</div>

<body>
	
<p>Barre de progression PHP : Demonstration</p>
	
<?php

echo "<script>";
	echo "document.getElementById('conteneur').style.display = \"block\";";
echo "</script>";
ob_flush();
flush();
ob_flush();
flush();

$x = 1000;

for( $i=0 ; $i < $x ; $i++ )
{ 
	$indice = round(( ($i+1)*100 ) / $x);
	progression($indice);

	/* Placez ici le code tres tres long a executer … */
	/* Exemple : */
	for( $j = 0 ; $j < 1000 ; $j++ )
		echo ".";
	echo '<br />';
}


echo "<script>";
	echo "document.getElementById('pourcentage').innerHTML='TERMINE !';";
echo "</script>";



function progression($indice)
{	
	echo "<script>";
		echo "document.getElementById('pourcentage').innerHTML='$indice%';";
		echo "document.getElementById('barre').style.width='$indice%';";
	echo "</script>";
	ob_flush();
	flush();
	ob_flush();
	flush();
}
?>
</body>
</html>