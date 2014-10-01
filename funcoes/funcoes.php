<?php
function unidade_sensor($tipo){
	switch($tipo){
		case 1: $unidade = " lux";
				break;
		case 2:
		case 5:
		case 10:$unidade = "ºC";
				break;
		case 4: 
		case 9:	$unidade = "%";
				break;				
		case 6: $unidade = "V";
				break;
		case 7: 
		case 8: $unidade = " dBm";
				break;
		default: $unidade = "";
	}
	return $unidade;
}

//DADO O TIPO RETORNA O VALOR MAXIMO PARA O GRAFICO REALTIME 
function max_grafico($tipo){
	switch($tipo){
		case 1: $max = 100;
				break;
		case 2:
		case 10: $max = 34;
				break;
		case 3: $max = 15;
				break;		
		case 5: $max = 25;
				break;				
		default:$max = 100;
	}
	return $max;
}

function min_grafico($tipo){
	switch($tipo){
		case 2: $min = 15;
				break;
		case 10:$min = 10;
				break;				
		default:$min = 0;
	}
	return $min;
}

function tipo_sensor($pk_sensor){
	$db = conectaBD();
	$query = "SELECT fk_sensor_tipo FROM sensores WHERE pk_sensor = ".$pk_sensor;
	$result = mysql_query($query);
	if($result)$row = mysql_fetch_array($result);
	else return 0;
	return $row['fk_sensor_tipo'];
}

function intervalo_linhas_y($tipo){
	switch($tipo){
		case 1: $intervalo = 10;
				break;
		case 4: $intervalo = 5;
				break;							
		case 9: $intervalo = 5;
				break;					
		default:$intervalo = 1;
	}
	return $intervalo;
}
