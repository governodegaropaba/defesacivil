<?php 
include("../conect/conexao.php");
$id = $_GET["id"];
$horas = $_GET["h"];
//echo "Id: ".$id;
//echo "<br>Hora: ".$horas;
$idn = substr($id,0,1);
//echo "<br>Idn: ".$idn;
if ($idn === '1'){
	$nome_estacao = 'Gamboa';
	$estacao = '420570401A';
}elseif ($idn === '2'){
	$nome_estacao = 'Campo Duna';
	$estacao = '420570402A';
}else{
	$nome_estacao = 'Areias de Palhocinha';
	$estacao = '420570403A';
}
//echo "<br>Nome estacao: ".$nome_estacao;
//echo "<br>estacao: ".$estacao;
//DEFINE VARIÁVEIS DE TEMPO
$hoje = date('Y-m-d H:i:s');
$ultima_hora = date("Y-m-d H:m:s", strtotime('-1 hour', time()));
$date_24 = date("Y-m-d H:m:s", strtotime('-24 hours', time()));
$date_36 = date("Y-m-d H:m:s", strtotime('-36 hours', time()));
$date_48 = date("Y-m-d H:m:s", strtotime('-48 hours', time()));
$date_54 = date("Y-m-d H:m:s", strtotime('-54 hours', time()));
//DEFINE MAIOR DATA 
$maior_data = $hoje;
if($horas == '24'){	
$menor_data = $date_24;	
}elseif($horas == '36'){
$menor_data = $date_36;	
}elseif($horas == '48'){
$menor_data = $date_48;	
}else{
$menor_data = $date_54;	
}	
 
//FORMATAÇÃO DA DATA PARA HORA
function data_br($data){
    return date("d/m/Y H:i", strtotime($data));
}

// FORMATA HORA		
function data_hr($data){
    return date("H:i", strtotime($data));
}

?>


<!DOCTYPE HTML>
<html>
<head>

 <title>Gráfico</title>

 <script type="text/javascript" src="https://www.google.com/jsapi"></script>
 <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
 <script type="text/javascript">
 google.charts.load("current", {packages:["corechart"]});
 google.load("visualization", "1", {packages:["corechart"]});
 
 google.setOnLoadCallback(drawChart);
 google.setOnLoadCallback(drawCharti);
 
 function drawChart() {
 var data = google.visualization.arrayToDataTable([

 ['class Name',''],
 <?php 
	$query1 = "select cod_estacao, sum(valor) as qtde from public.alerta_pcd where cod_estacao = '$estacao' and sensor = 'chuva' and datahora between '$menor_data' and '$maior_data' group by cod_estacao";
	$exec1 = pg_query($link,$query1);
	while($row1 = pg_fetch_assoc($exec1)){
		$volume = $row1['qtde'];	
	}
	 
	$query = "select datahora, valor from public.alerta_pcd where cod_estacao = '$estacao' and sensor = 'chuva' and datahora between '$menor_data' and '$maior_data' order by datahora";
	 $exec = pg_query($link,$query);
	 while($row = pg_fetch_assoc($exec)){
			
		echo "['".data_hr($row['datahora'])."',".$row['valor']."],";
	 } 
?> 
]);

 var options = {
 title: 'Chuvas Últimas <?php echo $horas; ?>h - <?php echo $nome_estacao; ?> - <?php echo data_br($menor_data)." e ".data_br($maior_data)." - mm acumulados: ".number_format($volume, 2, '.', ''); ?>',
	is3D: true,
  pieHole: 0.5,
          pieSliceTextStyle: {
          series: {
            0: { color: '#43459d' }
		  }	
          },
          legend: 'P'
 };
 var chart = new google.visualization.LineChart(document.getElementById("chuva54h")).draw(data, options);
 }

//INTENSIDADE-PRECIPITAÇÃO
function drawCharti() {
 var data = google.visualization.arrayToDataTable([

 ['class Name',''],
 <?php 
	$query1 = "select cod_estacao, sum(valor) as qtde from public.alerta_pcd where cod_estacao = '$estacao' and sensor = 'intensidade_precipitacao' and datahora between '$menor_data' and '$maior_data' group by cod_estacao";
	$exec1 = pg_query($link,$query1);
	while($row1 = pg_fetch_assoc($exec1)){
		$volume = $row1['qtde'];	
	}
	 
	$query = "select datahora, valor from public.alerta_pcd where cod_estacao = '$estacao' and sensor = 'intensidade_precipitacao' and datahora between '$menor_data' and '$maior_data' order by datahora";
	 $exec = pg_query($link,$query);
	 while($row = pg_fetch_assoc($exec)){			
		echo "['".data_hr($row['datahora'])."',".$row['valor']."],";
	 }	 
?> 
]);

 var options = {
 title: 'Intensidade/Precipitação Últimas <?php echo $horas; ?>h - <?php echo $nome_estacao; ?> - <?php echo data_br($menor_data)." e ".data_br($maior_data)." - mm acumulados: ".number_format($volume, 2, '.', ''); ?>',
	is3D: true,
  pieHole: 0.5,
          pieSliceTextStyle: {
            color: 'red',
          },
          legend: 'P'
 };
 var chart = new google.visualization.LineChart(document.getElementById("intensidade54h")).draw(data, options);
 }	
</script>
</head>

<body>
	<div class="container-fluid">
		<h2 align="center">Amostragem de chuvas e precipitação</h2>
		<div id="chuva54h" style="width: 100%; height: 500px;"></div>	
		<div id="intensidade54h" style="width: 100%; height: 500px;"></div>
	</div>	
</body>
</html>	 
	 