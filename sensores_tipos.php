<?php 

include "header.php";

//PAGINA INATIVA
if($_SESSION['admin'] == -999)
{
    
    if(isset($_POST['pk_sensor_tipo']))
    {
        if (is_numeric($_POST['pk_sensor_tipo'])) {

           $db=conectaBDLaranja($_SESSION['empresa_admin']);
           $query = "UPDATE sensores_tipos SET descricao = '".$_POST['descricao']."' WHERE pk_sensor_tipo = ".$_POST['pk_sensor_tipo'];
           $result = mysql_query($query);
           desconectaBD($db);

           if($result){
              $mensagem="EDITAR-SENSOR_TIPO ".$_POST['pk_sensor_tipo'];
              salvalog($mensagem, $_SESSION['empresa_admin']);
              $alerta_sucesso = "Sensor atualizado com sucesso.";
            }
           else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";

        }
        else
        { 
            $db=conectaBDLaranja($_SESSION['empresa_admin']);
            $query = "INSERT INTO sensores_tipos (pk_sensor_tipo, descricao) VALUES (NULL, '".$_POST['descricao']."')";
            $result = mysql_query($query);
            $pk_cadastrado = mysql_insert_id();
            desconectaBD($db);

            if($result){
              $mensagem="CADASTRAR-SENSOR ".$pk_cadastrado;
              salvalog($mensagem, $_SESSION['empresa_admin']);
              $alerta_sucesso = "sensor cadastrado com sucesso.";
            }
            else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";
        }
        usleep($usleep_time_azul);
    }


    if(isset($_GET['del']))
       $alerta_erro = 'Deseja apagar realmente este sensor? <a href="sensores_tipos.php?deletar=' . $_GET['del'] . '">Clique aqui</a> para apagar.';

    if(isset($_GET['deletar'])){
        $db=conectaBDLaranja($_SESSION['empresa_admin']);
        $query = "UPDATE sensores_tipos SET excluido = 1 WHERE pk_sensor_tipo = ".$_GET['deletar'];
        $result = mysql_query($query);
        desconectaBD($db);
    }


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
            $query="SELECT * FROM `sensores_tipos` WHERE excluido = 0 order by descricao";   
            $result = mysql_query($query);
            desconectaBD($db);
    ?>

            <ul class="pager">
              <li class="next">
                <a href="sensores_tipos.php?id=novo">Cadastrar Novo Tipo de Sensor &rarr;</a>
              </li>
              <li class="prev" style="float:left">
                <a href="sensores.php">&larr; Voltar aos Sensores</a>
              </li>
            </ul>

            <h3>Sensores</h3>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysql_fetch_array($result)) {
                            echo "<tr>
                                    <td onclick='location.href = \"sensores_tipos.php?id=".$row['pk_sensor_tipo']."\";'>".$row['descricao']."</td>
                                    <td>
                                        <a href='sensores_tipos.php?id=".$row['pk_sensor_tipo']."'><i class='icon-pencil'></i></a>
                                        <a href='sensores_tipos.php?del=".$row['pk_sensor_tipo']."'><i class='icon-trash'></i></a>
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
            $db=conectaBDSelect($_SESSION['empresa_admin']);
            $query="SELECT * FROM `sensores_tipos` WHERE pk_sensor_tipo = " . $_GET['id'];   
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            desconectaBD($db);
        }

        ?>

            <h3>Cadastro de Tipo de Sensor</h3>

            <form class="form-horizontal" action="sensores_tipos.php" method="post">
              <input type="hidden" name="pk_sensor_tipo" value="<?php echo $_GET['id']; ?>" />
              <div class="control-group">
                <label class="control-label" for="descricao">Descrição</label>
                <div class="controls">
                  <input type="text" class="input-xxlarge" name="descricao" value="<?php echo $row['descricao']; ?>" placeholder="Descrição">
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

