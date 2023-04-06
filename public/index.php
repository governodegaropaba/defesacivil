<?php
echo "Iniciando...<br>";
$comando = escapeshellcmd('python3 alerta_dados_pcd.py');
$cmdResult = shell_exec($comando);
echo $cmdResult;
echo "<br>Fim do processo<br>";
?>