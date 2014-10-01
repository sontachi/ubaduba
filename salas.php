<?php include "header.php";?>

<script>
	function verificaFormSala(cad){ // validar
		with(document.formSala){ // with
			if(nome.value == ''){alert('O preenchimento do nome da sala é obrigatório !');nome.focus();return false;}
			if(id_leitor.value == ''){alert('O preenchimento do id do leitor é obrigatório !');id_leitor.focus();return false;}
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
		$id_leitor = $_POST['id_leitor'];

		if($_POST['oque'] == "novo"){
			$db = conectaBD();
			$query = "SELECT nome FROM salas WHERE nome = '$nome'";
			$result = mysql_query($query);

			//Se houver sala com esse RA, não faz o cadastro
			if(mysql_num_rows($result) > 0){
				desconectaBD($db);
				echo "<script>alert('Esta sala já esta cadastrada');</script>";
			}
			else{
				$query = "INSERT INTO salas (nome, id_leitor) VALUES ('$nome', $id_leitor)";
				$result = mysql_query($query);
				desconectaBD($db);
				if($result)$aviso_sucesso = "Sala cadastrada com sucesso!";
				else $aviso_erro = "Houve um erro no cadastro, tente novamente!";
			}
		}
		else{
			$id = $_POST['oque'];
			$db = conectaBD();
			$query = "UPDATE salas SET nome = '$nome', id_leitor = $id_leitor WHERE pk_sala = '$id'";
			$result = mysql_query($query);
			desconectaBD($db);
			if($result)$aviso_sucesso = "Sala atualizado com sucesso";
			else $aviso_erro = "Houve um erro na edicão, tente novamente!";
		}
	}

	//Se o ID for numérico, é para edição, portanto faz o select do sala
	if(is_numeric($_GET['id']))
	{
		$db = conectaBD();
		$query="SELECT * FROM salas WHERE pk_sala = '".$_GET['id']."' limit 1";
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
				echo '<h3>Cadastro de Sala</h3>';
		}
		else if(is_numeric($_GET['id']))
		{
				echo '<h3>Edição de Sala</h3>';
		}
	?>
		<br />
				
		<form action="salas.php" method="post" id="formSala"  name="formSala" class="form-horizontal" onSubmit="return verificaFormSala(this);">
				
			<input type="hidden" name="oque" value="<?php echo $_GET['id']; ?>" />

			<div class="row">
				<div class="span6">
					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><h4>Informações</h4></div>
					</div>

					 <div class="control-group">
						<label class ="control-label" >Nome:</label>
						<div class="controls"><input class="input-xlarge" type="text" id="nome" name="nome" value="<?php echo $row['nome']; ?>" maxlength="50" required/></div>
					</div>

					 <div class="control-group">
						<label class ="control-label" >ID Leitor:</label>
						<div class="controls"><input class="input-xlarge" type="number" id="id_leitor" name="id_leitor" value="<?php echo $row['id_leitor']; ?>" maxlength="5" required/></div>
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

			<h2>Salas</h2>

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
						    <a href="salas.php?id=novo">Cadastrar novo &rarr;</a>
						  </li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<table class="table table-hover">
				<thead>
					<tr>
						<th>Sala</th>
						<th>Leitor</th>
						<?php
							if($_SESSION['permissao'] == 10)
								echo '<th></th>';
						?>
					</tr>
				</thead>
				<tbody>
				<?php
				$db = conectaBD();
				$query="SELECT pk_sala, nome, id_leitor FROM salas  WHERE 1 order by nome";
				$result = mysql_query($query);
				desconectaBD($db);
				while($row = mysql_fetch_array($result)) {

				echo '
						<tr class="itens_tabela">
						<td>' . $row['nome'] . '</td>
						<td>' . $row['id_leitor'] . '</td>
						';
						if($_SESSION['permissao'] == 10)
							echo '<td><a href="salas.php?id=' . $row['pk_sala'] . '"><i class = "icon-pencil"></i></a></td>
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

