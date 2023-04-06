<?php 
include("../conect/conexao.php");
$id = $_GET["id"];
$horas = $_GET["h"];
$ano = $_GET["ano"];	
$idn = substr($id,0,1);

if ($id === 'ga'){
	$nome_estacao = 'Gamboa';
	$estacao = '420570401A';
}elseif ($id === 'cd'){
	$nome_estacao = 'Campo Duna';
	$estacao = '420570402A';
}else{
	$nome_estacao = 'Areias de Palhocinha';
	$estacao = '420570403A';
}

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Gráfico Anual</title>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<h2 align="center">Chuvas no ano de <?php echo $ano?> - Mês a mês - Estação: <?php echo $nome_estacao?></h2>
		
<script type="text/javascript">
 google.charts.load("current", {packages:["corechart"]});
 google.load("visualization", "1", {packages:["corechart"]});
 
 google.setOnLoadCallback(drawChart);
 google.setOnLoadCallback(drawCharti);
 
 function drawChart() {
	var data = google.visualization.arrayToDataTable([
		['class Name',''],
		<?php 
			$vol = "select mes, month, trunc(sum(valor),2) as volume from alerta_pcd_historico aph where sensor = 'chuva' and cod_estacao = '$estacao' and ano = '$ano' group by mes, month order by mes ";
			$volume = pg_query($link, $vol);
			$qtde = pg_num_rows($volume);
			if($qtde > 0){
				while($row = pg_fetch_assoc($volume)){	
				 echo "['".$row['month']."',".$row['volume']."],";
				}							
			}

		?> 
	]);

	var options = {
		title: 'Chuvas',
		is3D: true,
		pieHole: 0.5,
			pieSliceTextStyle: {
				series: {
					0: { color: '#43459d' }
				}	
			},
			legend: 'P'
	 };
	 var chart = new google.visualization.LineChart(document.getElementById("chuva")).draw(data, options);
 }

</script>
</head>

<body>
	<div class="container-fluid">
		<h2 align="center">Amostragem de chuvas</h2>
		<div id="chuva" style="width: 100%; height: 500px;"></div>	
	</div>	
</body>
</html>	 
	 
	 