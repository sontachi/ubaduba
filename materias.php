<?php include "header.php";?>

<script>
	function verificaFormMateria(cad){ // validar
		with(document.formMateria){ // with
			if(nome.value == ''){alert('O preenchimento do nome é obrigatório !');nome.focus();return false;}
		  	if(creditos.value == ''){alert('O preenchimento do número de créditos é obrigatório !');creditos.focus();return false;}	
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
		$creditos = $_POST['creditos'];
		$curso = $_POST['curso'];

		if($_POST['oque'] == "novo"){
			$db = conectaBD();
			$query = "INSERT INTO materias (fk_curso, nome, creditos) VALUES ('$curso', '$nome', '$creditos')";
			$result = mysql_query($query);
			desconectaBD($db);
			if($result)$aviso_sucesso = "Matéria cadastrada com sucesso!";
			else $aviso_erro = "Houve um erro no cadastro, tente novamente!";
		}
		else{
			$id = $_POST['oque'];
			$db = conectaBD();
			$query = "UPDATE materias SET nome = '$nome', fk_curso = '$curso', creditos = '$creditos' WHERE pk_materia = '$id'";
			$result = mysql_query($query);
			desconectaBD($db);
			if($result)$aviso_sucesso = "Matéria atualizada com sucesso";
			else $aviso_erro = "Houve um erro na edicão, tente novamente!";
		}
	}

	//Se o ID for numérico, é para edição, portanto faz o select do materia
	if(is_numeric($_GET['id']))
	{
		$db = conectaBD();
		$query="SELECT * FROM `materias` WHERE `pk_materia` = '".$_GET['id']."' limit 1";
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
				echo '<h3>Cadastro de Matéria</h3>';
		}
		else if(is_numeric($_GET['id']))
		{
				echo '<h3>Edição de Matéria</h3>';
		}
	?>
		<br />
				
		<form action="materias.php" method="post" id="formMateria"  name="formMateria" class="form-horizontal" onSubmit="return verificaFormMateria(this);">
				
			<input type="hidden" name="oque" value="<?php echo $_GET['id']; ?>" />

			<div class="row">
				<div class="span6">
					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><h4>Informações</h4></div>
					</div>
		
					 <div class="control-group">
						<label class ="control-label" >Nome:</label>
						<div class="controls"><input class="input-xlarge" type="text" id="nome" name="nome" value="<?php echo $row['nome']; ?>" maxlength="100" required/></div>
					</div>
					
					<div class="control-group">
						<label class ="control-label">Créditos:</label>
						<div class="controls"><input class="input-small" type="number" name="creditos" value="<?php echo $row['creditos']; ?>"/></div>
					</div>

					<?php
					$db = conectaBD();
					$query = "SELECT * FROM cursos";
					$result = mysql_query($query);
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

			</div> <!-- row -->
			</form>

			<?php
	}else
	{ ?>

			<h2>Matérias</h2>

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
						    <a href="materias.php?id=novo">Cadastrar novo &rarr;</a>
						  </li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<table class="table table-hover">
				<thead>
					<tr>
						<th>Nome</th>
						<th>Curso</th>
						<th>Créditos</th>
						<?php
							if($_SESSION['permissao'] == 10)
								echo '<th></th>';
						?>
					</tr>
				</thead>
				<tbody>
				<?php
				$db = conectaBD();
				$query="SELECT m.pk_materia, m.nome, m.creditos, c.nome nome_curso FROM materias m INNER JOIN cursos c ON m.fk_curso = c.pk_curso WHERE 1 order by c.nome, m.nome";
				$result = mysql_query($query);
				desconectaBD($db);
				while($row = mysql_fetch_array($result)) {

				echo '
						<tr class="itens_tabela" >
						<td onclick="location.href = \'materia.php?id='.$row['pk_materia'].'\';">' . $row['nome'] . '</td>
						<td onclick="location.href = \'materia.php?id='.$row['pk_materia'].'\';">' . $row['nome_curso'] . '</td>
						<td onclick="location.href = \'materia.php?id='.$row['pk_materia'].'\';">' . $row['creditos'] . '</td>
						';
						if($_SESSION['permissao'] == 10)
							echo '<td><a href="materias.php?id=' . $row['pk_materia'] . '"><i class = "icon-pencil"></i></a></td>
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

