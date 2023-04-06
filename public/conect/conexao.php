
 <?php
$link = pg_connect("host=servidor.prefa.br port=5432 dbname=seudatabase user=informatica password=suasenha");
if (!$link) {
	die('Erro ao conectar com o banco PGSQL!');
}
?> 