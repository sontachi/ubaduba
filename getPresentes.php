<?php
    include "config.php";

    $id = $_GET['id'];
    $horario = $_GET['horario'];

    $db = conectaBD();

    $query="SELECT a.ra, p.flag as flag, h.flag as aula, CASE WHEN date_add(h.data_inicio_real, interval 1 minute) > NOW() AND h.flag = 1 THEN '1' WHEN date_add(h.data_inicio_real, interval 1 minute) > NOW() AND h.flag = 1 THEN '2' ELSE '0' END AS pode_entrar
            FROM alunos a 
            INNER JOIN rel_turmas_alunos r ON a.ra = r.ra AND r.fk_turma = $id
            INNER JOIN horarios h ON r.fk_turma = h.fk_turma AND h.pk_horario = $horario
            LEFT JOIN presentes p ON a.ra = p.ra AND p.fk_horario = h.pk_horario
            ORDER BY a.nome";

    
    $result = mysql_query($query);
    desconectaBD($db);
    $i = 0;
    while($row = mysql_fetch_array($result)){
        if($row['flag'] == NULL)$flag = "-1";
        else if($row['flag'] == 0)$flag = "0";
        else $flag = "1";

        $a[$i][0]=$row['ra'];
        $a[$i][1]=$flag;
        $a[$i][2]=$row['aula'];
        $a[$i][3]=$row['pode_entrar'];

        $i++;
    }

    $array['data'] = $a; 
    print json_encode($array); 
?> 
