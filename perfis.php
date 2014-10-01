<?php 

include "header.php";

if($_SESSION['admin'] > 0)
{

    // Ações no banco de dados
    if(isset($_POST['pk_perfil']))
    {
      if (is_numeric($_POST['pk_perfil'])) {
        
        $db=conectaBDSelect($_SESSION['empresa_admin']);
        $query = "SELECT fk_equipamento FROM perfil_cultura WHERE pk_perfil = ".$_POST['pk_perfil'];
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $pk_equipamento = $row['fk_equipamento'];
        desconectaBD($db);

        $db=conectaBDLaranja($_SESSION['empresa_admin'], $pk_equipamento);
        $query = "UPDATE perfil_cultura SET fk_estufa = '".$_POST['estufa']."', fk_cultura = '".$_POST['cultura']."', inicio = STR_TO_DATE('".$_POST['inicio']."', '%d/%m/%Y'), plantio = '".$_POST['plantio']."', safra = '".$_POST['safra']."' WHERE pk_perfil = '".$_POST['pk_perfil']."' AND pk_perfil = ".$_POST['pk_perfil'];
        $result = mysql_query($query);
        desconectaBD($db);

        if($result){
          $mensagem="EDITAR-PERFIL ".$_POST['pk_perfil'];
          salvalog($mensagem, $_SESSION['empresa_admin']);
          $alerta_sucesso = "Perfil atualizado com sucesso.";
        }
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";

      }
      else
      {

        $db=conectaBDSelect($_SESSION['empresa_admin']);
        $query = "SELECT * FROM equipamentos WHERE pk_equipamento = ".$_POST['equipamento'];
        $result = mysql_query($query);
        $equip = mysql_fetch_array($result);
        $base = $equip['num'];
        $pk_equipamento = $equip['pk_equipamento'];
        desconectaBD($db);

        $db=conectaBDSelect($_SESSION['empresa_admin']);
        $query = "SELECT * FROM culturas WHERE pk_cultura = ".$_POST['cultura'];
        $resultCultura = mysql_query($query);
        desconectaBD($db);

        if($resultCultura){
          $row = mysql_fetch_array($resultCultura);

          $db=conectaBDLaranja($_SESSION['empresa_admin'], $pk_equipamento);
          $query = "INSERT INTO `culturas` (pk_cultura, nome, unidade_plantio, unidade_safra, excluido) VALUES ('".$row['pk_cultura']."', '".$row['nome']."', '".$row['unidade_plantio']."', '".$row['unidade_safra']."', '".$row['excluido']."');";
          $resultInsert = mysql_query($query);
          desconectaBD($db);
        }         

        $db=conectaBDSelect($_SESSION['empresa_admin']);
        $query = "SELECT * FROM estufas WHERE pk_estufa = ".$_POST['estufa'];
        $result2 = mysql_query($query);
        desconectaBD($db);

        if($result2){
          $row = mysql_fetch_array($result2);
          
          $db=conectaBDLaranja($_SESSION['empresa_admin'], $pk_equipamento);
          $query = "INSERT INTO `estufas` (pk_estufa, tipo, nome, descricao, comprimento, largura, altura, excluido) VALUES ('".$row['pk_estufa']."', '".$row['tipo']."', '".$row['nome']."', '".$row['descricao']."', '".$row['comprimento']."', '".$row['largura']."', '".$row['altura']."', '".$row['excluido']."');";
          $resultInsert = mysql_query($query);
          desconectaBD($db);
        }

        $db=conectaBDAzul($_SESSION['empresa_admin']);
        $query = "INSERT INTO `perfil_cultura` (`pk_perfil`, `fk_cultura`, `fk_estufa`, `inicio`, `fim`, `perfil_ativo`, `plantio`, `safra`, `fk_equipamento`, `num_base`)
                  VALUES (NULL, '".$_POST['cultura']."','".$_POST['estufa']."', STR_TO_DATE('".$_POST['inicio']."', '%d/%m/%Y'), CURRENT_TIMESTAMP, 1, '".$_POST['plantio']."', '".$_POST['safra']."', '".$_POST['equipamento']."', '".$base."');";
        $result = mysql_query($query);
        $pk_cadastrado = mysql_insert_id();
        desconectaBD($db);
        
        if($result){
          $db=conectaBDLaranja($_SESSION['empresa_admin'], $pk_equipamento);
           $query = "INSERT INTO `perfil_cultura` (`pk_perfil`, `fk_cultura`, `fk_estufa`, `inicio`, `fim`, `perfil_ativo`, `plantio`, `safra`, `fk_equipamento`, `num_base`)
                  VALUES ($pk_cadastrado, '".$_POST['cultura']."','".$_POST['estufa']."', STR_TO_DATE('".$_POST['inicio']."', '%d/%m/%Y'), CURRENT_TIMESTAMP, 1, '".$_POST['plantio']."', '".$_POST['safra']."', '".$_POST['equipamento']."', '".$base."');";
          $result = mysql_query($query);
          
           $query = "UPDATE equipamentos SET fk_perfil = '".$pk_cadastrado."' WHERE pk_equipamento = ".$pk_equipamento;
          $result2 = mysql_query($query);
          desconectaBD($db);
        }

        if($result){
          $mensagem="CADASTRAR-PERFIL ".$pk_cadastrado;
          salvalog($mensagem, $_SESSION['empresa_admin']);
          $alerta_sucesso = "Perfil cadastrado com sucesso.";
        }
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";
      }
      usleep($usleep_time_azul);
    }


    if(isset($_GET['del']))
       $alerta_erro = 'Deseja realmente apagar este perfil? <a href="perfis.php?deletar=' . $_GET['del'] .'">Clique aqui</a> para apagar.';

    if(isset($_GET['deletar'])){

        $db=conectaBDSelect($_SESSION['empresa_admin']);
        $query = "SELECT fk_equipamento FROM perfil_cultura WHERE pk_perfil = ".$_GET['deletar'];
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $pk_equipamento = $row['fk_equipamento'];
        desconectaBD($db);

        $db=conectaBDLaranja($_SESSION['empresa_admin'], $pk_equipamento);
        $query = "UPDATE perfil_cultura SET excluido = 1 WHERE perfil_ativo = 1 AND pk_perfil = ".$_GET['deletar'];
        $result = mysql_query($query);
        desconectaBD($db);

        usleep($usleep_time_azul);
    }

    if(isset($_GET['end']))
       $alerta_importante = 'Deseja realmente finalizar este perfil? Essa ação é permanente. <a href="perfis.php?done=' . $_GET['end'] .'">Clique aqui</a> para finalizar.';

    if(isset($_GET['done'])){
        
        $db=conectaBDSelect($_SESSION['empresa_admin']);
        $query = "SELECT fk_equipamento FROM perfil_cultura WHERE pk_perfil = ".$_GET['done'];
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $pk_equipamento = $row['fk_equipamento'];
        desconectaBD($db);
         
        $db=conectaBDLaranja($_SESSION['empresa_admin'], $pk_equipamento);
        $query = "UPDATE perfil_cultura SET perfil_ativo = 0, fim = NOW() WHERE pk_perfil = ".$_GET['done'];
        $result = mysql_query($query);
        desconectaBD($db);

        usleep($usleep_time_azul);
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
    if(isset($alerta_importante) && $alerta_importante!='')
        echo '<div class="alert alert-error">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                '.$alerta_importante.'
              </div>';
        

    if(!isset($_GET['id']))
    {
            $db=conectaBDSupe();
            $query="SELECT pc.*, es.nome nome_estufa, c.nome as nome_cultura, DATE_FORMAT(inicio, '%d/%m/%Y') inicio, DATE_FORMAT(fim, '%d/%m/%Y') fim FROM `perfil_cultura` pc INNER JOIN culturas c ON pk_cultura = fk_cultura INNER JOIN estufas es ON fk_estufa = pk_estufa WHERE pk_perfil > 0 AND pc.excluido = 0 order by perfil_ativo, nome_estufa, nome_cultura desc";   
            $result = mysql_query($query);
            desconectaBD($db);
    ?>

            <ul class="pager">
              <li class="next">
                <a href="perfis.php?id=novo">Cadastrar novo perfil &rarr;</a>
              </li>
            </ul>

            <h2>Perfis</h2>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Estufa</th>
                        <th>Cultura</th>
                        <th>Base</th>
                        <th>Inicio</th>
                        <th>Fim</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysql_fetch_array($result)) {
                      if($row['perfil_ativo'] == 1)$fim = "Em aberto";
                      else $fim = $row['fim'];
                        echo "<tr>
                                <td onclick='location.href = \"perfis.php?id=".$row['pk_perfil']."\";'>".$row['nome_estufa']."</td>
                                <td onclick='location.href = \"perfis.php?id=".$row['pk_perfil']."\";'>".$row['nome_cultura']."</td>
                                <td onclick='location.href = \"perfis.php?id=".$row['pk_perfil']."\";'>".$row['num_base']."</td>
                                <td onclick='location.href = \"perfis.php?id=".$row['pk_perfil']."\";'>".$row['inicio']."</td>
                                <td onclick='location.href = \"perfis.php?id=".$row['pk_perfil']."\";'>".$fim."</td>
                                <td>";

                                if((!isset($_SESSION['perfil']) || $_SESSION['perfil'] == -1) && $row['perfil_ativo'] == 1)
                                echo "
                                    <a href='perfis.php?id=".$row['pk_perfil']."'><i class='icon-pencil'></i></a>
                                    <a href='perfis.php?del=".$row['pk_perfil']."'><i class='icon-trash'></i></a>
                                    <a href='equipamentos.php?set=".$row['pk_perfil']."'><i class='icon-play'></i> Utilizar Perfil</a> <a href='perfis.php?end=".$row['pk_perfil']."'><i class='icon-remove'></i>  Finalizar Perfil</a> " ;
                                    echo "
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
            $db=conectaBDSupe();
            $query="SELECT *, DATE_FORMAT(inicio, '%d/%m/%Y') as data FROM `perfil_cultura` WHERE pk_perfil = " . $_GET['id'];   
            $result = mysql_query($query);
            desconectaBD($db);

            $row = mysql_fetch_array($result);

        }
        else{
            $query = "SELECT DATE_FORMAT(CURRENT_TIMESTAMP, '%d/%m/%Y') as data";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            $row['safra'] = 0;
            $row['plantio'] = 0;
        }
        ?>

            <h3>Cadastro de perfil</h3>

            <form class="form-horizontal" action="perfis.php" method="post">
              <input type="hidden" name="pk_perfil" value="<?php echo $_GET['id']; ?>" />
              
              <?php
              $db = conectaBDSelect($_SESSION['empresa_admin']);
              $query = "SELECT estufas.* FROM estufas WHERE excluido = 0";
              $result = mysql_query($query);

              
              ?>
                <div class="control-group">
                  <label class="control-label" for="estufa">Estufa:</label>
                  <div class="controls">
                    <?php
                    if(mysql_num_rows($result) == 0) echo "Antes de cadastrar um perfil, é necessário cadastrar pelo menos uma estufa. <a href='estufas.php?id=novo'>Clique aqui</a> para isso.";
                    else
                    {
                      ?>
                      <select class="input-xlarge" name="estufa">
                        <option value="0">Selecione uma estufa</option>
                        <?php
                        while($row2 = mysql_fetch_array($result)){ ?>
                          <option value="<?php echo $row2['pk_estufa'];?>" <?php if($row2['pk_estufa'] == $row['fk_estufa'])echo "selected";?>><?php echo $row2['nome'];?></option>
                        <?php
                        }
                        ?>
                      </select>
                      <?php
                    } ?>
                  </div>
                </div>

                <?php
                $db = conectaBDSelect($_SESSION['empresa_admin']);
                $query = "SELECT * FROM culturas WHERE excluido = 0";
                $result = mysql_query($query);

                ?>

                <div class="control-group">
                  <label class="control-label" for="cultura">Cultura:</label>
                  <div class="controls">
                    <?php
                    if(mysql_num_rows($result) == 0) echo "Antes de cadastrar um perfil, é necessário cadastrar pelo menos uma cultura. <a href='culturas.php?id=novo'>Clique aqui</a> para isso.";
                    else
                    {
                      ?>
                      <select class="input-xlarge" name="cultura">
                        <option value="0">Selecione uma cultura</option>
                        <?php
                        while($row2 = mysql_fetch_array($result)){ ?>
                          <option value="<?php echo $row2['pk_cultura'];?>" <?php if($row2['pk_cultura'] == $row['fk_cultura'])echo "selected";?>><?php echo $row2['nome'];?></option>
                        <?php
                        } ?>
                      </select>
                      <?php
                    } ?>
                  </div>
                </div>

                <?php
                $db=conectaBDSelect($_SESSION['empresa_admin']);  
                if(isset($_GET['id']) && is_numeric($_GET['id']))$perfil = "OR fk_perfil = ".$_GET['id'];
                else $perfil = "";

                $query = "SELECT * FROM equipamentos INNER JOIN perfil_cultura ON fk_perfil = pk_perfil WHERE pk_equipamento > 0 AND (fk_perfil = 0 $perfil) AND tipo = 0 AND equipamentos.excluido = 0";
                $result = mysql_query($query);
                desconectaBD($db);
                ?>
                <div class="control-group">
                  <label class="control-label" for="base">Número Base</label>
                  <div class="controls">
                    <?php
                    if(mysql_num_rows($result) == 0) echo "Antes de cadastrar um perfil, é necessário cadastrar pelo menos uma base. <a href='equipamentos.php?id=novo'>Clique aqui</a> para isso.";
                    else
                    {
                      ?>
                      <select name="equipamento" class="input-small" <?php if(isset($_GET['id']) && is_numeric($_GET['id']))echo "disabled";?>>
                      <?php
                        while ($row2 = mysql_fetch_array($result))
                        {
                          if($row['fk_equipamento'] == $row2['pk_equipamento'])
                            echo "<option value='".$row2['pk_equipamento']."' selected>".$row2['num']."</option>";
                          else
                            echo "<option value='".$row2['pk_equipamento']."'>".$row2['num']."</option>";
                        }
                        ?>
                      </select>
                    <?php
                    }
                      ?> 
                  </div>
                </div>

                  <div class="control-group">
                    <label class ="control-label">Início:</label>
                    <div class="controls">
                      <div id="datetimepicker" class="input-append date">
                          <input class="input-xlarge" name="inicio" type="text" id="inicio" value="<?php echo $row['data']?>"></input>  
                          <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                          </span>
                        </div>
                    </div>
                  </div>

                  <div class="control-group">
                    <label class="control-label" for="plantio">Plantio</label>
                    <div class="controls">
                      <input type="text" class="input-small" name="plantio" value="<?php echo $row['plantio']; ?>" placeholder="Plantio">
                    </div>
                  </div>   
                   <div class="control-group">
                    <label class="control-label" for="safra">Safra</label>
                    <div class="controls">
                      <input type="text" class="input-small" name="safra" value="<?php echo $row['safra']; ?>" placeholder="Plantio">
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

