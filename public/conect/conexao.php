
<?php
include("config.php");
$dados = "host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_DBNAME . " user=" . DB_USER . " password=" . DB_PASS;
$link = pg_connect($dados);

if (!$link) {
	die('Erro ao conectar com o banco PGSQL');
}//else{
//	echo "Conectou";exit;
//}

?> 
