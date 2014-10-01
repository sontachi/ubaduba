<?php 

include "header.php";

if($_SESSION['admin'] > 0)
{
    if(isset($_POST['pk_equipamento']))
    {
      if((!isset($_SESSION['perfil']) || $_SESSION['perfil'] == -1) && $tipo_equipamento == 0){
        $perfil = 0;
      }
      else $perfil = $_SESSION['perfil'];

      if (is_numeric($_POST['pk_equipamento'])) {

        //Se o equipamento for BASE, é necessário setar a session, para conectar no laranja
        //No caso de ser nó, a session já está setada
        if($tipo_equipamento == 0)$_SESSION['pk_equipamento_bd'] = $_POST['pk_equipamento'];

        $db=conectaBDLaranja($_SESSION['empresa_admin'], $_SESSION['pk_equipamento_bd']);
        $query = "UPDATE equipamentos SET descricao = '".$_POST['descricao']."' WHERE pk_equipamento = ".$_POST['pk_equipamento']." AND tipo = ".$tipo_equipamento;
        $result = mysql_query($query);
        desconectaBD($db);

        if($result){
          $mensagem="EDITAR-EQUIPAMENTO ".$_POST['pk_equipamento'];
          salvalog($mensagem, $_SESSION['empresa_admin']);
          $alerta_sucesso = "Atualizado com sucesso!";
        }
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";

      }
      else
      { 
        $id_empresa = $_SESSION['empresa_admin'];

        //Se for base adiciona no Azul primeiro
        if($tipo_equipamento == 0){
          $db = conectaBDAzul($id_empresa);
        }
        //Se for nó, adiciona no laranja e o sync traz para o azul
        else
          $db = conectaBDLaranja($_SESSION['empresa_admin'], $_SESSION['pk_equipamento_bd']);

        

        $query = "INSERT INTO equipamentos (num, tipo, descricao, fk_perfil)
                  VALUES (".$_POST['num'].",".$tipo_equipamento.",'".$_POST['descricao']."', '".$perfil."')";
        $result = mysql_query($query);
        $pk_cadastrado = mysql_insert_id();
        desconectaBD($db);

        if($result){
          if($tipo_equipamento == 0){
            //Se o insert foi bem feito e é base, adiciona os empresa_bd laranja no azul
            
            $db = conectaBDAzul($id_empresa);
            $query = "INSERT INTO empresas_bd (fk_equipamento, fk_empresa, tipo, servidor, usuario, senha, porta, nome_bd)
                  VALUES ($pk_cadastrado, $id_empresa, '3','".$_POST['servidor_laranja']."', '".$_POST['usuario_laranja']."', '".$_POST['senha_laranja']."', '3306' ,'".$_POST['nome_bd_laranja']."'),
                         ($pk_cadastrado, $id_empresa, '4','".$_POST['servidor_laranja_nat']."', '".$_POST['usuario_laranja_nat']."', '".$_POST['senha_laranja_nat']."', '".$_POST['porta_laranja_nat']."' ,'".$_POST['nome_bd_laranja_nat']."')";
            $result = mysql_query($query);
            desconectaBD($db);

            //Adiciona a base no laranja
            $db = conectaBDLaranja($_SESSION['empresa_admin'], $pk_cadastrado);
            $query = "INSERT INTO equipamentos (pk_equipamento, num, tipo, descricao, fk_perfil)
                      VALUES (".$pk_cadastrado.",".$_POST['num'].",".$tipo_equipamento.",'".$_POST['descricao']."', '".$perfil."')";
            $result = mysql_query($query);
            desconectaBD($db);
          }

          //Se o insert da base não der certo no Laranja, apaga tudo a base do azul
          //E então é necessário cadastrar a base novamente.
          // if(!$result){
          //   conectaBDAzul($id_empresa);
          //   $query = "DELETE FROM equipamentos WHERE pk_equipamento = ".$pk_cadastrado;
          //   $result = mysql_query($query);
          //   desconectaBD($db);
          //   $alerta_erro = "Houve um problema nesta operação devido à NÃO conexão com o laranja cadastrado.";
          // }
          // else{
          //   $mensagem="CADASTRAR-EQUIPAMENTO ".$pk_cadastrado;
          //   salvalog($mensagem, $_SESSION['empresa_admin']);
          //   $alerta_sucesso = $nome_equipamento." cadastrado com sucesso.";  
          // }
          
        }
        else $alerta_erro = "Houve um problema nesta operação. Tente novamente.";
      }
      usleep($usleep_time_verde);
    }


    if(isset($_GET['del']))
       $alerta_erro = 'Deseja apagar realmente este '.$nome_equipamento.'? <a href="equipamentos.php?deletar=' . $_GET['del'] . '">Clique aqui</a> para apagar.';

    if(isset($_GET['deletar'])){
        if($tipo_equipamento == 0)$pk_equipamento = $_GET['deletar'];

        //Seleciona do azul o equipamento
        $db = conectaBDAzul($_SESSION['empresa_admin']);
        $query="SELECT * FROM `equipamentos` INNER JOIN perfil_cultura ON num_base = num WHERE pk_equipamento > 0 AND pk_equipamento = ".$_GET['deletar'];   
        $result = mysql_query($query);
        desconectaBD($db);

        if(mysql_num_rows($result) == 0){
          //Se for nó
          $db=conectaBDLaranja($_SESSION['empresa_admin'], $_SESSION['pk_equipamento_bd']);
          $query = "UPDATE equipamentos SET excluido = 1 WHERE pk_equipamento = ".$_GET['deletar']." AND tipo = ".$tipo_equipamento;
          $result = mysql_query($query);
          desconectaBD($db);
          if(!$result){
            //Se update no laranja funcionar, tenta no azul;
            $db = conectaBDAzul($_SESSION['empresa_admin']);
            $result = mysql_query($query);
            desconectaBD($db);
          }
          
        }
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
        $complemento = "";
        if($_SESSION['perfil'] > 0)$complemento = " AND fk_perfil = ".$_SESSION['perfil']." ";
        $complemento .= "AND tipo = $tipo_equipamento";

        $db=conectaBDSelect($_SESSION['empresa_admin']);
         $query="SELECT * FROM `equipamentos` INNER JOIN perfil_cultura ON fk_perfil = pk_perfil AND perfil_cultura.excluido = 0 WHERE pk_equipamento > 0 AND equipamentos.excluido = 0 $complemento order by fk_perfil, num";   
        $result = mysql_query($query);
        desconectaBD($db);
    ?>

            <ul class="pager">
              <li class="next">
                <a href="equipamentos.php?id=novo">Cadastrar Novo Equipamento &rarr;</a>
              </li>
            </ul>

            <h3><?php echo $nome_equipamento."s";?></h3>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <?php
                        if(!isset($_SESSION['perfil']) || $_SESSION['perfil'] == -1)
                          echo '<th>Perfil</th>'; ?>
                        <th>Num</th>
                        <th>Descrição</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysql_fetch_array($result)) {
                        if($row['tipo'] <= 0){
                          $tipo = "Base";
                          
                          if($row['fk_perfil'] <= 0){
                            $cor = 'class="alert alert-success"';
                            $perfil_num = "Sem perfil";
                          }
                          else{
                            $cor = 'class="alert alert-block"';
                            $perfil_num = $row['pk_perfil'];
                          } 
                        }
                        else{
                          $tipo = "Nó";
                        } 
                        
                          

                        echo "<tr $cor>";
                            if(!isset($_SESSION['perfil']) || $_SESSION['perfil'] == -1)
                                echo "<td onclick='location.href = \"equipamentos.php?id=".$row['pk_equipamento']."&tipo=".$row['tipo']."\";'>".$perfil_num."</td>";
                                echo "
                                <td onclick='location.href = \"equipamentos.php?id=".$row['pk_equipamento']."&tipo=".$row['tipo']."\";'>".$row['num']."</td>
                                <td onclick='location.href = \"equipamentos.php?id=".$row['pk_equipamento']."&tipo=".$row['tipo']."\";'>".$row['descricao']."</td>
                                <td>";
                               
                                if(!is_numeric($perfil_num)){
                                    echo "<a href='equipamentos.php?id=".$row['pk_equipamento']."'><i class='icon-pencil'></i></a>
                                          <a href='equipamentos.php?del=".$row['pk_equipamento']."'><i class='icon-trash'></i></a>";
                                }
                                else if($row['tipo'] == 0){
                                    echo "Base já utilizada.";
                                }
                                echo "</td>
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

            if(isset($_SESSION['perfil']) && $_SESSION['perfil'] > 0)$complemento = "AND tipo = ".$_GET['tipo'];
            else $complemento = "AND tipo = 0";

            $db=conectaBDSelect($_SESSION['empresa_admin']);
            $query="SELECT * FROM `equipamentos` WHERE pk_equipamento = ".$_GET['id']." $complemento";   
            $result = mysql_query($query);
            desconectaBD($db);

            $row = mysql_fetch_array($result);
        }
        ?>
        
            <h3>Cadastro de <?php echo $nome_equipamento;?></h3>

            <form class="form-horizontal" action="equipamentos.php" method="post">
              <input type="hidden" name="pk_equipamento" value="<?php echo $_GET['id']; ?>" />

               <div class="control-group">
                <label class="control-label" for="num">Número <?php echo $nome_equipamento;?></label>
                <div class="controls">
                  <input type="text" class="number" name="num" value="<?php echo $row['num']; ?>" placeholder="Número do <?php echo $nome_equipamento;?>" <?php if(is_numeric($_GET['id']))echo 'disabled';?>>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label" for="local">Descrição</label>
                <div class="controls">
                  <input type="text" class="input-xxlarge" name="descricao" value="<?php echo $row['descricao']; ?>" placeholder="Descrição do <?php echo $nome_equipamento;?>">
                </div>
              </div>

                <?php
                if($tipo_equipamento == 0){
                    $db=conectaBDSelect($_SESSION['empresa_admin']);
                    $query="SELECT * FROM `empresas_bd` WHERE tipo = 3 AND fk_equipamento = ".$_GET['id']." limit 1";
                    $result = mysql_query($query);
                    desconectaBD($db);
                    $row = mysql_fetch_array($result);
                    ?>
                     <div class="control-group">
                      <label class ="control-label"></label>
                      <div class="controls"><h4>Banco de Dados Laranja</h4></div>
                     </div>
                     <div class="control-group">
                      <label class ="control-label" >Servidor:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="servidor_laranja" name="servidor_laranja" value="<?php if(isset($row['servidor']))echo $row['servidor'];else echo "192.168."; ?>" maxlength="100" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>

                     <div class="control-group">
                      <label class ="control-label" >Usuário:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="usuario_laranja" name="usuario_laranja" value="<?php if(isset($row['usuario']))echo $row['usuario'];else echo "root"; ?>" maxlength="100" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>

                     <div class="control-group">
                      <label class ="control-label">Senha:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="senha_laranja" name="senha_laranja" value="<?php if(isset($row['senha']))echo $row['senha'];else echo "AgroXP01"; ?>" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>
                     
                      <div class="control-group">
                      <label class ="control-label">Database:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="nome_bd_laranja" name="nome_bd_laranja" value="<?php if(isset($row['nome_bd']))echo $row['nome_bd'];else echo "laranja"; ?>" maxlength="20" placeholder="" datamask="" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>

                    <?php
                    $db=conectaBDSelect($_SESSION['empresa_admin']);
                    $query="SELECT * FROM `empresas_bd` WHERE tipo = 4 AND fk_equipamento = ".$_GET['id']." limit 1";
                    $result = mysql_query($query);
                    desconectaBD($db);
                    $row = mysql_fetch_array($result);
                    ?>

                      <div class="control-group">
                      <label class ="control-label"></label>
                      <div class="controls"><h4>Banco de Dados Laranja Nat</h4></div>
                     </div>
                     <div class="control-group">
                      <label class ="control-label" >Servidor:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="servidor_laranja_nat" name="servidor_laranja_nat" value="<?php if(isset($row['servidor']))echo $row['servidor'];else echo "192.168."; ?>" maxlength="100" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>
                     <div class="control-group">
                      <label class ="control-label" >Porta:</label>
                      <div class="controls"><input class="input-xlarge" type="number" id="porta_laranja_nat" name="porta_laranja_nat" value="<?php if(isset($row['porta']))echo $row['porta'];else echo "3306"; ?>" maxlength="100" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>
                     <div class="control-group">
                      <label class ="control-label" >Usuário:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="usuario_laranja_nat" name="usuario_laranja_nat" value="<?php if(isset($row['usuario']))echo $row['usuario'];else echo "root"; ?>" maxlength="100" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>

                     <div class="control-group">
                      <label class ="control-label">Senha:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="senha_laranja_nat" name="senha_laranja_nat" value="<?php if(isset($row['senha']))echo $row['senha'];else echo "AgroXP01"; ?>" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>
                     
                      <div class="control-group">
                      <label class ="control-label">Database:</label>
                      <div class="controls"><input class="input-xlarge" type="text" id="nome_bd_laranja_nat" name="nome_bd_laranja_nat" value="<?php if(isset($row['nome_bd']))echo $row['nome_bd'];else echo "laranja"; ?>" maxlength="20" placeholder="" datamask="" <?php if(is_numeric($_GET['id']))echo "disabled";?>/></div>
                     </div>
                     <?php
                  }?>
                         
                          
                                 

              <div class="control-group">
                <div class="controls">
                  <button type="submit" name="enviar" class="btn btn-large btn-primary">Salvar</button>
                </div>
              </div>
            </form>

        <?php

    }
    desconectaBD($db);
}
include "footer.php";
?>

