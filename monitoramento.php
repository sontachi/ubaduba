<?php 

include "header.php";

if($_SESSION['permissao'] >= 5)
{

  ?>

  <!--TABLE AJAX-->
  <script type="text/javascript">
    
    function updateTable(series){
      var i = 0;
      var valor;
      var data;
      
      for(; i<series.data.length; i++){
        valor = series.data[i][1] + series.data[i][3];
        data = series.data[i][2];
      
      if(document.getElementById("valor_"+series.data[i][0]).innerHTML > valor){  
        document.getElementById("valor_"+series.data[i][0]).style.color="#ff0000";
      }
      else if(document.getElementById("valor_"+series.data[i][0]).innerHTML < valor){ 
        document.getElementById("valor_"+series.data[i][0]).style.color="#00FF00";
      }
      else{
        document.getElementById("valor_"+series.data[i][0]).style.color="#000"; 
      }
        document.getElementById("valor_"+series.data[i][0]).innerHTML=valor;
        document.getElementById("data_"+series.data[i][0]).innerHTML=data;
      }
    }
    function update() {
        $.ajax({
          url: "getDataSensores.php",
          type: "GET",
          dataType: "json",
          success: updateTable
        });
        setTimeout(update, 1000);
      }
      update();
  </script>

  <?php
 
    if(!isset($_GET['id']))
    {
        $db=conectaBD();  
        $query = "SELECT
                    sen.pk_sensor,
                    s.nome, 
                    tipos.descricao, tipos.pk_sensor_tipo,
                    cs.valor_lido, DATE_FORMAT(cs.data, '%d/%m/%Y às %H:%i:%s') as data
                  FROM 
                    sensores sen 
                  INNER JOIN 
                    sensores_tipos tipos
                  ON 
                    sen.fk_sensor_tipo = tipos.pk_sensor_tipo
                  INNER JOIN
                    salas s
                  ON s.pk_sala = sen.fk_sala
                  LEFT JOIN 
                    consultas_sensor cs
                  ON
                    cs.fk_sensor = sen.pk_sensor
                  WHERE
                    sen.excluido = 0
                  ORDER BY nome, tipos.descricao, intervalo_consulta";

        $result = mysql_query($query);
        desconectaBD($db);
    ?>
            <h3>Monitoramento</h3>
            
            <table class="table table-stripped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Sala</th>
                        <th>Medida</th>
                        <th>Valor Lido</th>
                        <th>Data</th>
                        <th style="width:10%">Histórico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysql_fetch_array($result)) {
                      if($row['valor_lido'] == NULL)$valor_lido = "Indefinido";
                      else $valor_lido = $row['valor_lido'] + unidade_sensor($row['fk_sensor_tipo']);

                      if($row['data'] == NULL)$data = "Indefinida";
                      else $data = $row['data'];

                      $pk_sensor = $row['pk_sensor'];
                      echo "<tr>
                              <td></td>
                              <td><span class='label label-inverse'>".$row['nome']."</span></td>
					                  	<td>".$row['descricao']."</td>
                              <td id='valor_".$pk_sensor."'>".$valor_lido."</td>
                              <td id='data_".$pk_sensor."'>".$data."</td>
                              <td><a href='sensorHistorico.php?id_sensor=".$pk_sensor."'><img style='width:20px;' src='img/about.png' border='0' /></a></td>
                            </tr> ";
                    }
                    
                    ?>
                </tbody>
            </table>


            <div class="row">
              <div class="span8">
              </div>
              <div class="span4">
                  <table class="table table-stripped">
                    <thead>
                        <tr>
                            <th>Legenda</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                          <th></th>
                          <th><span class='label label-inverse'>Sem aula</span></th>
                        </tr>
                        <tr>
                          <th></th>
                          <th><span class='label label-warning'>Horário de aula</span></th>
                        </tr>
                        <tr>
                          <th></th>
                          <th><span class='label label-success'>Aula em andamento</span></th>
                        </tr>
                    </tbody>
                </table>
              </div>
            </div>
<?php
    }
    else 
    {
       ?>
        <?php

    }
   
}
include "footer.php";
?>

