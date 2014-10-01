<?php include "header.php";
	if($_SESSION['permissao'] >=2 && is_numeric($_GET['id']) && is_numeric($_GET['horario']))  {


	if(isset($_POST['enviarPresenca'])){
		$horario = $_GET['horario'];

		$db = conectaBD();
		$query = "DELETE FROM presentes WHERE fk_horario = '$horario'";
		$result = mysql_query($query);


		foreach($_POST['alunos'] as $ra){
			$query = "INSERT INTO presentes (ra, fk_horario) VALUES ('$ra', '$horario')";
			$result = mysql_query($query);
		}

		$query = "UPDATE horarios SET flag = 3 WHERE pk_horario = '$horario'";
		$result = mysql_query($query);

		desconectaBD($db);
	}

	$id = $_GET['id'];
	$horario = $_GET['horario'];
?>
	
	<?php
	if(!isset($_GET['manual']))
	{ ?>
		<!--TABLE AJAX-->
		<script type="text/javascript">
			
		  function updateTable(series){
		    var i = 0;
		    var ra;
		    var aula;
		    var pode_entrar;

		    for(; i<series.data.length; i++){
		    	ra = series.data[i][0];
			   	aula = series.data[i][2];
			   	pode_entrar = series.data[i][3];

			   	if(aula == 0){
			   		document.getElementById("aula_<?php echo $id;?>").innerHTML="Aula não iniciada.";
			   	}
			   	else if(aula == 1 && pode_entrar == 1){
			   		document.getElementById("aula_<?php echo $id;?>").innerHTML="Aula iniciada - Entrada de alunos.";
			   	}
			   	else if(aula == 1  && pode_entrar == 0){
			   		document.getElementById("aula_<?php echo $id;?>").innerHTML="Aula em andamento - Entrada já finalizada.";
			   	}
			   	else if(aula == 2){
			   		document.getElementById("aula_<?php echo $id;?>").innerHTML="Aula em finalização - Saída iniciada.";
			   	}
			   	else if(aula == 3){
			   		document.getElementById("aula_<?php echo $id;?>").innerHTML="Aula finalizada.";
			   	}


		    	if(series.data[i][1] == 0){
		    		$("#" + series.data[i][0]).addClass("alert-warning");
		    		$("#" + series.data[i][0]).removeClass("alert-success");
		    		$("#" + series.data[i][0]).removeClass("alert-error");
		    	}
		    	else if( series.data[i][1] == 1){
					$("#" + series.data[i][0]).removeClass("alert-warning");
		    		$("#" + series.data[i][0]).addClass("alert-success");
		    		$("#" + series.data[i][0]).removeClass("alert-error");
		    	}
		    	else{
		    		$("#" + series.data[i][0]).removeClass("alert-warning");
		    		$("#" + series.data[i][0]).removeClass("alert-success");
		    		$("#" + series.data[i][0]).addClass("alert-error");
		    	}
		    }
		  }
		  function update() {
		      $.ajax({
		        url: "getPresentes.php?id=<?php echo $id.'&horario='.$horario;?>",
		        type: "GET",
		        dataType: "json",
		        success: updateTable
		      });
		      setTimeout(update, 1000);
		    }
		    update();
		</script>

	<?php
	}
	else
	{?>

		<script type="text/javascript">

			function checkAll(){
				console.log("AA");
				document.formPresenca.todos.checked=!document.formPresenca.todos.checked;
				var status = document.formPresenca.todos.checked;
				var alunos = document.formPresenca.elements;

				if(status == true){
					document.getElementById("coletiva").innerHTML = "Falta Coletiva";
				}
				else document.getElementById("coletiva").innerHTML = "Presença Coletiva";

				for(var i = 0; i < alunos.length; i++){
					var x = alunos[i];

					if(x.name == "alunos[]"){
						x.checked = status;
						toogle_class( status, $('table tbody tr') );
					}
				}
			}

			$(document).ready(function(){
				/* ao clicar no checkbox, altera a class da linha */
				$("input[name='alunos[]']").click(function(){
					toogle_class(this.checked, $( this ).parents('tr') );
				});
				$("table tbody tr").click(function( e ){
					if( e.target.tagName!='INPUT' )
					{
						var checkbox = $( this ).find("input[type='checkbox']");
						var ckd = !checkbox.attr('checked');
		 
						checkbox.attr('checked', ckd);
						toogle_class( ckd, $( this ) );
					}
				});
			});
			function toogle_class( ckd, el ){
				if( ckd==true )
				{
					el.addClass('alert alert-success');
					el.removeClass('alert alert-error');
				}
				else
				{
					el.addClass('alert alert-error');
					el.removeClass('alert alert-success');
				}
			}
			</script>

		<?php
	}?>


			<ul class="pager">
			    <li class="previous">
			      <a href = "turma.php?id=<?php echo $_GET['id']; ?>">&larr; Voltar</a>
			    </li>
			</ul>

			<?php
			if(isset($_GET['manual']))
			{
			?>
				<ul class="pager">
				    <li class="next">
				      <a href="presenca.php?id=<?php echo $_GET['id']."&horario=".$_GET['horario'];?>">Mudar para Visualização Tempo Real</a>
				    </li>
				</ul>
				<?php
			}
			else {
				?>
				<ul class="pager">
				    <li class="next">
				      <a href="presenca.php?id=<?php echo $_GET['id']."&horario=".$_GET['horario'];?>&manual">Mudar para Visualização Estática</a>
				    </li>
				</ul>
				<?php
			}?>
			


			<h2>Presença</h2>

			<h3 id="aula_<?php echo $id;?>"></h3>

			<div class="row">
				<div class="span6">

					<form class="navbar-search pull-left">
					  <input type="text" class="search-query" placeholder="Search" onkeyup="BuscaItemTabela(this);">
					</form>

				</div>
				<?php
				if(isset($_GET['manual']))
				{
				?>
					<div class="span6">
						<?php if($_SESSION['permissao'] >= 2){?>
							<ul class="pager">
							  <li class="next">
							    <a href="#" onClick="checkAll();" id="coletiva">Presença Coletiva</a>
							  </li>
							</ul>
						<?php } ?>
					</div>
					<?php
				}?>
			</div>

			<?php
			if(isset($_GET['manual']))$manual = "&manual";
			else $manual = "";
			?>
			<form action="presenca.php?id=<?php echo $_GET['id']."&horario=".$_GET['horario'].$manual;?>" method="post" id="formPresenca" name="formPresenca" class="form-horizontal" onSubmit="return verificaFormAddAula(this);">
				<input type="checkbox" style="display:none;" name="todos">

				<table class="table">
					<thead>
						<tr>
							<th>Nome</th>
							<th>RA</th>

						</tr>
					</thead>
					<tbody>
						
					<?php
					$id=$_GET['id'];
					$horario=$_GET['horario'];
					$db = conectaBD();
					$query="SELECT a.ra, a.nome, p.flag FROM alunos a 
								INNER JOIN rel_turmas_alunos r ON a.ra = r.ra AND r.fk_turma = $id
								INNER JOIN horarios h ON r.fk_turma = h.fk_turma AND h.pk_horario = $horario
								LEFT JOIN presentes p ON a.ra = p.ra AND p.fk_horario = h.pk_horario
								ORDER BY a.nome";
					$result = mysql_query($query);
					desconectaBD($db);
					while($row = mysql_fetch_array($result)) {
						if($row['flag'] == NULL || $row['flag'] == 0)
							echo '
								<tr id="'.$row['ra'].'" class="itens_tabela alert alert-error">
									<td>' . $row['nome'] . '</td>
									<td>' . $row['ra'] . '<input style="display:none;" type="checkbox" name="alunos[]" value="' . $row['ra'] . '"></td>
								</tr>
								';
						else
							echo '
									<tr id="'.$row['ra'].'"  class="itens_tabela alert alert-success">
									<td>' . $row['nome'] . '</td>
									<td>' . $row['ra'] . '<input style="display:none;" type="checkbox" name="alunos[]" value="' . $row['ra'] . '" checked></td>
								</tr>
								';
						}
					?>
					</tbody>
				</table>
				<div class="control-group">
					<label class ="control-label"></label>
					<div class="controls"></div>
				</div>

				<?php
				if(isset($_GET['manual'])){
					?>
					<div class="control-group">
						<label class ="control-label"></label>
						<div class="controls"><input class="btn btn-large btn-primary" style="float:right" type="submit" name="enviarPresenca" value="Salvar" /></div>
					</div>
					<?php
				}?>
		</form>		
    <?php 
	}else{
		echo 'C';
	}
	include "footer.php";

?>

