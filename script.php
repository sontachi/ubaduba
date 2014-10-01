<?php include "header.php";


	// if(isset($_GET['tags']) && is_numeric($_GET['tags']))
	// {

	// 	$db = conectaBD();

	// 	for(;$a >= 0; $a--)
	// 	{
	// 		$b = $_GET['tags']/100;
	// 		$c = $_GET['tags']/100;
	// 		for(;$b >= 0; $b--)
	// 		{
	// 			$c = $_GET['tags']/100;
	// 			for(;$c >= 0; $c--)
	// 			{
	// 				$query = "INSERT INTO tags (pk_tag, p35,p36,p37,p38,p39,p40,p41,p42,p43,p44)
	// 							VALUES (NULL, $a, $b, $c, $d, $e, $f, $g, $h, $m, $n)";
	// 				$result = mysql_query($query);
	// 			}
	// 		}
	// 	}

	// 	desconectaBD($db);
	// }

	if(isset($_GET['aluno']))
	{

		$db = conectaBD();
		$j = 993966;

		for($i = 20000; $i < 20500 ; $i++)
		{
			$j++;


			$query = "INSERT INTO professores (rp, nome, sexo, email, fk_tag) VALUES ('$i', 'PROF $i', 'M', '88', '0')";
			$result = mysql_query($query);
			
			$query = "INSERT INTO logins (usuario, senha, permissao) VALUES ('$i', '2a38a4a9316c49e5a833517c45d31070', '2')";
			$result = mysql_query($query);
			
		}

		desconectaBD($db);
	}
	include "footer.php";
?>

