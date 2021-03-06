<?php 

include "header.php";

if($_SESSION['permissao'] == 10)
{
    
    //CADASTRO OU EDIÇÃO
    if(isset($_POST['pk_sensor']))
    {
      if($_POST['intervalo_consulta'] <= 0)$_POST['intervalo_consulta'] = 0;
      else $_POST['intervalo_consulta']--;

      $sala_id = explode('_', $_POST['sala_id']);
      $sala = $sala_id[0];
      $num_eq = $sala_id[1];

      if (is_numeric($_POST['pk_sensor'])) {
        $db=conectaBD();  
        $query = "UPDATE sensores SET  fk_sala = '".$sala."',fk_sensor_tipo = '".$_POST['descricao']."',num_no = '".$num_eq."', intervalo_consulta = '".$_POST['intervalo_consulta']."' WHERE pk_sensor = ".$_POST['pk_sensor'];
        $result = mysql_query($query);
        desconectaBD($db);

        if($result)$alerta_sucesso = "Sensor atualizado com sucesso.";
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";

      }
      else
      { 

        $db=conectaBD();  
        $query = "INSERT INTO sensores (pk_sensor, fk_sensor_tipo, fk_sala, intervalo_consulta, num_no) VALUES (NULL,'".$_POST['descricao']."', '".$sala."', '".$_POST['intervalo_consulta']."', '".$num_eq."')";
        $result = mysql_query($query);
        desconectaBD($db);

        if($result)$alerta_sucesso = "Sensor cadastrado com sucesso.";
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";
      }
    }


    if(isset($_GET['del']))
       $alerta_erro = 'Deseja apagar realmente este sensor? <a href="sensores.php?deletar=' . $_GET['del'] . '">Clique aqui</a> para apagar.';

    if(isset($_GET['deletar'])){
        $db=conectaBDLaranja($_SESSION['empresa_admin'], $_SESSION['pk_equipamento_bd']);  
        $query = "UPDATE sensores SET excluido = 1 WHERE fk_perfil = ".$_SESSION['perfil']." AND pk_sensor = ".$_GET['deletar'];
        $result = mysql_query($query);
        desconectaBD($db);
    }

    // Layout


    if(isset($alerta_sucesso) && $alerta_sucesso!='')
        echo '<div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                '.$alerta_sucesso.'
              </div>';

    if(isset($alerta_erro) && $alerta_erro!='')
        echo '<div class="alert alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                '.$alerta_erro.'
              </div>';
        

    if(!isset($_GET['id']))
    {
        $db=conectaBD();  
        $query="SELECT *, sensores_tipos.descricao as descricao, (intervalo_consulta+1) as intervalo_consulta FROM `sensores` sen INNER JOIN sensores_tipos ON fk_sensor_tipo = pk_sensor_tipo INNER JOIN salas s ON sen.fk_sala = s.pk_sala WHERE sen.excluido = 0 AND sen.excluido = 0 order by num_no, fk_sensor_tipo, intervalo_consulta";   
        $result = mysql_query($query);
        desconectaBD($db);
    ?>

            <ul class="pager">
              <li class="next">
                <a href="sensores.php?id=novo">Cadastrar Novo Sensor &rarr;</a>
              </li>
            </ul>

            <h3>Sensores</h3>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Leitor / Sala</th>
                        <th>Intervalo Consulta(seg)</th>
						<th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysql_fetch_array($result)) {
                        echo "<tr>
  					                  	<td onclick='location.href = \"sensores.php?id=".$row['pk_sensor']."\";'>".$row['descricao']."</td>
                                <td onclick='location.href = \"sensores.php?id=".$row['pk_sensor']."\";'>".$row['id_leitor']." / ".$row['nome']."</td>
                                <td onclick='location.href = \"sensores.php?id=".$row['pk_sensor']."\";'>".$row['intervalo_consulta']."</td>
                                <td>
                                    <a href='sensores.php?id=".$row['pk_sensor']."'><i class='icon-pencil'></i></a>
                                    <a href='sensores.php?del=".$row['pk_sensor']."'><i class='icon-trash'></i></a>
                                </td>
                              </tr> ";
                    }
                    
                    ?>
                </tbody>
            </table>
<?php
    }
    else 
    {
        // Cadastro ou edição
        if(is_numeric($_GET['id'])) {
          $query="SELECT sensores.*, (intervalo_consulta+1) as intervalo_consulta FROM `sensores` WHERE pk_sensor = " . $_GET['id'];   
          $db=conectaBD();   
          $result = mysql_query($query);
          desconectaBD($db);
          $row = mysql_fetch_array($result);
        }
        ?>

            <h3>Cadastro de Sensor</h3>

            <form class="form-horizontal" action="sensores.php" method="post">
              <input type="hidden" name="pk_sensor" value="<?php echo $_GET['id']; ?>" />
              <div class="control-group">
                <label class="control-label" for="descricao">Descrição</label>
                <div class="controls">
                <?php 
                  $db=conectaBD(); 
                  $query = "SELECT * FROM sensores_tipos WHERE excluido = 0 order by descricao"; 
                  $result = mysql_query($query);
                  desconectaBD($db);
                  // if(mysql_num_rows($result) == 0) echo "Antes de cadastrar um sensor, é necessário cadastrar pelo menos um tipo de sensor. <a href='sensores_tipos.php?id=novo'>Clique aqui</a> para isso.";
                  ?>
	  				       <select name="descricao">
                    <?php
                      while ($row2 = mysql_fetch_array($result))
                      {
                      	if($row['fk_sensor_tipo'] == $row2['pk_sensor_tipo'])
                      		echo "<option value='".$row2['pk_sensor_tipo']."' selected>".$row2['descricao']."</option>";
                      	else
                      		echo "<option value='".$row2['pk_sensor_tipo']."'>".$row2['descricao']."</option>";
                      }
                      ?>
	                </select>	
                </div>
              </div>
              
               <div class="control-group">
                <label class="control-label" for="sala">Sala</label>
                <div class="controls">
	  			    	 <select name="sala_id">
    	          		<?php  
                      $db=conectaBD();  
    	          			$query = "SELECT * FROM salas"; 
                      $result = mysql_query($query);
                      desconectaBD($db);

                  		while ($row2 = mysql_fetch_array($result))
          	  				{
          	  					if($row['fk_sala'] == $row2['pk_sala'])
          	  						echo "<option value='".$row2['pk_sala']."_".$row2['id_leitor']."' selected>".$row2['nome']."</option>";
          	  					else
          	  						echo "<option value='".$row2['pk_sala']."_".$row2['id_leitor']."'>".$row2['nome']."</option>";
          	  				}
          		  			?>
	                </select>	
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="intervalo_consulta">Intervalo Consulta (Seg)</label>
                <div class="controls">
                	<input class="input-medium" type="number" id="intervalo_consulta" name="intervalo_consulta" value="<?php echo $row['intervalo_consulta']; ?>" size="10" maxlength="10" />
                </div>
              </div>
              
            	<div class="control-group">
            	  <div class="controls">
            	    <button type="submit" name="enviar" class="btn btn-large btn-primary">Salvar</button>
            	  </div>
            	</div>
            </form>

        <?php

    }
   
}
include "footer.php";
?>

