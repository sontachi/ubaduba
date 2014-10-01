<?php 

include "header.php";

if($_SESSION['admin'] > 0 && $_SESSION['perfil'] > 0)
{
    
    //CADASTRO OU EDIÇÃO
    if(isset($_POST['pk_sensor']))
    {
      if($_POST['intervalo_consulta'] <= 0)$_POST['intervalo_consulta'] = 0;
      else $_POST['intervalo_consulta']--;

      $pk_equipamento = $_POST['fk_equipamento'];
      $num_eq = equipamento_num($pk_equipamento);

      if (is_numeric($_POST['pk_sensor'])) {
        $db=conectaBDLaranja($_SESSION['empresa_admin'], $_SESSION['pk_equipamento_bd']);  
        $query = "UPDATE sensores SET local = '".$_POST['local']."',fk_sensor_tipo = '".$_POST['descricao']."',num_no = '".$num_eq."', fk_equipamento = '".$pk_equipamento."',pos_pkg = '".$_POST['pos_pkg']."', intervalo_consulta = '".$_POST['intervalo_consulta']."' WHERE fk_perfil = ".$_SESSION['perfil']." AND pk_sensor = ".$_POST['pk_sensor'];
        $result = mysql_query($query);
        desconectaBD($db);

        if($result){
          $mensagem="EDITAR-SENSOR ".$_POST['pk_sensor'];
          salvalog($mensagem, $_SESSION['empresa_admin']);
          $alerta_sucesso = "Sensor atualizado com sucesso.";
        }
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";

      }
      else
      { 

        // //PARA TESTES DE VARIOS LARANJAS (GAMBI)
        // $db=conectaBDAzul($_SESSION['empresa_admin']);
        // $query = "SELECT MAX(pk_sensor) as max FROM sensores";
        // $result = mysql_query($query);
        // if($result){
        //   $row = mysql_fetch_array($result);
        //   $maior = $row['max']+1;
        // }
        // else $maior = 1;

        $db=conectaBDLaranja($_SESSION['empresa_admin'], $_SESSION['pk_equipamento_bd']);  
        $query = "INSERT INTO sensores (pk_sensor, fk_sensor_tipo, fk_perfil, intervalo_consulta, num_no, fk_equipamento, pos_pkg, local) VALUES (NULL,'".$_POST['descricao']."', '".$_SESSION['perfil']."', '".$_POST['intervalo_consulta']."', '".$num_eq."','".$pk_equipamento."','".$_POST['pos_pkg']."','".$_POST['local']."')";
        $result = mysql_query($query);
        $pk_cadastrado = mysql_insert_id();

        // $query = "INSERT INTO consultas_sensor (fk_sensor_tipo, valor_lido, data) VALUES (".$pk_cadastrado.", 0, CURRENT_TIMESTAMP)";
        // $result2 = mysql_query($query);
        desconectaBD($db);

        if($result){
          $mensagem="CADASTRAR-SENSOR ".$pk_cadastrado;
          salvalog($mensagem, $_SESSION['empresa_admin']);
          $alerta_sucesso = "Sensor cadastrado com sucesso.";
        }
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";
      }
      usleep($usleep_time_azul);
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
        $db=conectaBDSelect($_SESSION['empresa_admin']);  
        $query="SELECT *, sensores_tipos.descricao as descricao, (intervalo_consulta+1) as intervalo_consulta FROM `sensores` sen INNER JOIN sensores_tipos ON fk_sensor_tipo = pk_sensor_tipo INNER JOIN perfil_cultura ON sen.fk_perfil = pk_perfil INNER JOIN equipamentos eq ON sen.fk_equipamento = eq.pk_equipamento WHERE administrativo = 0 AND sen.fk_perfil = ".$_SESSION['perfil']." AND sen.excluido = 0 AND eq.excluido = 0 order by num_no, local, fk_sensor_tipo, pos_pkg, intervalo_consulta";   
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
                        <th>Local</th>
                        <th>Descrição</th>
                        <th>Base / Nó / Pkg</th>
                        <th>Intervalo Consulta(seg)</th>
						<th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysql_fetch_array($result)) {
                        echo "<tr>
                                <td onclick='location.href = \"sensores.php?id=".$row['pk_sensor']."\";'>".$row['local']."</td>
  					                  	<td onclick='location.href = \"sensores.php?id=".$row['pk_sensor']."\";'>".$row['descricao']."</td>
                                <td onclick='location.href = \"sensores.php?id=".$row['pk_sensor']."\";'>".$row['num_base']." / ".$row['num_no']." / ".$row['pos_pkg']."</td>
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
          $query="SELECT sensores.*, (intervalo_consulta+1) as intervalo_consulta, sensores.fk_equipamento as fk_equipamento, perfil_cultura.num_base FROM `sensores` INNER JOIN perfil_cultura ON pk_perfil = fk_perfil WHERE fk_perfil = ".$_SESSION['perfil']." AND pk_sensor = " . $_GET['id'];   
         
        }
        else {
          $query="SELECT num_base FROM perfil_cultura WHERE pk_perfil = ".$_SESSION['perfil'];   
        }

        $db=conectaBDSelect($_SESSION['empresa_admin']);  
        
        $result = mysql_query($query);
        desconectaBD($db);
        $row = mysql_fetch_array($result);
        ?>

            <h3>Cadastro de Sensor</h3>

            <form class="form-horizontal" action="sensores.php" method="post">
              <input type="hidden" name="pk_sensor" value="<?php echo $_GET['id']; ?>" />
              <div class="control-group">
                <label class="control-label" for="descricao">Descrição</label>
                <div class="controls">
                <?php 
                  $db=conectaBDSelect($_SESSION['empresa_admin']); 
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
                <label class="control-label" for="local">Local</label>
                <div class="controls">
                  <input type="text" class="input-xxlarge" name="local" value="<?php echo $row['local']; ?>" placeholder="Local do sensor">
                </div>
              </div>

               <div class="control-group">
                <label class="control-label" for="base">Número Base</label>
                <div class="controls">
                  <?php echo $row['num_base'];?>
                </div>
              </div>
              
               <div class="control-group">
                <label class="control-label" for="no">Número Nó</label>
                <div class="controls">
	  			    	 <select name="fk_equipamento">
    	          		<?php  
                      $db=conectaBDSelect($_SESSION['empresa_admin']);  
    	          			$query = "SELECT * FROM equipamentos WHERE tipo = 1 AND excluido = 0 AND fk_perfil = ".$_SESSION['perfil']." order by num"; 
                      $result = mysql_query($query);
                      desconectaBD($db);

                  		while ($row2 = mysql_fetch_array($result))
          	  				{
          	  					if($row['fk_equipamento'] == $row2['pk_equipamento'])
          	  						echo "<option value='".$row2['pk_equipamento']."' selected>".$row2['num']."</option>";
          	  					else
          	  						echo "<option value='".$row2['pk_equipamento']."'>".$row2['num']."</option>";
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
                <label class="control-label" for="pos_pkg">Pos. Pkg</label>
                <div class="controls">
                  <select name="pos_pkg">
	                  <?php 
                  		for($i = 0; $i<52; $i++){
                  			if($i == $row['pos_pkg'])
        		  						echo "<option value='".$i."' selected>".$i."</option>";
        		  					else
        		  						echo "<option value='".$i."'>".$i."</option>";
		  				        }
			  			      ?>
	                </select>	
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

