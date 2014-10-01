<?php 
include "header.php";

if($_SESSION['permissao'] != 5 && $_SESSION['permissao'] > 0)
{		
	?>
	<script>
		function verificaFormAluno(cad){ // validar
			with(document.formAluno){ // with
				if(ra.value == ''){alert('O preenchimento do ra é obrigatório !');ra.focus();return false;}
				if(senha.value == ''){alert('O preenchimento da senha é obrigatório !');senha.focus();return false;}	
				if(nome.value == ''){alert('O preenchimento do nome é obrigatório !');nome.focus();return false;}
			  	if(email.value == ''){alert('O preenchimento do e-mail é obrigatório !');email.focus();return false;}	
			  	if(curso.value == '0'){alert('O preenchimento do curso é obrigatório !');curso.focus();return false;}	
				return true;
			}
		}

		function verificaFormAddAula(cad){ // validar
			with(document.formAddAula){ // with
 				
 				for(var i = 0; i < dia.length; i++)
 				{
 					if(dia[i].checked == true)break;
 				}
 				if(i == 6){alert('Escolha pelo menos um dia da semana!');return false;}

				if(sala.value == '0'){alert('O preenchimento da sala é obrigatório !');sala.focus();return false;}
				
				if(parseInt(hora_inicio.value) > parseInt(hora_fim.value)){alert('A hora de inicio deve ser menor que a hora de fim!');hora_inicio.focus();return false;}	
				if(parseInt(hora_inicio.value) == parseInt(hora_fim.value))
				{
					if(parseInt(minuto_inicio.value) >= parseInt(minuto_fim.value)){alert('A hora de inicio deve ser menor que a hora de fim!');minuto_inicio.focus();return false;}	
				}

				return true;
			}
		} 
		
	</script>

	<?php

	if(isset($_POST['cadastrarAlunos']))
	{
		$turma = $_GET['id'];

		$db = conectaBD();

		$query = "DELETE from rel_turmas_alunos WHERE fk_turma = '$turma'";
		$result = mysql_query($query);

		foreach($_POST['incluirAluno'] as $ra)
	    {
	        $query = "INSERT INTO `rel_turmas_alunos` (`fk_turma`,`ra`) VALUES ('$turma', '$ra')";
	        $result=mysql_query($query);
	    }
	    desconectaBD($db);
	}

	if(isset($_POST['enviarNovaAula']))
	{	
		$turma = $_GET['id'];
		$diasSelecionados = $_POST['dia'];
		$sala = $_POST['sala'];

		$db = conectaBD();	
		$query = "SELECT data_inicio, data_fim, datediff(data_fim, data_inicio) as dias FROM turmas WHERE pk_turma = ".$_GET['id'];
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		$dataInicio = $row['data_inicio']." ". $_POST['hora_inicio'].":". $_POST['minuto_inicio'].":00";
		$dataFim = $row['data_inicio']." ". $_POST['hora_fim'].":". $_POST['minuto_fim'].":00";
	 	$numeroDias = $row['dias'];

	 	for($i=0; $i<=$numeroDias; $i++)
	 	{
	 	 	$diaAtual = date("w",strtotime($dataInicio." +$i day"));
	 	 	$dataAtualInicio = date("Y-m-d H:i:s",strtotime($dataInicio." +$i day"));
	 	 	$dataAtualFim = date("Y-m-d H:i:s",strtotime($dataFim." +$i day"));
	 	 	if (in_array($diaAtual, $diasSelecionados))
	 	 	{
	 	 			$query = "INSERT INTO horarios (fk_turma, fk_sala, data_inicio, data_fim) VALUES ('$turma', '$sala', '$dataAtualInicio', '$dataAtualFim')";
	 	 		$result = mysql_query($query);
	 	 	}

	 	}

		desconectaBD($db);

	}



	if(is_numeric($_GET['id']) && !isset($_GET['nova_aula']) && !isset($_GET['adicionar_alunos']))
	{
		$pk_turma = $_GET['id'];

		//Usado para deletar o horário
		if(isset($_GET['del']) && is_numeric($_GET['del']))
		{
			$aviso_erro = "Deseja realmente apagar este horario? <a href='turma.php?id=".$_GET['id']."&deletar=".$_GET['del']."'>Clique aqui</a> para confirmar";
		}
		if(isset($_GET['deletar']) && is_numeric($_GET['deletar']))
		{
			$db = conectaBD();
			$query = "DELETE FROM `horarios` WHERE pk_horario=".$_GET['deletar']." AND fk_turma = ".$_GET['id']."";
			$result = mysql_query($query);
			desconectaBD($db);
			$aviso_sucesso = "Aula cancelada com sucesso!";
		}

		//Para exibir os alertas de erro
		if(isset($aviso_erro) && $aviso_erro != "")
			echo '<div class="alert"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_erro . '</div>';
		if(isset($aviso_sucesso) && $aviso_sucesso != "")
			echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_sucesso . '</div>';
		
		//Para pegar a informação da turma
		$query = "SELECT
					t.numero, DATE_FORMAT(t.data_inicio, '%d/%m/%Y') inicio, DATE_FORMAT(t.data_fim, '%d/%m/%Y') fim,
					c.nome curso,
					m.nome materia, m.creditos
				  FROM
				  	turmas t
				  INNER JOIN
				  	cursos c
				  ON
				  	t.fk_curso = c.pk_curso
				  INNER JOIN
				  	materias m
				  ON
				  	t.fk_materia = m.pk_materia
				  WHERE
				  	pk_turma = '$pk_turma'
			";

		


		$db = conectaBD();
		$result = mysql_query($query);
		if($_SESSION['permissao'] == 1){
			$query2 = "SELECT (SELECT count(*) FROM horarios WHERE horarios.flag = 3 AND fk_turma = '".$_GET['id']."') as total, (SELECT count(*) FROM horarios INNER JOIN presentes ON fk_horario = pk_horario AND ra = '".$_SESSION['usuario']."' AND presentes.flag = 1 WHERE horarios.flag = 3 AND fk_turma = '".$_GET['id']."') as presentes";
			$result2=mysql_query($query2);
			$rowAula = mysql_fetch_array($result2);
			$presenca = round($rowAula['presentes']*100/$rowAula['total'],2)."%";
		}
		desconectaBD($db);


		if(mysql_num_rows($result) > 0)
		{
			$row = mysql_fetch_array($result);
			$num_turma = $row['numero'];
			$curso = $row['curso'];
			$materia = $row['materia'];
			$creditos = $row['creditos'];
			$inicio = $row['inicio'];
			$fim = $row['fim'];

			?>
			<ul class="pager">
			    <li class="previous">
			      <a href = "turmas.php?">&larr; Voltar</a>
			    </li>
			</ul>

				<div class="row">
					<div class="span12">
						<h3>Turma <?php echo $num_turma;?></h3>
						<div class="row">
							<div class="span4">
								<address>
								  	<b>Curso:</b> <?php echo $curso;?><br>
								  	<b>Matéria:</b> <?php echo $materia;?><br>
								  	<b>Créditos:</b> <?php echo $creditos;?><br>
								  	<b>Semestre:</b> <?php echo $inicio." a ".$fim;?><br>
								  	<?php
								  	if($_SESSION['permissao']==1){ ?>
								  		<br>
								  		<b>Presença:</b> <?php echo $presenca;?><br>
								  		<?php
								  	} ?>
								</address>		
							</div>
							<div class="span4">
								<?php
								if($_SESSION['permissao'] == 10){ ?>
									<a href="turma.php?id=<?php echo $pk_turma;?>&nova_aula" class="btn btn-medium btn-primary">Cadastrar Nova Aula</a>
									<?php
								} ?>
							</div>
							<div class="span4">
								<?php
								if($_SESSION['permissao'] == 10){ ?>
									<a href="turma.php?id=<?php echo $pk_turma;?>&adicionar_alunos" class="btn btn-medium btn-primary">Adicionar Alunos</a>
									<?php
								}
								if($_SESSION['permissao'] > 0){ ?>
									<a href="#myModal" role="button" class="btn" data-toggle="modal">Lista de Alunos</a>
									<?php
								} ?>
							</div>
						</div>
					</div>
				</div>


				<style>
				.window{
				    display:none;
				    width:300px;
				    height:300px;
				    position:absolute;
				    left:0;
				    top:0;
				    background:#FFF;
				    z-index:9900;
				    padding:10px;
				    border-radius:10px;
				}
				 
				#mascara{
				    display:none;
				    position:absolute;
				    left:0;
				    top:0;
				    z-index:9000;
				    background-color:#0f0f0f;
				}
				 
				.fechar{display:block; text-align:right;}
				</style>
				 
				<!-- Modal -->
				<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				  <div class="modal-header">
				    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				    <h3 id="myModalLabel">Alunos</h3>
				  </div>
				  <div class="modal-body">
				    <?php 
				    	$db = conectaBD();
				    	$query = "SELECT nome, ra FROM alunos a WHERE ra IN (SELECT ra FROM rel_turmas_alunos WHERE fk_turma = '".$_GET['id']."') ORDER BY a.nome";

				    	$result = mysql_query($query);
				    	if(mysql_num_rows($result) > 0){
				    		while ($row = mysql_fetch_array($result)) {
				    			echo "<p> ".$row['ra']." - <b>".$row['nome']."</b></p>";
				    		}
				    	}
				    ?>
				  </div>
				  <div class="modal-footer">
				    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				  </div>
				</div>


				<div class="row">
					<div class ="span12">
					<?php 


					$aba = array();
					$aba_conteudo = array();
					$i = 0;
					
					$db = conectaBD();	
					$query = "SELECT count(*),date_format(data_inicio, '%m/%Y') as data FROM horarios WHERE fk_turma = ".$_GET['id']." group by date_format(data_inicio, '%m/%Y') order by data_inicio";
					$result = mysql_query($query);
				
					while ($row = mysql_fetch_array($result))
					{
						$aba[$i]  = '';
						$aba_conteudo[$i]  = '';

						$aba[$i] .= $row['data']; 

						if($_SESSION['permissao'] == 1)
							$query = "SELECT pk_horario, DATE_FORMAT(data_inicio, '%d às %H:%i') as data_inicio, data_inicio data_aula, data_fim, NOW() as agora, ra FROM horarios LEFT JOIN presentes ON pk_horario = fk_horario AND ra = '".$_SESSION['usuario']."' AND presentes.flag = 1 AND horarios.flag = 3 WHERE fk_turma = ".$_GET['id']." AND DATE_FORMAT(data_inicio, '%m/%Y') = '".$row['data']."' order by data_inicio";
						else 
							$query = "SELECT pk_horario, DATE_FORMAT(data_inicio, '%d às %H:%i') as data_inicio, data_inicio data_aula, NOW() as agora FROM horarios WHERE fk_turma = ".$_GET['id']." AND DATE_FORMAT(data_inicio, '%m/%Y') = '".$row['data']."' order by data_inicio";
						
						$result3 = mysql_query($query);

						if($_SESSION['permissao'] == 1){
							while ($row2 = mysql_fetch_array($result3))
							{
								if($row2['ra'] != NULL)
									$aba_conteudo[$i] .= "<div><i class='icon-ok'></i> <b>Presente</b> - ";
								else
								{
									if($row2['agora'] > $row2['data_fim'])
										$aba_conteudo[$i] .= "<div><i class='icon-ok'></i> <b>Falta</b> - ";
									else 
										$aba_conteudo[$i] .= "<div><i class='icon-calendar'></i> ";
								}
									

								$aba_conteudo[$i] .= "Aula do dia ".$row2['data_inicio']."";
								$aba_conteudo[$i] .= "</div>";
							}
						}else if($_SESSION['permissao'] == 2){
							while ($row2 = mysql_fetch_array($result3))
							{
								if($row2['agora'] > $row2['data_aula'])
									$aba_conteudo[$i] .= "<div><a href='presenca.php?id=".$_GET['id']."&horario=".$row2['pk_horario']."'>Aula do dia ".$row2['data_inicio']."</a>";
								else
									$aba_conteudo[$i] .= "<div>Aula do dia ".$row2['data_inicio']."";
								$aba_conteudo[$i] .= "</div>";
							}
						} else if($_SESSION['permissao'] == 10){
							while ($row2 = mysql_fetch_array($result3))
							{
								$aba_conteudo[$i] .= "<div><a href='turma.php?id=".$_GET['id']."&del=".$row2['pk_horario']."'><i class='icon-remove'></i></a>";
								if($row2['agora'] > $row2['data_aula'])
									$aba_conteudo[$i] .= "<a href='presenca.php?id=".$_GET['id']."&horario=".$row2['pk_horario']."'>Aula do dia ".$row2['data_inicio']."</a>";
							 	else 
							 		$aba_conteudo[$i] .= "Aula do dia ".$row2['data_inicio']."";
								$aba_conteudo[$i] .= "</div>";
							}
						}
						

						$i++;

					}	
					desconectaBD($db);

						echo '
						<div class="bs-docs-example">
				            <ul id="myTab" class="nav nav-tabs">';

				            foreach ($aba as $key => $value) {
				            	if($key != 0)
				            		echo '<li class=""><a href="#'.$key.'" data-toggle="tab">'.$value.'</a></li>';
				            	else
				            		echo '<li class="active"><a href="#'.$key.'" data-toggle="tab">'.$value.'</a></li>';
				            }
				            echo '
				            </ul>
				            <div id="myTabContent" class="tab-content">';


				            foreach ($aba_conteudo as $key => $value) {
				              if($key != 0)
				              		echo '  <div class="tab-pane fade in" id="'.$key.'">
						                <p>'.$value.'</p>
						              </div>';
						       else 
						       	echo '  <div class="tab-pane in active" id="'.$key.'">
						                <p>'.$value.'</p>
						              </div>';
				            }
				            echo '
				            </div>
				         </div>
				         ';
					
							?>

							<?php 
				
					?>

					</div>
				</div>


			<script>
			  $(function () {
			    $('#myTab a:first').tab('show');
			  })
			</script>
			<?php 
		} // if mysql_num_rows > 0
		else {
			// echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./'>";
		}
	} 
	// INICIO DO CADASTRO DE NOVA AULA
	else if(is_numeric($_GET['id']) && isset($_GET['nova_aula']) && !isset($_GET['adicionar_alunos']))
	{ 
		$pk_turma = $_GET['id'];
		//Para pegar a informação da turma
			$query = "SELECT
					t.numero
				  FROM
				  	turmas t
				  WHERE
				  	pk_turma = '$pk_turma'";
		
		$db = conectaBD();
		$result = mysql_query($query);
		desconectaBD($db);

		if(mysql_num_rows($result) > 0)
		{
			$row = mysql_fetch_array($result);
			$num_turma = $row['numero'];
			?>
			<form action="turma.php?id=<?php echo $_GET['id'];?>" method="post" id="formAddAula" name="formAddAula" class="form-horizontal" onSubmit="return verificaFormAddAula(this);">
				<div class="row">
					<div class="span6">
						<div class="control-group">
							<label class ="control-label"></label>
							<div class="controls"><h4>Nova aula - Turma <?php echo $row['numero']?></h4></div>
						</div>
						<?php
						$db = conectaBD();
						$query = "SELECT * FROM salas";
						$result = mysql_query($query);
						?>
						<div class="control-group">
							<label class ="control-label">Sala:</label>
							<div class="controls">
								<select name="sala">
										<option value="0">Selecione um Sala</option>
									<?php
									while($sala = mysql_fetch_array($result)){ ?>
								    	<option value="<?php echo $sala['pk_sala']?>" <?php if($sala['pk_sala'] == $row['fk_sala']) echo 'selected'; ?>><?php echo $sala['nome'];?></option>
								    	<?php
								    } ?>
								</select>
							</div>
						</div>

						<div class="control-group">
							<label class ="control-label"></label>
							 <div class="controls"><input type="checkbox" name="dia[]" id="dia" value = "1"> Segunda-Feira</div>
							 <div class="controls"><input type="checkbox" name="dia[]" id="dia" value = "2"> Terça-Feira</div>
							 <div class="controls"><input type="checkbox" name="dia[]" id="dia" value = "3"> Quarta-Feira</div>
							 <div class="controls"><input type="checkbox" name="dia[]" id="dia" value = "4"> Quinta-Feira</div>
							 <div class="controls"><input type="checkbox" name="dia[]" id="dia" value = "5"> Sexta-Feira</div>
							 <div class="controls"><input type="checkbox" name="dia[]" id="dia" value = "6"> Sábado</div>
						</div>

						<div class="control-group">
							<label class ="control-label">Inicio:</label>
							<div class="controls">
								<select class="input-mini" name="hora_inicio">
								    <?php
									for($i = 8; $i<22; $i++){ ?>
								    	<option value="<?php echo $i;?>" <?php if($row['hora_inicio'] == "<?php echo $i;?>") echo 'selected'; ?>><?php echo $i;?></option>
								    <?php 
									}?>
								</select>
								:
								<select class="input-mini" name="minuto_inicio">
									<?php
									for($i = 0; $i<60; $i++){ ?>
								    	<option value="<?php echo $i;?>" <?php if($row['minuto_inicio'] == "<?php echo $i;?>") echo 'selected'; ?>><?php echo $i;?></option>
								    <?php 
									}?>
								</select>
							</div>
						</div>

						<div class="control-group">
							<label class ="control-label">Fim:</label>
							<div class="controls">
								<select class="input-mini" name="hora_fim">
								    <?php
									for($i = 8; $i<23; $i++){ ?>
								    	<option value="<?php echo $i;?>" <?php if($row['hora_fim'] == "<?php echo $i;?>") echo 'selected'; ?>><?php echo $i;?></option>
								    <?php 
									}?>
								</select>
								:
								<select class="input-mini" name="minuto_fim">
									<?php
									for($i = 0; $i<60; $i++){ ?>
								    	<option value="<?php echo $i;?>" <?php if($row['minuto_fim'] == "<?php echo $i;?>") echo 'selected'; ?>><?php echo $i;?></option>
								    <?php 
									}?>
								</select>
							</div>
						</div>
				

						<div class="control-group">
							<label class ="control-label"></label>
							<div class="controls"></div>
						</div>


						<div class="control-group">
							<label class ="control-label"></label>
							<div class="controls"><input class="btn btn-large btn-primary" type="submit" name="enviarNovaAula" value="Enviar" /></div>
						</div>
					</div> <!-- span6-->

				</div> <!-- row -->
				</form>
		<?php
		} // NUM ROWS > 0
	}
	// INICIO DA ADIÇÃO DE ALUNOS
	else if(is_numeric($_GET['id']) && !isset($_GET['nova_aula']) && isset($_GET['adicionar_alunos']))
	{ 
		?>
		<ul class="pager">
		    <li class="previous">
		      <a href = "turma.php?id=<?php echo $_GET['id']; ?>">&larr; Cancelar</a>
		    </li>
		</ul>
		<br/>
		<br />
		<div class="row">
			<div style="span12">
				<form class="navbar-search pull-left" >
				  <input style="height: 25px;line-height: 20px;" type="text" class="input-xxlarge search-query" placeholder="Pesquisar nas 2 tabelas" onkeyup="BuscaItemTabela(this);">
				  <br/><br/>
				</form>
			</div>
		</div>
					
		<style>
		textarea,
		input[type="text"],
		.uneditable-input {
		  display: inline-block;
		  height: 14px;

		  margin: 0 0 0 0;
		  font-size: 14px;
		  line-height: 2px;
		  color: #555555;
		}
		.btn {

		  display: inline-block;
		  *display: inline;
		  /* IE7 inline-block hack */

		  *zoom: 1;
		  padding: 4px 12px;
		  margin-bottom: 0;
		  font-size: 14px;
		  line-height: 12px;
		  text-align: center;
		  vertical-align: middle;
		  cursor: pointer;
		  
		}
		
		
		</style>

			<div class="row">
				<div class="span6">
					<h4>Lista de todos alunos</h4>
					<table class="table" id="lista">
						
						<thead>
							<tr>
								<th>RA.</th>
								<th>Nome</th>
								<th></th>
							</tr>
						</thead>
						<tbody >
						<?php

						$db = conectaBD();
						$query="SELECT a.*, r.fk_turma FROM `alunos` a LEFT JOIN rel_turmas_alunos r ON a.ra = r.ra AND r.fk_turma = '".$_GET['id']."' WHERE 1";
						$result = mysql_query($query);
						$result2 = mysql_query($query);
						desconectaBD($db);
						
						while($row = mysql_fetch_array($result)) {
							if($row['fk_turma'] == $_GET['id']){
								$hide = 'style="display:none"';
							}
							else $hide = '';
							echo '
								<tr id="item_' . $row['ra'] . '" class="itens_tabela" '.$hide.'>
									<td>' . $row['ra'] .'</td>
									<td class="nome">' . $row['nome'] . '</td>
									<td> <a class="btn btn-small" onClick="insereAlunoTurma(' . $row['ra'] . ', \'' . $row['nome'] . '\');"><i class="icon-chevron-right" ></i></a> </td>
								</tr>
								';
						}
						?>
						</tbody>
					</table>
				</div>

				<div class="span6">
					<h4>Lista dos alunos da turma</h4>
					<form action="turma.php?id=<?php echo $_GET['id'];?>" method="post" id="comboForm">							
						<table class="table" id="lista">
							<thead>
								<tr>
									<th>RA</th>
									<th>Nome</th>
					 			</tr>
							</thead>

							<tbody id="lista_combo">
							
							</tbody>

						</table>
						<br/>
						<div class="well well-small">
						<b>Total de alunos na turma:</b> <span id="qtd_alunos">0</span></td></tr>
						</div>

						<?php
						while($row = mysql_fetch_array($result2)) {
							if($row['fk_turma'] == $_GET['id']){
								echo '<script>insereAlunoTurma(' . $row['ra'] . ', \'' . $row['nome'] . '\');</script>';
							}
						}
						?>


						<div class="control-group">
							<label class ="control-label"></label>
							<div class="controls"><input class="btn btn-large btn-primary" type="submit" name="cadastrarAlunos" value="Salvar" /></div>
						</div>
						

					</form>
				</div>
			</div>
<?php

	}
	else
	{
		echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./turmas.php'>";
	}

} // if permissao

include "footer.php";

?>
