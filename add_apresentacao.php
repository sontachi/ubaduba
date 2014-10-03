<?php 

include "header.php";

if($_SESSION['permissao'] > 0)
{

	if(isset($_POST['enviar'])) {
		
		$pk_atividade=$_POST['atividade'];
		$descricao=$_POST['descricao'];

		$arquivo = uploadArquivo($_FILES["apresentacao"], "downloads", 1000, 1000);
		
	        if($arquivo!=-1 && $arquivo!=NULL && $pk_atividade>0) {
	            $query = "INSERT INTO apresentacoes (pk_apresentacao, fk_atividade, descricao, endereco, data_envio, status) VALUES ( NULL, '".$pk_atividade."','".$descricao."','".$arquivo."', '".date('Y-m-d H:i:s')."', 1)";
				$db=conectaBD();
				$result=mysql_query($query);
				desconectaBD($db);
				
				if($result)
					echo "<script type=\"text/javascript\" language=\"javascript\"> alert('Apresentação salva com sucesso.') </script>";
			}
			else
			    echo "<script type=\"text/javascript\" language=\"javascript\"> alert('O arquivo não foi carregado.')</script>";
	}

	$query="SELECT aco.fk_atividade, ati.titulo, amb.nome FROM acontece aco
			INNER JOIN atividades as ati ON ati.pk_atividade = aco.fk_atividade
			INNER JOIN ambientes amb ON amb.pk_ambiente = ati.fk_ambiente
			WHERE data_inicio < ADDTIME('".date('Y-m-d H:i:s')."', '00:20:00')";

	$db=conectaBD();
	$result=mysql_query($query);
	desconectaBD($db);

?>
					
			<h3>Disponibilizar material de palestra no site </h3>
			<form action = "" method="post" enctype="multipart/form-data" class="form" name="contato">
				<p>
					<label class ="control-label">Arquivo</label>
					<input type="file" name="apresentacao" />
				</p>
				<p>
					<label class ="control-label">Descrição do material</label>
					<input class="input-xxlarge" type="text" id="descricao" name="descricao" value="Apresentação" size="50" />
				</p>
	            <p>
                <label class ="control-label">Atividade</label>
				<select name="atividade">
                <?php while ($row = mysql_fetch_array($result))
				 {
                 echo "<option value='".$row['fk_atividade']."'>".$row['fk_atividade']." - ".$row['nome']." - ".$row['titulo']."</option>";
				 }
				  ?>
                </select>	
				</p>
                
                <p>
					<label class ="control-label"></label>
					<input clas="btn btn-primary" type="submit" name="enviar" value="Enviar" />
				</p>
              
			</form>
            
	<?php
	}
	include "footer.php";
	?>

