<?php include "header.php";?>

<script>
	function verificaFormAluno(cad){ // validar
		with(document.formAluno){ // with
			if(ra.value == ''){alert('O preenchimento do ra é obrigatório !');ra.focus();return false;}
			if(nome.value == ''){alert('O preenchimento do nome é obrigatório !');nome.focus();return false;}
		  	if(email.value == ''){alert('O preenchimento do e-mail é obrigatório !');email.focus();return false;}	
		  	if(curso.value == '0'){alert('O preenchimento do curso é obrigatório !');curso.focus();return false;}	
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
		$curso = $_POST['curso'];
		$fk_tag = $_POST['tag'];

		if($_POST['senha'] == "")$senha = "";
		else $senha = md5($_POST['senha']);

		if($_POST['oque'] == "novo"){
			$ra = $_POST['ra'];

			$db = conectaBD();
			$query = "SELECT ra FROM alunos WHERE ra = '$ra'";
			$result = mysql_query($query);

			//Se houver aluno com esse RA, não faz o cadastro
			if(mysql_num_rows($result) > 0){
				desconectaBD($db);
				echo "<script>alert('Este RA já esta cadastrado');</script>";
			}
			else{
				$query = "INSERT INTO alunos (ra, fk_curso, nome, sexo, email, fk_tag) VALUES ('$ra', '$curso', '$nome', '$sexo', '$email', '$fk_tag')";
				$result = mysql_query($query);
				if($result){
					$query = "INSERT INTO logins (usuario, senha, permissao) VALUES ('$ra', '$senha', '1')";
					$result = mysql_query($query);
					$aviso_sucesso = "Aluno cadastrado com sucesso!";
				}
				else $aviso_erro = "Houve um erro no cadastro, tente novamente!";
				desconectaBD($db);
			}
		}
		else{
			$id = $_POST['oque'];
			$db = conectaBD();
			$query = "UPDATE alunos SET fk_tag = '$fk_tag',nome = '$nome', email = '$email', sexo = '$sexo', fk_curso = '$curso' WHERE ra = '$id'";
			$result = mysql_query($query);
			if($senha != ""){
				$query = "UPDATE logins SET senha = '$senha' WHERE usuario = '$id' AND permissao = 1";
				$result2 = mysql_query($query);
			}

			desconectaBD($db);
			if($result)$aviso_sucesso = "Aluno atualizado com sucesso";
			else $aviso_erro = "Houve um erro na edicão, tente novamente!";
		}
	}

	//Se o ID for numérico, é para edição, portanto faz o select do aluno
	if(is_numeric($_GET['id']))
	{
		$db = conectaBD();
		$query="SELECT * FROM `alunos` WHERE `ra` = '".$_GET['id']."' limit 1";
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
	if($_GET['id'] != "")
	{

		if($_GET['id'] == "novo")
		{
				echo '<h3>Cadastro de Aluno</h3>';
		}
		else if(is_numeric($_GET['id']))
		{
				echo '<h3>Edição de Aluno</h3>';
		}
	?>
		<br />
				
		<form action="alunos.php" method="post" id="formAluno"  name="formAluno" class="form-horizontal" onSubmit="return verificaFormAluno(this);">
				
			<input type="hidden" name="oque" value="<?php echo $_GET['id']; ?>" />

			<div class="row">
				<div class="span6">
					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><h4>Informações Pessoais</h4></div>
					</div>
					 <div class="control-group">
						<label class ="control-label" >RA:</label>
						<div class="controls"><input class="input-small" type="text" id="ra" name="ra" value="<?php echo $row['ra']; ?>" maxlength="8" <?php if($row['ra'] != "")echo "disabled"; ?> required/></div>
					</div>

					 <div class="control-group">
						<label class ="control-label" >Senha:</label>
						<div class="controls"><input class="input-xlarge" type="text" id="senha" name="senha" value="" maxlength="100" required/></div>
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

					<?php
					$db = conectaBD();
					$query = "SELECT * FROM tags WHERE pk_tag NOT IN (SELECT fk_tag FROM alunos UNION select fk_tag FROM professores) OR pk_tag = (SELECT fk_tag FROM alunos WHERE ra = '".$_GET['id']."')";
					$result = mysql_query($query);
					desconectaBD($db);
					?>
					<div class="control-group">
						<label class ="control-label">Tags:</label>
						<div class="controls">
							<select name="tag" class="input-xlarge">
									<option value="0">Selecione uma tag</option>
								<?php
								while($tag = mysql_fetch_array($result)){ ?>
							    	<option value="<?php echo $tag['pk_tag']?>" <?php if($tag['pk_tag'] == $row['fk_tag']) echo 'selected'; ?>>Tag <?php echo $tag['pk_tag']." [".$tag['p35']."-".$tag['p36']."-".$tag['p37']."-".$tag['p38']."-".$tag['p39']."-".$tag['p40']."-".$tag['p41']."-".$tag['p42']."-".$tag['p43']."-".$tag['p44']."]";?></option>
							    	<?php
							    } ?>
							</select>
						</div>
					</div>


					<?php
					$db = conectaBD();
					$query = "SELECT * FROM cursos";
					$result = mysql_query($query);
					desconectaBD($db);
					?>
					<div class="control-group">
						<label class ="control-label">Curso:</label>
						<div class="controls">
							<select name="curso">
									<option value="0">Selecione um Curso</option>
								<?php
								while($curso = mysql_fetch_array($result)){ ?>
							    	<option value="<?php echo $curso['pk_curso']?>" <?php if($curso['pk_curso'] == $row['fk_curso']) echo 'selected'; ?>><?php echo $curso['nome'];?></option>
							    	<?php
							    } ?>
							</select>
						</div>
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
				</div>

			</div> <!-- row -->
			</form>

			<?php
	}else
	{ ?>

			<h2>Alunos</h2>

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
						    <a href="alunos.php?id=novo">Cadastrar novo &rarr;</a>
						  </li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<table class="table table-hover">
				<thead>
					<tr>
						<th>RA</th>
						<th>Nome</th>
						<th>Curso</th>
						<th>Tag</th>
						<?php
							if($_SESSION['permissao'] == 10)
								echo '<th></th>';
						?>
					</tr>
				</thead>
				<tbody>
				<?php
				$db = conectaBD();
				$query="SELECT a.ra, a.nome, c.nome nome_curso, fk_tag FROM alunos a INNER JOIN cursos c ON a.fk_curso = c.pk_curso WHERE 1 order by c.nome, a.nome";
				$result = mysql_query($query);
				desconectaBD($db);
				while($row = mysql_fetch_array($result)) {
					if($row['fk_tag'] == 0)$tag = "SEM TAG";
					else $tag = "Tag ".$row['fk_tag'];

				echo '
						<tr class="itens_tabela">
						<td>' . $row['ra'] . '</td>
						<td>' . $row['nome'] . '</td>
						<td>' . $row['nome_curso'] . '</td>
						<td>' . $tag . '</td>
						';
						if($_SESSION['permissao'] == 10)
							echo '<td><a href="alunos.php?id=' . $row['ra'] . '"><i class = "icon-pencil"></i></a></td>
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

