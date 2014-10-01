<?php
function login($usuario, $senha){
	$senha = md5($senha);
	$db = conectaBD();
	$query = "SELECT * FROM admins WHERE usuario = '".$usuario."' AND senha = '".$senha."'";
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_array($result);

		$query = "INSERT INTO log_acesso (pk_acesso, ip, navegador, fk_admin, status)
		VALUES (NULL,'".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."', '".$row['pk_admin']."',  1);";
		$result = mysql_query($query);

		$_SESSION['admin'] = $row['pk_admin'];
		$_SESSION['permissao'] = $row['permissao'];
		$_SESSION['nome'] = $row['nome'];
		return true;
	}
	else{
		return false;
	}
}

function logout(){
	unset($_SESSION['pk_admin']);
	unset($_SESSION['permissao']);
	unset($_SESSION['nome']);
	echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./'>";
}