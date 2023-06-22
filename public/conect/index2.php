<?php 
session_start();
$nf = $_SESSION["nf"];

$pasta = "/";
echo "<h3 align='center'>Arquivo de Orçamento 2</h3>";
if(is_dir($pasta))
{	
	foreach (glob("*.pdf") as $arquivo) {
		echo "<h4><p style='font-family: verdana; font-size: 12px; text-align: center; color: navy;'><a href='".$arquivo."' target='_blank'>".$arquivo." </h4></a>";		
	}	
}else{
	echo '<br>A pasta não existe.';
}

