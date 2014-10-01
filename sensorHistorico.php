<?php 
include "header.php";

$pk_sensor = $_GET['id_sensor'];

$num_historico = 20;

if($_SESSION['permissao'] >= 5){

$db = conectaBD();

?>
<script>
	$(function() {
/*		var T = 50;
		var UR = 30;$
		var To=T-(14.55+0.114*T)*(1-(0.01*UR))-Math.pow(((2.5+0.007*T)*(1-(0.01*UR))),3)-(15.9+0.117*T)*Math.pow((1-(0.01*UR)),14);
		alert(To); */
		var data = [
		<?php 
			//PARA EVITAR FAZER NOVOS SELECTS
			$maior = -99999;
			$menor =  99999;
			$query = "SELECT * FROM (SELECT * FROM consultas_sensor_historico WHERE fk_sensor = ".$pk_sensor." AND data > DATE_SUB(NOW(), INTERVAL 12 HOUR) order by pk_consulta desc) as consulta order by pk_consulta";
			$result = mysql_query($query);
			while($row=mysql_fetch_array($result)){
				if($maior < $row['valor_lido'])$maior = $row['valor_lido'];
				if($menor > $row['valor_lido'])$menor = $row['valor_lido'];

				echo "[".(strtotime($row['data'])*1000).",".$row['valor_lido']."],";
			}

		?>
		];
		

		function porcentagem(v, axis) {
			return v.toFixed(axis.tickDecimals) + "%";
		}
		function unidade(v, axis){
			return v.toFixed(axis.tickDecimals) + <?php echo unidade_sensor(tipo_sensor($pk_sensor));?>;
		}

		function doPlot(position) {
			$.plot("#placeholder", [
				{ data: data },
			], {
			grid: {
				hoverable: true,	
				clickable: true
			},
			series: {
				shadowSize: 0,	// Drawing is faster without shadows
				color: "#000394"
			},
				xaxes: [ { mode: "time", timeformat: "%d/%m %H:%M"}],
				yaxes: [ { 
					min: <?php echo min_grafico(tipo_sensor($pk_sensor));?>,
					max: <?php echo max_grafico(tipo_sensor($pk_sensor));?>,
					tickSize: <?php echo intervalo_linhas_y(tipo_sensor($pk_sensor));?>,

				}]
			});
		}
		doPlot("right");


	});
</script>
<?php 
desconectaBD($db); ?>


	<div id="content">
		<div class="wrapper">
			<section>
				<h3 class="title">Monitoramento</h3>

<?php


	 $db = conectaBD();
	 $sql = mysql_query("
		SELECT
			sen.pk_sensor,
		   	tipos.descricao, tipos.pk_sensor_tipo,
		   	salas.nome
		FROM 
		   sensores sen 
		INNER JOIN 
		   sensores_tipos tipos
		ON 
		   sen.fk_sensor_tipo = tipos.pk_sensor_tipo
		INNER JOIN
			salas
		ON
			fk_sala = pk_sala
		WHERE
		   sen.pk_sensor = '".$pk_sensor."'");
		desconectaBD($db);

		$row = mysql_fetch_array($sql);
	
	    $descricao = $row["descricao"];
	    $local = $row['nome'];

		?> 	 
	   
			<h4>
					Gráfico <?php echo $descricao." - Sala ".$local."";?>
			</h4>

			<?php
			if($menor == 99999){
				echo "<p>Não existem valores desse sensor nas últimas 12 horas para ser construído o gráfico</p>";
			}
			else{
				?>
				<div class="graph-content" style="height:550px;">
					<div id="placeholder" class="graph-placeholder"></div>
				</div>
				<br/>
				<?php
			}	?>

			 <br/>
			 <h4>Últimos <?php echo $num_historico;?> Valores Coletados</h4>

			 <table class="table table-hover">
			   <thead>
				  <tr>
					<th>DATA E HORÁRIO </th>
					<th>VALOR LIDO</th>
				  </tr>
			   <tbody>
			      <?php 
			      	$db = conectaBD();
			      	$query = "SELECT fk_sensor_tipo FROM sensores WHERE pk_sensor = ".$pk_sensor;
			      	$result = mysql_query($query);
			      	$row = mysql_fetch_array($result);
			      	$query = "SELECT * FROM consultas_sensor_historico WHERE fk_sensor = ".$pk_sensor." order by pk_consulta desc limit ".$num_historico;
			      	$sql3 = mysql_query($query);
			      	desconectaBD($db);
			      	$i = 0;
			      	while($row3 = mysql_fetch_array($sql3))
			      	{
			      	 echo "<tr>
			      	          <td>".$row3['data']."</td>
			      	          <td>".$row3['valor_lido'].unidade_sensor($row['fk_sensor_tipo'])."</td>
			      	       </tr>";
			      	 
			      	}
			      ?>
			   </tbody>
		 			
			 </table>

		</section>
	</div>
</div>
<?php
	}
	else if($GLOBALS['status_empresa'] == 0){
		echo "<h3 class='title' style='text-align:center;'>Erro 120 - Sistema inativo.</h3>";
	}
	else echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./index.php'>";
	include "footer.php";
?>
