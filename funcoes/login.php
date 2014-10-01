<?php
function login($usuario, $senha){
	$senha = md5($senha);
	$db = conectaBD();
	$query = "SELECT permissao FROM logins WHERE usuario = '".$usuario."' AND senha = '".$senha."'";
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_array($result);
		$permissao = $row['permissao'];

		switch ($permissao) {
			case '1':
				$query = "SELECT nome FROM alunos WHERE ra = '$usuario'";
				break;
			case '2':
				$query = "SELECT nome FROM professores WHERE rp = '$usuario'";
				break;
		}

		if($permissao != 10){
			$result = mysql_query($query);
			$row2 = mysql_fetch_array($result);
			$nome = $row2['nome'];
		}
		else{
			$nome = "Administrador";
		}
			

		$query = "INSERT INTO log_acesso (pk_acesso, ip, navegador, usuario, permissao, status)
		VALUES (NULL,'".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."', '$usuario', '$permissao', 1);";
		$result = mysql_query($query);

		$_SESSION['usuario'] = $usuario;
		$_SESSION['permissao'] = $permissao;
		$_SESSION['nome'] = $nome;
		$_SESSION['welcome'] = 0;
		return true;
	}
	else{
		return false;
	}
}

function logout(){
	unset($_SESSION['usuario']);
	unset($_SESSION['permissao']);
	unset($_SESSION['nome']);
	unset($_SESSION['welcome']);
	echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./'>";
}