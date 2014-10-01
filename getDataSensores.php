<?php
    include "config.php";

    $db=conectaBD(); 

    $query = "SELECT
                    sen.pk_sensor, tipos.pk_sensor_tipo,
                    cs.valor_lido, DATE_FORMAT(cs.data, '%d/%m/%Y às %H:%i:%s') as data
                FROM 
                    sensores sen 
                INNER JOIN 
                    sensores_tipos tipos
                ON 
                    sen.fk_sensor_tipo = tipos.pk_sensor_tipo AND sen.excluido = 0
                INNER JOIN 
                    consultas_sensor cs
                ON
                    cs.fk_sensor = sen.pk_sensor
                INNER JOIN
                    salas
                ON
                    sen.fk_sala = pk_sala
                WHERE 
                    sen.excluido = 0
                ORDER BY
                    salas.nome, pk_sensor";

    
    $result = mysql_query($query);
    desconectaBD($db);
    $i = 0;
    while($row = mysql_fetch_array($result)){
        $a[$i][0]=$row['pk_sensor'];
        $a[$i][1]=$row['valor_lido'];
        $a[$i][2]=$row['data'];
        $a[$i][3]=unidade_sensor($row['pk_sensor_tipo']);
        $i++;
    }

    $array['data'] = $a; 
    print json_encode($array); 
?> 
