<?php
function conectaBD() {

    $db_host='localhost'; //O nome do servidor
    $db_user='root'; //O nome do utilizador de MySQL
    $db_password='root'; //A senha do utilizador
    $db_name='sistema'; //O nome da base de dados

        
    $db=mysql_connect($db_host, $db_user, $db_password);
    mysql_select_db($db_name, $db);
    mysql_query('SET character_set_connection=utf8');
    mysql_query('SET character_set_client=utf8');
    mysql_query('SET character_set_results=utf8');

    return $db;
}

function desconectaBD($db) {
    mysql_close($db);
}

?>
