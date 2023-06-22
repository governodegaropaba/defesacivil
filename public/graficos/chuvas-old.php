<?php
include("../conect/conexao.php");

function data_br($data){
    return date("d/m H:i", strtotime($data));
}

function alertas($qtde, $volume, $cor, $ud, $estacao, $periodo, $local){
	
	if ($volume > 0){
		$nome		= 'DTI - GAROPABA';	
		$alerta		= 'Chuva '.$volume.' mm'.' - '.$periodo.' - '.$local;	

		// Variável que junta os valores acima e monta o corpo do email

		$corpo = "Remetente: $nome\n\nAlerta: $alerta\n\n";

		require_once("phpmailer/class.phpmailer.php");

		define('GUSER', 'dev@garopaba.sc.gov.br');	// <-- Insira aqui o seu GMail
		define('GPWD', '2020.acaba');		// <-- Insira aqui a senha do seu GMail
		
		$assunto = "Alerta ".$cor." - ".$local;
		
		function smtpmailer($de, $de_nome, $assunto, $corpo) { 
			global $error;

			$mail = new PHPMailer();
			$mail->IsSMTP();						// Ativar SMTP
			$mail->SMTPDebug = 0;					// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
			$mail->SMTPAuth = true;					// Autenticação ativada
			$mail->SMTPSecure = 'ssl';				// SSL REQUERIDO pelo GMail
			$mail->Host = 'smtp.fecamsc.org.br';	// SMTP utilizado
			$mail->Port = 465;  					// A porta 465 deverá estar aberta em seu servidor
			$mail->Username = GUSER;
			$mail->Password = GPWD;
			$mail->SetFrom($de, $de_nome);
			$mail->Subject = $assunto;
			$mail->Body = $corpo;
			$mail->AddAddress('luispaglioza@gmail.com');
			$mail->AddCC('defesacivil@garopaba.sc.gov.br');
			if(!$mail->Send()) {
				$error = 'Mail error: '.$mail->ErrorInfo;
				echo "Erro: ".$error;	
				return false;
			} else {
				$error = '<br>OK!';
				return true;
			}
			
		}
		
		// abaixo o email que irá receber a mensagem, o email que irá enviar (o mesmo da variável GUSER), 
		// nome do email que envia a mensagem, o Assunto da mensagem e por último a variável com o corpo do email.

		if (smtpmailer('dev@garopaba.sc.gov.br', 'DTI - GAROPABA/SC', 'Alerta '.$cor.' - '.$local, $corpo)) {
			//echo "Email de alerta enviado!";
		}
		if (!empty($error)) echo $error;		
	}
}

$hoje = date('Y-m-d H:i:s');
$date_24 = date("Y-m-d H:m:s", strtotime('-24 hours', time()));
$date_36 = date("Y-m-d H:m:s", strtotime('-36 hours', time()));
$date_48 = date("Y-m-d H:m:s", strtotime('-48 hours', time()));
$date_54 = date("Y-m-d H:m:s", strtotime('-54 hours', time()));

$data_sql = "update alerta_pcd set datahora_gmt = datahora, horaok = 1, datahora = datahora - interval '3 hours' where horaok is null";
$data_sql_exec = pg_query($link, $data_sql);

$gamboa_sql = "select * from public.alerta_pcd where cod_estacao = '420570401A' and datahora between '$date_54' and '$hoje'";
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

//SOMA MM CAMPO DUNA
$chuvas_sql_cd = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570402A' and datahora between '$date_24' and '$hoje' and sensor = 'chuva'";
//echo "<br>cd_24: ".$chuvas_sql_cd;
$chuvas_exec_cd = pg_query($link, $chuvas_sql_cd);
while($row = pg_fetch_assoc($chuvas_exec_cd)){
	if($row['volume'] > 0){
		$volume_cd = $row['volume'];
	}else{
		$volume_cd = 0;
	}
}
 
//SOMA MM A.PALHOCINHA 
$chuvas_sql_ap = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570403A' and datahora between '$date_24' and '$hoje' and sensor = 'chuva'";
//echo "<br>ap_24: ".$chuvas_sql_ap;
$chuvas_exec_ap = pg_query($link, $chuvas_sql_ap);
while($row = pg_fetch_assoc($chuvas_exec_ap)){
	if($row['volume'] > 0){
		$volume_ap = $row['volume'];
	}else{
		$volume_ap = 0;
	}	
}

//CHUVAS 36 HORAS ATRÁS 
$chuvas_sql_ga_36 = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570401A' and datahora between '$date_36' and '$hoje' and sensor = 'chuva'";
//echo "<br>ga_36: ".$chuvas_sql_ga_36;
$chuvas_exec_ga_36 = pg_query($link, $chuvas_sql_ga_36);
while($row = pg_fetch_assoc($chuvas_exec_ga_36)){
	if($row['volume'] > 0){
		$volume_ga_36 = $row['volume'];
	}else{
		$volume_ga_36 = 0;
	}
}

$chuvas_sql_cd_36= "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570402A' and datahora between '$date_36' and '$hoje' and sensor = 'chuva'";
//echo "<br>cd_36: ".$chuvas_sql_cd_36;
$chuvas_exec_cd_36= pg_query($link, $chuvas_sql_cd_36);
while($row = pg_fetch_assoc($chuvas_exec_cd_36)){
	if($row['volume'] > 0){
		$volume_cd_36 = $row['volume'];
	}else{
		$volume_cd_36 = 0;
	}
}
 
$chuvas_sql_ap_36 = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570403A' and datahora between '$date_36' and '$hoje' and sensor = 'chuva'";
//echo "<br>ap_36: ".$chuvas_sql_ap_36;
$chuvas_exec_ap_36 = pg_query($link, $chuvas_sql_ap_36);
while($row = pg_fetch_assoc($chuvas_exec_ap_36)){
	if($row['volume'] > 0){
		$volume_ap_36 = $row['volume'];
	}else{
		$volume_ap_36 = 0;
	}
}

//CHUVAS 48 HORAS ATRÁS 
$chuvas_sql_ga_48 = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570401A' and datahora between '$date_48' and '$hoje' and sensor = 'chuva'";
//echo "<br>ga_48: ".$chuvas_sql_ga_48;
$chuvas_exec_ga_48 = pg_query($link, $chuvas_sql_ga_48);
while($row = pg_fetch_assoc($chuvas_exec_ga_48)){
	if($row['volume'] > 0){
		$volume_ga_48 = $row['volume'];
	}else{
		$volume_ga_48 = 0;
	}
}

$chuvas_sql_cd_48= "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570402A' and datahora between '$date_48' and '$hoje' and sensor = 'chuva'";
//echo "<br>cd_48: ".$chuvas_sql_cd_48;
$chuvas_exec_cd_48= pg_query($link, $chuvas_sql_cd_48);
while($row = pg_fetch_assoc($chuvas_exec_cd_48)){
	if($row['volume'] > 0){
		$volume_cd_48 = $row['volume'];
	}else{
		$volume_cd_48 = 0;
	}
}
 
$chuvas_sql_ap_48 = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570403A' and datahora between '$date_48' and '$hoje' and sensor = 'chuva'";
//echo "<br>ap_48: ".$chuvas_sql_ap_48;
$chuvas_exec_ap_48 = pg_query($link, $chuvas_sql_ap_48);
while($row = pg_fetch_assoc($chuvas_exec_ap_48)){
	if($row['volume'] > 0){
		$volume_ap_48 = $row['volume'];
	}else{
		$volume_ap_48 = 0;
	}
}


//CHUVAS 54 HORAS ATRÁS 
$chuvas_sql_ga_54 = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570401A' and datahora between '$date_54' and '$hoje' and sensor = 'chuva'";
//echo "<br>ga_54: ".$chuvas_sql_ga_54;
$chuvas_exec_ga_54 = pg_query($link, $chuvas_sql_ga_54);
while($row = pg_fetch_assoc($chuvas_exec_ga_54)){
	if($row['volume'] > 0){
		$volume_ga_54 = $row['volume'];
	}else{
		$volume_ga_54 = 0;
	}
}

$chuvas_sql_cd_54= "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570402A' and datahora between '$date_54' and '$hoje' and sensor = 'chuva'";
//echo "<br>cd_54: ".$chuvas_sql_cd_54;
$chuvas_exec_cd_54= pg_query($link, $chuvas_sql_cd_54);
while($row = pg_fetch_assoc($chuvas_exec_cd_54)){
	if($row['volume'] > 0){
		$volume_cd_54 = $row['volume'];
	}else{
		$volume_cd_54 = 0;
	}
}
 
$chuvas_sql_ap_54 = "select sum(valor) as volume from public.alerta_pcd where cod_estacao = '420570403A' and datahora between '$date_54' and '$hoje' and sensor = 'chuva'";
//echo "<br>ap_54: ".$chuvas_sql_ap_54;
$chuvas_exec_ap_54 = pg_query($link, $chuvas_sql_ap_54);
while($row = pg_fetch_assoc($chuvas_exec_ap_54)){
	if($row['volume'] > 0){
		$volume_ap_54 = $row['volume'];
	}else{
		$volume_ap_54 = 0;
	}
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta http-equiv="refresh" content="10" />
  <title>Defesa Civil</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <link rel="stylesheet" href="../css/style.css" />  
</head>
<div class="container" style="padding-left: 15%;">
	<body>
		<div id="titulo"><a href="index.php"><img src="../img/garopaba.png"></a><p class="titulo">PREFEITURA MUNICIPAL DE GAROPABA/SC</p></div>
		<div id="fim" style="padding-left: 15%">	
			<a href="http://defesacivil.prefa.br/graficos/"><button type="submit" id="voltar" class="btn btn-success btn-sm">Voltar</button></a>
		</div>
		<?php if ($qtde_cdmboa == 0 and $qtde_ap == 0 and $qtde_cduna == 0){ ?>
			<nav class="navbar navbar-expand-sm bg-light">
			  <div class="container-fluid" style="padding-left: 20%">
				<ul class="navbar-nav">
				  <li class="nav-item">
					<a class="nav-link" style="font-size: 1.6rem; color: navy;" href="historico.php" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
					</svg> Base histórica <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
					</svg></a>
				  </li>  
				</ul>
			  </div>
			</nav>
			<div class="container-fluid mt-3" style="padding-left: 20%">
				<h5>Monitoramento Pluviométrico</h5>
				<h5>Com dados do Cemaden - Centro Nacional de Monitoramento e Alertas de Desastres Naturais</br>
				 Garopaba apresenta dados de monitoramento de índice de chuvas e precipitação.<br>				 
				</h5>
				<h4 style="color: green; text-align: justify;">Não há registro de chuvas nas últimas 54 horas para<br> nenhuma das estações de monitoramento!</h4>
				<footer>
					<div id="footer" style="padding-left:41%; padding-top: 30px;text-align: center;"><a href="https://garopaba.atende.net/" target="_blank"><p>DTI - GAROPABA - JANEIRO/2023</p></a></div>
				</footer>				
			</div>
		<?php }else{ ?>
			<h5 style="text-align: center; padding-top: 25px;">Monitoramento Pluviométrico</h5>
	
			<table class="table-sm table1" >
				<thead>
				  <tr>
					<th width="33%">Gamboa</th>
					<th width="33%">Campo Duna</th>
					<th width="33%">Areias de Palhocinha</th>
				  </tr>
				</thead>
				<tbody>
					<tr>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=1a&h=24" target="_blank"> <img src="../img/24h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=2a&h=24" target="_blank"> <img src="../img/24h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=3a&h=24" target="_blank"> <img src="../img/24h.png" width="60px" title="Visualizar gráfico"></a></td>
					</tr>                                                                                                                                                     
					<tr> 	                                                                                                                                                  
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=1b&h=36" target="_blank"> <img src="../img/36h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=2b&h=36" target="_blank"> <img src="../img/36h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=3b&h=36" target="_blank"> <img src="../img/36h.png" width="60px" title="Visualizar gráfico"></a></td>		
					</tr>                                                                                                                                                     
					<tr>	                                                                                                                                                  
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=1c&h=48" target="_blank"> <img src="../img/48h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=2c&h=48" target="_blank"> <img src="../img/48h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=3c&h=48" target="_blank"> <img src="../img/48h.png" width="60px" title="Visualizar gráfico"></a></td>
					</tr>                                                                                                                                                     
					<tr>	                                                                                                                                                  
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=1d&h=54" target="_blank"> <img src="../img/54h.png" width="60px" title="Visualizar gráfico"></a></td>
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=2d&h=54" target="_blank"> <img src="../img/54h.png" width="60px" title="Visualizar gráfico"></a></td>	
						<td><a class="nav-link" style="font-size: 1.6rem; color: navy;" href="graficos.php?id=3d&h=54" target="_blank"> <img src="../img/54h.png" width="60px" title="Visualizar gráfico"></a></td>
					</tr>
			</table>

				<div>
					<!---------------------------------------- ALERTAS 24 HORAS -------------------------------------------->
					<?php 	if($volume_ga == 0){ 
								echo '<div class="card text-black mb-3 ga24sc">';
									echo '<p class="card-text">0 mm  24h</p>';
								echo "</div>";	
							}else{
								echo '<div class="card text-black mb-3 ga24">';
									echo '<div class="card-header">Última leitura:<br> '.data_br($ud_ga).'</div>';
								echo "</div>";								
							}?>					
					<?php 	if($volume_ga >= 0.2 and $volume_ga <= 70){ 	
								echo '<div class="card text-black mb-3 amarelo">';
									echo '<p class="card-text">'.number_format($volume_ga, 2, '.', '').' mm/24h</p>';;									
								echo '</div>';		
							}?>
					<?php 	if($volume_ga > 70 and $volume_ga <= 100){ 
								echo '<div class="card text-black mb-3 laranja">';
									echo '<p class="card-text">'.number_format($volume_ga, 2, '.', '').' mm/24h</p>';;									
								echo '</div>';
							}?>	
					<?php 	if($volume_ga > 100 and $volume_ga <= 150){ 		
							echo '<div class="card text-black mb-3 vermelho">';
								echo '<p class="card-text">'.number_format($volume_ga, 2, '.', '').' mm/24h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ga > 150){ 	
							echo '<div class="card text-black mb-3 roxo">';
								echo '<p class="card-text">'.number_format($volume_ga, 2, '.', '').' mm/24h</p>';;									
							echo '</div>';
					}?>								
					<!---------------------------------------- ALERTAS 36 HORAS -------------------------------------------->
					<?php if($volume_ga_36 == 0){ 	
							echo '<div class="card text-black mb-3 ga24sc">';
								echo '<p class="card-text">0 mm  36h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_ga_36 >= 0.2 and $volume_ga_36 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo">';
								echo '<p class="card-text">'.number_format($volume_ga_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_ga_36 > 70 and $volume_ga_36 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja">';
								echo '<p class="card-text">'.number_format($volume_ga_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_ga_36 > 100 and $volume_ga_36 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho">';
								echo '<p class="card-text">'.number_format($volume_ga_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ga_36 > 150){ 		
							echo '<div class="card text-black mb-3 roxo">';
								echo '<p class="card-text">'.number_format($volume_ga_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>	
					<!---------------------------------------- ALERTAS 48 HORAS -------------------------------------------->
					<?php if($volume_ga_48 == 0){ 	
							echo '<div class="card text-black mb-3 ga24sc">';
								echo '<p class="card-text">0 mm  48h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_ga_48 >= 0.2 and $volume_ga_48 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo">';
								echo '<p class="card-text">'.number_format($volume_ga_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_ga_48 > 70 and $volume_ga_48 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja">';
								echo '<p class="card-text">'.number_format($volume_ga_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_ga_48 > 100 and $volume_ga_48 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho">';
								echo '<p class="card-text">'.number_format($volume_ga_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ga_48 > 150){ 		
							echo '<div class="card text-black mb-3 roxo">';
								echo '<p class="card-text">'.number_format($volume_ga_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>		
					<!---------------------------------------- ALERTAS 54 HORAS -------------------------------------------->
					<?php if($volume_ga_54 == 0){ 	
							echo '<div class="card text-black mb-3 ga24sc">';
								echo '<p class="card-text">0 mm  54h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_ga_54 >= 0.2 and $volume_ga_54 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo">';
								echo '<p class="card-text">'.number_format($volume_ga_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_ga_54 > 70 and $volume_ga_54 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja">';
								echo '<p class="card-text">'.number_format($volume_ga_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_ga_54 > 100 and $volume_ga_54 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho">';
								echo '<p class="card-text">'.number_format($volume_ga_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ga_54 > 150){ 		
							echo '<div class="card text-black mb-3 roxo">';
								echo '<p class="card-text">'.number_format($volume_ga_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>	
				</div>
				<div>	
					<!---------------------------------------- DEFINIÇÕES CAMPO DUNA---------------------------------------->	
					<!---------------------------------------- ALERTAS 24 HORAS -------------------------------------------->
					<?php 	if($volume_cd == 0){ 
								echo '<div class="card text-black mb-3 cd24sc" >';
									echo '<p class="card-text">0 mm  24h</p>';
								echo "</div>";	
							}else{
								echo '<div class="card text-black mb-3 cd24">';
									echo '<div class="card-header">Última leitura:<br> '.data_br($ud_cd).'</div>';
								echo "</div>";								
							}?>					
					<?php 	if($volume_cd >= 0.2 and $volume_cd <= 70){ 	
								echo '<div class="card text-black mb-3 amarelo_1">';
									echo '<p class="card-text">'.number_format($volume_cd, 2, '.', '').' mm/24h</p>';;									
								echo '</div>';		
							}?>
					<?php 	if($volume_cd > 70 and $volume_cd <= 100){ 
								echo '<div class="card text-black mb-3 laranja_1">';
									echo '<p class="card-text">'.number_format($volume_cd, 2, '.', '').' mm/24h</p>';;									
								echo '</div>';
							}?>	
					<?php 	if($volume_cd > 100 and $volume_cd <= 150){ 		
							echo '<div class="card text-black mb-3 vermelho_1">';
								echo '<p class="card-text">'.number_format($volume_cd, 2, '.', '').' mm/24h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_cd > 150){ 	
							echo '<div class="card text-black mb-3 roxo_1">';
								echo '<p class="card-text">'.number_format($volume_cd, 2, '.', '').' mm/24h</p>';;									
							echo '</div>';
					}?>								
					<!---------------------------------------- ALERTAS 36 HORAS -------------------------------------------->
					<?php if($volume_cd_36 == 0){ 	
							echo '<div class="card text-black mb-3 cd24sc">';
								echo '<p class="card-text">0 mm  36h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_cd_36 >= 0.2 and $volume_cd_36 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo_1">';
								echo '<p class="card-text">'.number_format($volume_cd_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_cd_36 > 70 and $volume_cd_36 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja_1">';
								echo '<p class="card-text">'.number_format($volume_cd_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_cd_36 > 100 and $volume_cd_36 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho_1">';
								echo '<p class="card-text">'.number_format($volume_cd_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_cd_36 > 150){ 		
							echo '<div class="card text-black mb-3 roxo_1">';
								echo '<p class="card-text">'.number_format($volume_cd_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>	
					<!---------------------------------------- ALERTAS 48 HORAS -------------------------------------------->
					<?php if($volume_cd_48 == 0){ 	
							echo '<div class="card text-black mb-3 cd24sc">';
								echo '<p class="card-text">0 mm 48h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_cd_48 >= 0.2 and $volume_cd_48 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo_1">';
								echo '<p class="card-text">'.number_format($volume_cd_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_cd_48 > 70 and $volume_cd_48 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja_1">';
								echo '<p class="card-text">'.number_format($volume_cd_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_cd_48 > 100 and $volume_cd_48 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho_1">';
								echo '<p class="card-text">'.number_format($volume_cd_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_cd_48 > 150){ 		
							echo '<div class="card text-black mb-3 roxo_1">';
								echo '<p class="card-text">'.number_format($volume_cd_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>		
					<!---------------------------------------- ALERTAS 54 HORAS -------------------------------------------->
					<?php if($volume_cd_54 == 0){ 	
							echo '<div class="card text-black mb-3 cd24sc">';
								echo '<p class="card-text">0 mm  54h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_cd_54 >= 0.2 and $volume_cd_54 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo_1">';
								echo '<p class="card-text">'.number_format($volume_cd_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_cd_54 > 70 and $volume_cd_54 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja_1">';
								echo '<p class="card-text">'.number_format($volume_cd_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_cd_54 > 100 and $volume_cd_54 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho_1">';
								echo '<p class="card-text">'.number_format($volume_cd_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_cd_54 > 150){ 		
							echo '<div class="card text-black mb-3 roxo_1">';
								echo '<p class="card-text">'.number_format($volume_cd_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>
				</div>
				<div>
					<!---------------------------------------- DEFINIÇÕES AREIAS DE PALHOCINHA ----------------------------->				
					<!---------------------------------------- ALERTAS 24 HORAS -------------------------------------------->
					<?php if($volume_ap == 0){ 
								echo '<div class="card text-black mb-3 ap24sc">';
									echo '<p class="card-text">0 mm  24h</p>';
								echo "</div>";	
							}else{
								echo '<div class="card text-black mb-3 ap24">';
									echo '<div class="card-header">Última leitura:<br> '.data_br($ud_ap).'</div>';
								echo "</div>";								
							}?>					
					<?php 	if($volume_ap >= 0.2 and $volume_ap <= 70){ 	
								echo '<div class="card text-black mb-3 amarelo_2">';
									echo '<p class="card-text">'.number_format($volume_ap, 2, '.', '').' mm/24h</p>';;									
								echo '</div>';		
							}?>
					<?php 	if($volume_ap > 70 and $volume_ap <= 100){ 
								echo '<div class="card text-black mb-3 laranja_2">';
									echo '<p class="card-text">'.number_format($volume_ap, 2, '.', '').' mm/24h</p>';;									
								echo '</div>';
							}?>	
					<?php 	if($volume_ap > 100 and $volume_ap <= 150){ 		
							echo '<div class="card text-black mb-3 vermelho_2">';
								echo '<p class="card-text">'.number_format($volume_ap, 2, '.', '').' mm/24h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ap > 150){ 	
							echo '<div class="card text-black mb-3 roxo_2">';
								echo '<p class="card-text">'.number_format($volume_ap, 2, '.', '').' mm/24h</p>';;									
							echo '</div>';
					}?>								
					<!---------------------------------------- ALERTAS 36 HORAS -------------------------------------------->
					<?php if($volume_ap_36 == 0){ 	
							echo '<div class="card text-black mb-3 ap24sc">';
								echo '<p class="card-text">0 mm  36h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_ap_36 >= 0.2 and $volume_ap_36 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo_2">';
								echo '<p class="card-text">'.number_format($volume_ap_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_ap_36 > 70 and $volume_ap_36 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja_2">';
								echo '<p class="card-text">'.number_format($volume_ap_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_ap_36 > 100 and $volume_ap_36 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho_2">';
								echo '<p class="card-text">'.number_format($volume_ap_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ap_36 > 150){ 		
							echo '<div class="card text-black mb-3 roxo_2">';
								echo '<p class="card-text">'.number_format($volume_ap_36, 2, '.', '').' mm/36h</p>';;									
							echo '</div>';
					}?>	
					<!---------------------------------------- ALERTAS 48 HORAS -------------------------------------------->
					<?php if($volume_ap_48 == 0){ 	
							echo '<div class="card text-black mb-3 ap24sc">';
								echo '<p class="card-text">0 mm  48h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_ap_48 >= 0.2 and $volume_ap_48 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo_2">';
								echo '<p class="card-text">'.number_format($volume_ap_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_ap_48 > 70 and $volume_ap_48 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja_2">';
								echo '<p class="card-text">'.number_format($volume_ap_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_ap_48 > 100 and $volume_ap_48 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho_2">';
								echo '<p class="card-text">'.number_format($volume_ap_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ap_48 > 150){ 		
							echo '<div class="card text-black mb-3 roxo_2">';
								echo '<p class="card-text">'.number_format($volume_ap_48, 2, '.', '').' mm/48h</p>';;									
							echo '</div>';
					}?>		
					<!---------------------------------------- ALERTAS 54 HORAS -------------------------------------------->
					<?php if($volume_ap_54 == 0){ 	
							echo '<div class="card text-black mb-3 ap24sc">';
								echo '<p class="card-text">0 mm  54h</p>';;									
							echo '</div>';		
					}?>
					<?php if($volume_ap_54 >= 0.2 and $volume_ap_54 <= 70){ 	
							echo '<div class="card text-black mb-3 amarelo_2">';
								echo '<p class="card-text">'.number_format($volume_ap_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>							
					<?php 	if($volume_ap_54 > 70 and $volume_ap_54 <= 100){ 		
							echo '<div class="card text-black mb-3 laranja_2">';
								echo '<p class="card-text">'.number_format($volume_ap_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';							
					}?>	
					<?php 	if($volume_ap_54 > 100 and $volume_ap_54 <= 150){ 
							echo '<div class="card text-black mb-3 vermelho_2">';
								echo '<p class="card-text">'.number_format($volume_ap_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>	
					<?php if($volume_ap_54 > 150){ 		
							echo '<div class="card text-black mb-3 roxo_2">';
								echo '<p class="card-text">'.number_format($volume_ap_54, 2, '.', '').' mm/54h</p>';;									
							echo '</div>';
					}?>	
				</div>							
				<!--</div>
			</div>-->

		<div id="fim" style="padding-left: 15%">
		<div id="alerta" class="alerta"><img src="../img/alerta.png"><p class="Legenda">Legenda - Cores de alerta</p></div>
		  <br>
		  	<!--<h5 style="text-align: center; padding-top: 25px; color: red;">SISTEMA EM TESTES</h5>-->
			  <p>Com dados do Cemaden - Centro Nacional de Monitoramento e Alertas de Desastres Naturais</br>
				 Garopaba apresenta dados de monitoramento de índice de chuvas e precipitação.<br>
				 <font color="red">**</font> Última leitura, refere-se a horário em que houve volume de chuva registrado.
			  </p>
		</div>
		<footer>
			<div id="footer" style="padding-left:23%; text-align: center;"><a href="https://garopaba.atende.net/" target="_blank"><p>Diretoria de Tecnologia da Informação - DTI - GAROPABA - JANEIRO/2023</p></a></div>
		</footer>
		<?php } 
		$link->close();
		?>
	</body>
</html>
</div>
