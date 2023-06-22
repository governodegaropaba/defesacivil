
 <?php
$link = pg_connect("host=dbserver.prefa.br port=5432 dbname=defesacivil user=informatica password=gsul@10");
if (!$link) {
	die('Erro ao conectar com o banco PGSQL do iEducar!');
}//else{
//	echo "Conectado com sucesso";
//}
?> 