<?php include "header.php";?>
<script>
	function verificaFormArquivo(){
		with(document.formArquivo){
			if(arquivo.value == ''){alert('Nenhum arquivo selecionado !');nome.focus();return false;}
			return true;
		}
	} 
</script>

<?php
if($_SESSION['permissao'] == 1) //se for aluno
{

	if(isset($_POST['enviar']))
	{
	}
	if(isset($_POST['atualizar']))
	{
	}
	if(isset($_POST['remover']))
	{
	}	
}
?>
<?php if(isset($aviso_sucesso) && $aviso_sucesso != "")
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_sucesso . '</div>';
?>
<?php if(isset($aviso_erro) && $aviso_erro != "")
	echo '<div class="alert"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_erro . '</div>';
?>
	
	<form action="escaninho.php" method="post" id="form"  name="formArquivo" class="form-horizontal" onSubmit="return verificaFormArquivo(this);">
		<h3>Upload de Arquivo</h3>
		
		<div class="control-group">
			
			<label class ="control-label" >Arquivo:</label>
			<div class="controls"><input class="input-xlarge" type="text" id="arquivo" name="arquivo" value="<?php echo $row['numero']; ?>" maxlength="100" ></div>
			<div class="controls"><input class="btn btn-large btn-primary" type="submit" name="buscar" value="Selecionar Arquivo" /></div>
		</div>
		<br />
		<div class="control-group">
			<label class ="control-label"></label>
			<div class="controls"><input class="btn btn-large btn-primary" type="submit" name="enviar" value="Enviar" /></div>
		</div>
	</form>
	<br />

<table class="table table-hover">
	<thead>
		<tr>
			<th>Arquivo</th>
			<th>Data</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$ra = 123;
	$turma =1; 
	$db = conectaBD();
	$query="SELECT * from arquivos WHERE fk_turma=$turma and ra=$ra;";
	echo $query;
	$result = mysql_query($query);
	desconectaBD($db);
	while($row = mysql_fetch_array($result))
	{
		echo ' 
		<tr class="itens_tabela" >
			<td>' . $row['label'] . '</td>
			<td>' . $row['data'] . '</td>
		</tr>
			';
	}
	?>
	</tbody>
</table>
<?php
	include "footer.php";
?>




	