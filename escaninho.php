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
if($_SESSION['permissao'] =1)//aluno
{
	if(isset($_POST['enviar'])) {
		
		$arquivo = uploadArquivo($_FILES['material'], "escaninho");
		$label = explode("/", $arquivo)[1];
	        if($arquivo!=-1 && $arquivo!=NULL) {
	            $query = "INSERT INTO materiais (pk_material, fk_turma, `path`, label, data, excluido) VALUES ( NULL, '".$_GET['turma']."','$arquivo', '$label' ,, 0)";
				$db=conectaBD();
				$result=mysql_query($query);
				desconectaBD($db);
				
				if($result)
					$aviso_sucesso = 'Apresentação salva com sucesso.';
			}
			else
			    $aviso_erro = 'O arquivo não foi carregado.';
	}

	//Se o ID for numérico, é para edição, portanto faz o select do curso
	if(is_numeric($_GET['id']))
	{
		$db = conectaBD();
		$query="SELECT * FROM `cursos` WHERE `pk_curso` = '".$_GET['id']."' limit 1";
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
	if($_GET['id'] == "")//Exibi arquivo
	{

	?>
		<br />

		<ul class="pager">
						  <li class="next">
						    <a href="escaninho.php?id=novo">Enviar Arquivo &rarr;</a>
						  </li>
						</ul>
		<br />

		<table class="table table-hover">
		<thead>
			<tr>
				<th>Arquivo</th>
				<th>Data</th>
				<th>Operação<th>
			</tr>
		</thead>
		<tbody>
		<?php
		$ra = 123;
		$turma =1; 
		$db = conectaBD();
		$query="SELECT * from escaninho WHERE fk_turma=$turma and ra=$ra;";
		$result = mysql_query($query);
		desconectaBD($db);
		while($row = mysql_fetch_array($result))
		{
			echo ' 
			<tr class="itens_tabela" >
				<td>' . $row['label'] . '</td>
				<td>' . $row['data'] . '</td>
				<td> <a class="btn btn-small btn-primary" href="escaninho.php?id=' .$row['pk_arquivo'].'">Alterar</a></td>
			</tr>
				';
		}
		
		?>
		</tbody>
	</table>
	<?php
	}else
	{ ?>

			<h2>Escaninho</h2><br>
			<form action = "" method="post" enctype="multipart/form-data" class="form" name="contato">
				<p>
					<label class ="control-label">Arquivo</label>
					<input type="file" name="apresentacao" />
				</p>

                <p>
					<label class ="control-label"></label>
					<input clas="btn btn-primary" type="submit" name="enviar" value="Enviar" />
				</p>
              
			</form>
            

	

    <?php 
    }
}
	include "footer.php";
?>





