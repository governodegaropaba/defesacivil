<?php
include("../conect/conexao.php");

function data_br($data){
    return date("d/m H:i", strtotime($data));
}

$date_24 = date("Y-m-d H:m:s", strtotime('-24 hours', time()));
$date_36 = date("Y-m-d H:m:s", strtotime('-36 hours', time()));
$date_48 = date("Y-m-d H:m:s", strtotime('-48 hours', time()));
$date_54 = date("Y-m-d H:m:s", strtotime('-54 hours', time()));

//DATA MINIMA GAMBOA
$chuvas_sql_ud_ga = "select * from public.alerta_pcd_historico where cod_estacao = '420570403A' and sensor = 'chuva' order by datahora";
$chuvas_exec_ud_ga = pg_query($link, $chuvas_sql_ud_ga);
$min_ga = '2015-03-05 17:00:00';
while($row = pg_fetch_assoc($chuvas_exec_ud_ga)){	
	$periodo = '24:00:00';
	$periodo = vsprintf(" +%d hours +%d minutes +%d seconds", explode(':', $periodo)); 
	$max_ga = date('Y-m-d H:i:s', strtotime($min_ga.$periodo));
	$chuvas_sql = "select cod_estacao, sum(valor) as qtd from public.alerta_pcd_historico where cod_estacao = '420570402A' and sensor = 'chuva' and datahora between '$min_ga' and '$max_ga' group by cod_estacao";
	$chuvas_exec = pg_query($link, $chuvas_sql);
	while($row1 = pg_fetch_assoc($chuvas_exec)){
		$qtd = $row1['qtd'];
		$cod_estacao = $row1['cod_estacao'];
		$chuvas_sql1 = "INSERT INTO public.maior (datahora_ini, datahora_fim, valor, cod_estacao, periodo) VALUES ( '$min_ga', '$max_ga', $qtd, '$cod_estacao', '24')";
		//echo $chuvas_sql1;
		$chuvas_exec1 = pg_query($link, $chuvas_sql1);
	}

	$periodo = '36:00:00';
	$periodo = vsprintf(" +%d hours +%d minutes +%d seconds", explode(':', $periodo)); 
	$max_ga = date('Y-m-d H:i:s', strtotime($min_ga.$periodo));	
	$chuvas_sql = "select cod_estacao, sum(valor) as qtd from public.alerta_pcd_historico where cod_estacao = '420570402A' and sensor = 'chuva' and datahora between '$min_ga' and '$max_ga' group by cod_estacao";
	$chuvas_exec = pg_query($link, $chuvas_sql);
	while($row1 = pg_fetch_assoc($chuvas_exec)){
		$qtd = $row1['qtd'];
		$cod_estacao = $row1['cod_estacao'];
		$chuvas_sql1 = "INSERT INTO public.maior (datahora_ini, datahora_fim, valor, cod_estacao, periodo) VALUES ( '$min_ga', '$max_ga', $qtd, '$cod_estacao', '36')";
		//echo $chuvas_sql1;
		$chuvas_exec1 = pg_query($link, $chuvas_sql1);
	}

	$periodo = '48:00:00';
	$periodo = vsprintf(" +%d hours +%d minutes +%d seconds", explode(':', $periodo)); 
	$max_ga = date('Y-m-d H:i:s', strtotime($min_ga.$periodo));
	$chuvas_sql = "select cod_estacao, sum(valor) as qtd from public.alerta_pcd_historico where cod_estacao = '420570402A' and sensor = 'chuva' and datahora between '$min_ga' and '$max_ga' group by cod_estacao";
	$chuvas_exec = pg_query($link, $chuvas_sql);
	while($row1 = pg_fetch_assoc($chuvas_exec)){
		$qtd = $row1['qtd'];
		$cod_estacao = $row1['cod_estacao'];
		$chuvas_sql1 = "INSERT INTO public.maior (datahora_ini, datahora_fim, valor, cod_estacao, periodo) VALUES ( '$min_ga', '$max_ga', $qtd, '$cod_estacao', '48')";
		//echo $chuvas_sql1;
		$chuvas_exec1 = pg_query($link, $chuvas_sql1);
	}

	$periodo = '54:00:00';
	$periodo = vsprintf(" +%d hours +%d minutes +%d seconds", explode(':', $periodo)); 
	$max_ga = date('Y-m-d H:i:s', strtotime($min_ga.$periodo));
	$chuvas_sql = "select cod_estacao, sum(valor) as qtd from public.alerta_pcd_historico where cod_estacao = '420570402A' and sensor = 'chuva' and datahora between '$min_ga' and '$max_ga' group by cod_estacao";
	$chuvas_exec = pg_query($link, $chuvas_sql);
	while($row1 = pg_fetch_assoc($chuvas_exec)){
		$qtd = $row1['qtd'];
		$cod_estacao = $row1['cod_estacao'];
		$chuvas_sql1 = "INSERT INTO public.maior (datahora_ini, datahora_fim, valor, cod_estacao, periodo) VALUES ( '$min_ga', '$max_ga', $qtd, '$cod_estacao', '54')";
		//echo $chuvas_sql1;
		$chuvas_exec1 = pg_query($link, $chuvas_sql1);
	}	
    $min_ga = $max_ga;
	//echo $min_ga." - ".$max_ga;
	//echo "<br>"	;
}
//echo "Fim do processo AP";
//exit;
//echo '$min_ga: '.$min_ga;
//echo '<br>$max_ga: '.$max_ga;


//DATA MINIMA GAMBOA
$chuvas_sql_ud_ga = "select min(datahora) as datahora from public.alerta_pcd where cod_estacao = '420570401A' and sensor = 'chuva'";
$chuvas_exec_ud_ga = pg_query($link, $chuvas_sql_ud_ga);
while($row = pg_fetch_assoc($chuvas_exec_ud_ga)){
	$min_ga = $row['datahora'];	
}

$gamboa_sql = "select * from public.alerta_pcd_historico where cod_estacao = '420570401A' and datahora between '$date_54' and '$hoje' ";
$gamboa_exec = pg_query($link, $gamboa_sql);
$qtde_cdmboa = pg_num_rows($gamboa_exec);

$cduna_sql = "select * from public.alerta_pcd where cod_estacao = '420570402A' and datahora between '$date_54' and '$hoje'";
$cduna_exec = pg_query($link, $cduna_sql);
$qtde_cduna = pg_num_rows($cduna_exec);

$ap_sql = "select * from public.alerta_pcd where cod_estacao = '420570403A' and datahora between '$date_54' and '$hoje' ";
$ap_exec = pg_query($link, $ap_sql);
$qtde_ap = pg_num_rows($ap_exec);
 
 
//DATA MÁXIMA GAMBOA
$chuvas_sql_ud_ga = "select max(datahora) as datahora from public.alerta_pcd where cod_estacao = '420570401A' and sensor = 'chuva'";
$chuvas_exec_ud_ga = pg_query($link, $chuvas_sql_ud_ga);
while($row = pg_fetch_assoc($chuvas_exec_ud_ga)){
	$ud_ga = $row['datahora'];
}

//DATA MÁXIMA CAMPO DUNA
$chuvas_sql_ud_cd = "select max(datahora) as datahora from public.alerta_pcd where cod_estacao = '420570402A' and sensor = 'chuva'";
$chuvas_exec_ud_cd = pg_query($link, $chuvas_sql_ud_cd);
while($row = pg_fetch_assoc($chuvas_exec_ud_cd)){
	$ud_cd = $row['datahora'];
}

//DATA MÁXIMA A.PALHOCINHA 
$chuvas_sql_ud_ap = "select max(datahora) as datahora from public.alerta_pcd where cod_estacao = '420570403A' and sensor = 'chuva'";
$chuvas_exec_ud_ap = pg_query($link, $chuvas_sql_ud_ap);
while($row = pg_fetch_assoc($chuvas_exec_ud_ap)){
	$ud_ap = $row['datahora'];
}


//CHUVAS 24 HORAS ATRÁS 
//SOMA MM GAMBOA
$chuvas_sql_ga = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570401A' and datahora between '$date_24' and '$hoje' and sensor = 'chuva'";
//echo "ga_24: ".$chuvas_sql_ga;
$chuvas_exec_ga = pg_query($link, $chuvas_sql_ga);
while($row = pg_fetch_assoc($chuvas_exec_ga)){
	if($row['volume'] > 0){
		$volume_ga = $row['volume'];
	}else{
		$volume_ga = 0;
	}
}

echo 'FIM';
?>
