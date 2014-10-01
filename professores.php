<?php include "header.php";?>

<script>
	function verificaFormProfessor(cad){ // validar
		with(document.formProfessor){ // with
			if(rp.value == ''){alert('O preenchimento do rp é obrigatório !');ra.focus();return false;}
			if(nome.value == ''){alert('O preenchimento do nome é obrigatório !');nome.focus();return false;}
		  	if(email.value == ''){alert('O preenchimento do e-mail é obrigatório !');email.focus();return false;}	
			return true;
		}
	} 
</script>

<?php
if($_SESSION['permissao'] > 0)
{
	if(isset($_POST['enviar']))
	{	
		$nome = $_POST['nome'];
		$sexo = $_POST['sexo'];
		$email = $_POST['email'];

		if($_POST['oque'] == "novo"){
			$rp = $_POST['rp'];
			$db = conectaBD();
			$query = "SELECT rp FROM professores WHERE rp = '$rp'";
			$result = mysql_query($query);

			//Se houver professor com esse RA, não faz o cadastro
			if(mysql_num_rows($result) > 0){
				desconectaBD($db);
				echo "<script>alert('Este RP já esta cadastrado');</script>";
			}
			else{
				$query = "INSERT INTO professores (rp, nome, sexo, email ) VALUES ('$rp', '$nome', '$sexo', '$email')";
				$result = mysql_query($query);
				desconectaBD($db);
				if($result)$aviso_sucesso = "Professor cadastrado com sucesso!";
				else $aviso_erro = "Houve um erro no cadastro, tente novamente!";
			}
		}
		else{
			$id = $_POST['oque'];
			$db = conectaBD();
			$query = "UPDATE professores SET nome = '$nome', email = '$email', sexo = '$sexo' WHERE rp = '$id'";
			$result = mysql_query($query);
			desconectaBD($db);
			if($result)$aviso_sucesso = "Professor atualizado com sucesso";
			else $aviso_erro = "Houve um erro na edicão, tente novamente!";
		}
	}

	//Se o ID for numérico, é para edição, portanto faz o select do professor
	if(isset($_GET['id']) && $_GET['id'] != "novo")
	{
		$db = conectaBD();
		$query="SELECT * FROM `professores` WHERE `rp` = '".$_GET['id']."' limit 1";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		desconectaBD($db);
	}

?>
	
	<?php if(isset($aviso_sucesso) && $aviso_sucesso != "")
		echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_sucesso . '</div>';
	?>

	<?php if(isset($aviso_erro) && $aviso_erro != "")
		echo '<div class="alert"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_erro . '</div>';
	?>

			
					
	<?php 	
	if(isset($_GET['id']))
	{

		if($_GET['id'] == "novo")
		{
				echo '<h3>Cadastro de Professor</h3>';
		}
		else
		{
				echo '<h3>Edição de Professor</h3>';
		}
	?>
		<br />
				
		<form action="professores.php" method="post" id="formProfessor"  name="formProfessor" class="form-horizontal" onSubmit="return verificaFormProfessor(this);">
				
			<input type="hidden" name="oque" value="<?php echo $_GET['id']; ?>" />

			<div class="row">
				<div class="span6">
					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><h4>Informações Pessoais</h4></div>
					</div>
					 <div class="control-group">
						<label class ="control-label" >RP:</label>
						<div class="controls"><input class="input-small" type="text" id="rp" name="rp" value="<?php echo $row['rp']; ?>" maxlength="8" <?php if($row['rp'] != "")echo "disabled"; ?> required/></div>
					</div>

					 <div class="control-group">
						<label class ="control-label" >Nome:</label>
						<div class="controls"><input class="input-xlarge" type="text" id="nome" name="nome" value="<?php echo $row['nome']; ?>" maxlength="100" required/></div>
					</div>
					<div class="control-group">
						<label class ="control-label">Sexo:</label>
						<div class="controls">
							<select name="sexo">
							    <option value="M" <?php if($row['sexo'] == "M") echo 'selected'; ?>>Masculino</option>
							    <option value="F" <?php if($row['sexo'] == "F") echo 'selected'; ?>>Feminino</option>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class ="control-label">E-mail:</label>
						<div class="controls"><input class="input-xlarge" type="text" name="email" value="<?php echo $row['email']; ?>" maxlength="100"/></div>
					</div>

					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"></div>
					</div>


					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><input class="btn btn-large btn-primary" type="submit" name="enviar" value="Enviar" /></div>
					</div>
				</div> <!-- span6-->

			</div> <!-- row -->
			</form>

			<?php
	}else
	{ ?>

			<h2>Professores</h2>

			<div class="row">
				<div class="span6">

					<form class="navbar-search pull-left">
					  <input type="text" class="search-query" placeholder="Search" onkeyup="BuscaItemTabela(this);">
					</form>

				</div>
				<div class="span6">
					<?php if($_SESSION['permissao'] == 10){?>
						<ul class="pager">
						  <li class="next">
						    <a href="professores.php?id=novo">Cadastrar novo &rarr;</a>
						  </li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<table class="table table-hover">
				<thead>
					<tr>
						<th>RP</th>
						<th>Nome</th>
						<?php
							if($_SESSION['permissao'] == 10)
								echo '<th></th>';
						?>
					</tr>
				</thead>
				<tbody>
				<?php
				$db = conectaBD();
				$query="SELECT rp, nome FROM professores WHERE 1 order by nome";
				$result = mysql_query($query);
				desconectaBD($db);
				while($row = mysql_fetch_array($result)) {

				echo '
						<tr class="itens_tabela">
						<td>' . $row['rp'] . '</td>
						<td>' . $row['nome'] . '</td>
						';
						if($_SESSION['permissao'] == 10)
							echo '<td><a href="professores.php?id=' . $row['rp'] . '"><i class = "icon-pencil"></i></a></td>
					</tr>
					';
				}
				?>
				</tbody>
			</table>

    <?php 
    }
}
	include "footer.php";
?>

