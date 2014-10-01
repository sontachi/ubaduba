<?php session_start();

/* Exibição dos Errors e Warnings para desenvolvimento
 */
ini_set('display_errors', 0);
ini_set('log_errors', 0);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);


/* Definição de GMT do Brasil
 */
date_default_timezone_set("Brazil/East");
setlocale(LC_TIME, 'pt_BR.utf8');
setlocale(LC_ALL, 'pt_BR');
ini_set('default_charset','UTF-8');
	
include "funcoes/db.php";
include "funcoes/funcoes.php";
include "funcoes/login.php";

?>