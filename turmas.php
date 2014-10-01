<?php include "header.php";?>


<script>
	function verificaFormTurma(cad){ // validar
		with(document.formTurma){ // with	
			
			var dataInicio = (inicio.value).split("/");  
			dataInicio = new Date(dataInicio[2], dataInicio[1]-1, dataInicio[0]);  
			
			var dataFim = (fim.value).split("/");  
			dataFim = new Date(dataFim[2], dataFim[1]-1, dataFim[0]);  

			if(materia.value == '0'){alert('O preenchimento da materia é obrigatório!');materia.focus();return false;}
		  	if(professor.value == '0'){alert('O preenchimento do nome do professor é obrigatório!');professor.focus();return false;}
		  	if(curso.value == '0'){alert('O preenchimento do curso é obrigatório!');curso.focus();return false;}	
		  	if(numero.value == ''){alert('O preenchimento do número da turma é obrigatório!');numero.focus();return false;}	
		  	if(inicio.value == ''){alert('O preenchimento da data de início é obrigatório!');inicio.focus();return false;}
		  	if(fim.value == ''){alert('O preenchimento da data de término é obrigatório!');fim.focus();return false;}
		  	if(dataFim < dataInicio){alert('A data de inicio do semestre deve ser menor que a data de fim do semestre');return false;}
			return true;
		}
	} 
</script>
<?php
if($_SESSION['permissao'] != 5 && $_SESSION['permissao'] > 0)
{
	if(isset($_POST['enviar']))
	{	
		$numero = $_POST['numero'];
		$curso = $_POST['curso'];
		$professor = $_POST['professor'];
		$materia = $_POST['materia'];
		$inicio = $_POST['inicio'];
		$fim = $_POST['fim'];

			if($_POST['oque'] == "novo"){
				$db = conectaBD();
				$query = "INSERT INTO turmas (fk_materia, fk_curso, rp, numero, data_inicio, data_fim) VALUES ('$materia', '$curso', '$professor', '$numero', STR_TO_DATE('$inicio', '%d/%m/%Y'),  STR_TO_DATE ('$fim', '%d/%m/%Y'))";
				$result = mysql_query($query);
				desconectaBD($db);
				if($result)$aviso_sucesso = "Turma cadastrada com sucesso!";
				else $aviso_erro = "Houve um erro no cadastro, tente novamente!";
			}
			else{
			$id = $_POST['oque'];
			$db = conectaBD();
			$query = "UPDATE turmas SET rp = '$professor', fk_curso = '$curso', fk_materia = '$materia', data_inicio = STR_TO_DATE('$inicio', '%d/%m/%Y'), data_fim = STR_TO_DATE('$fim', '%d/%m/%Y')  WHERE pk_turma = '$id'";
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
		$query="SELECT *, DATE_FORMAT(data_inicio, '%d/%m/%Y') as data_inicio, DATE_FORMAT(data_fim, '%d/%m/%Y') as data_fim FROM `turmas` WHERE `pk_turma` = '".$_GET['id']."' limit 1";
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
					echo '<h3>Cadastro de Turma</h3>';
			}
			else if(is_numeric($_GET['id']))
			{
					echo '<h3>Edição de Turma</h3>';
			}
		?>

	<br />
				
		<form action="turmas.php" method="post" id="formTurma"  name="formTurma" class="form-horizontal" onSubmit="return verificaFormTurma(this);">
				
			<input type="hidden" name="oque" value="<?php echo $_GET['id']; ?>" />

			<div class="row">
				<div class="span6">
					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><h4>Informações</h4></div>
					</div>
		
					 <div class="control-group">
						<label class ="control-label" >Número:</label>
						<div class="controls"><input class="input-xlarge" type="text" id="numero" name="numero" value="<?php echo $row['numero']; ?>" maxlength="100" required/></div>
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
							<select name="curso" id="curso">
									<option value="0">Selecione um Curso</option>
								<?php
								while($curso = mysql_fetch_array($result)){ ?>
							    	<option value="<?php echo $curso['pk_curso']?>" <?php if($curso['pk_curso'] == $row['fk_curso']) echo 'selected'; ?>><?php echo $curso['nome'];?></option>
							    	<?php
							    } ?>
							</select>
						</div>
					</div>

					<?php
					$db = conectaBD();
					$query = "SELECT * FROM materias";
					$result = mysql_query($query);
					desconectaBD($db);
					?>
					<div class="control-group">
						<label class ="control-label">Materia:</label>
						<div class="controls">
							<select name="materia" id="materia">
									<option value="0">Selecione uma Materia</option>
								<?php
								while($materia = mysql_fetch_array($result)){ ?>
							    	<option value="<?php echo $materia['pk_materia']?>" <?php if($materia['pk_materia'] == $row['fk_materia']) echo 'selected'; ?>><?php echo $materia['nome'];?></option>
							    	<?php
							    } ?>
							</select>
						</div>
					</div>

					<?php
					$db = conectaBD();
					$query = "SELECT * FROM professores";
					$result = mysql_query($query);
					desconectaBD($db);
					?>
					<div class="control-group">
						<label class ="control-label">Professor:</label>
						<div class="controls">
							<select name="professor" id="professor">
									<option value="0">Selecione um Professor</option>
								<?php
								while($professor = mysql_fetch_array($result)){ ?>
							    	<option value="<?php echo $professor['rp']?>" <?php if($professor['rp'] == $row['rp']) echo 'selected'; ?>><?php echo $professor['nome'];?></option>
							    	<?php
							    } ?>
							</select>
						</div>
					</div>

					 <div class="control-group">
						   <label class ="control-label">Início Semestre:</label>
						   <div class="controls">
						     <div id="datetimepicker" class="input-append date" data-date-minviewmode="days">
						         <input class="input-medium" name="inicio" type="text" id="inicio" value="<?php if($row['data_inicio']!='')echo $row['data_inicio']; else echo date("d/m/Y"); ?>" readonly></input>  
						         <span class="add-on">
						           <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
						         </span>
						       </div>
						   </div>
						 </div>
						 <div class="control-group">
						   <label class ="control-label">Fim Semestre:</label>
						   <div class="controls">
						     <div id="datetimepicker2" class="input-append date" data-date-minviewmode="days">
						         <input class="input-medium" name="fim" type="text" id="fim" value="<?php if($row['data_fim']!='')echo $row['data_fim']; else echo date("d/m/Y",strtotime(date("Y-m-d")." +4 month")); ?>"readonly></input>  
						         <span class="add-on">
						           <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
						         </span>
						       </div>
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

			}

			else{ 
			?>

			<h2>Turmas</h2>

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
						    <a href="turmas.php?id=novo">Cadastrar novo &rarr;</a>
						  </li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<table class="table table-hover">
				<thead>
					<tr>
						<?php
							if($_SESSION['permissao'] > 1){
								echo '<th>Número</th>';
								echo '<th>Curso</th>';
							}
						?>
						<th>Matéria</th>
						<th>Professor</th>
						<?php
							if($_SESSION['permissao'] == 10)
								echo '<th></th>';
							else if($_SESSION['permissao'] == 1)
								echo '<th>Presença</th>';
						?>
					</tr>
				</thead>
				<tbody>
				<?php
				$db = conectaBD();

				$usuario = $_SESSION['usuario'];

				if($_SESSION['permissao']==2){
					$complemento = "p.rp = $usuario";
				}
				else if($_SESSION['permissao']==10){
					$complemento = "1";
				}
				else{
					$complemento = "t.pk_turma IN (SELECT fk_turma FROM rel_turmas_alunos WHERE ra = $usuario)";
				}	

				$query="SELECT t.pk_turma, c.nome curso, m.nome materia, p.nome professor, t.numero FROM turmas t 
				INNER JOIN cursos c ON t.fk_curso = c.pk_curso 
				INNER JOIN materias m ON t.fk_materia = m.pk_materia 
				INNER JOIN professores p ON t.rp = p.rp  WHERE $complemento";

				$result = mysql_query($query);
				desconectaBD($db);
				while($row = mysql_fetch_array($result)) {

					$db = conectaBD();
					$query = "SELECT (SELECT count(*) FROM horarios WHERE horarios.flag = 3 AND fk_turma = '".$row['pk_turma']."') as total, (SELECT count(*) FROM horarios INNER JOIN presentes ON fk_horario = pk_horario AND ra = '".$_SESSION['usuario']."' AND presentes.flag = 1 WHERE horarios.flag = 3 AND fk_turma = '".$row['pk_turma']."') as presentes";
					$result2 = mysql_query($query);
					$rowAula = mysql_fetch_array($result2);
					desconectaBD($db);


					echo '
							<tr class="itens_tabela" >';

						if($_SESSION['permissao'] > 1){
							echo '<td onclick="location.href = \'turma.php?id='.$row['pk_turma'].'\';">' . $row['numero'] . '</td>
							      <td onclick="location.href = \'turma.php?id='.$row['pk_turma'].'\';">' . $row['curso'] . '</td>';
							  }
						echo '
							<td onclick="location.href = \'turma.php?id='.$row['pk_turma'].'\';">' . $row['materia'] . '</td>
							<td onclick="location.href = \'turma.php?id='.$row['pk_turma'].'\';">' . $row['professor'] . '</td>
							';
							if($_SESSION['permissao'] == 10)
								echo '<td><a href="turmas.php?id=' . $row['pk_turma'] . '"><i class = "icon-pencil"></i></a></td>';
							if($_SESSION['permissao'] == 1)
								echo '<td onclick="location.href = \'turma.php?id='.$row['pk_turma'].'\';">' . round($rowAula['presentes']*100/$rowAula['total'],2) . '% </td>';
							echo '
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