<?php
include "header.php";

if($_SESSION['permissao'] == 10)
{
	
	$db = conectaBD();
 
	if($_SESSION['permissao'] == 10)
	{
			?>
			<h3>Últimos acessos</h3>
            
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>IP</th>
						<th>Usuário</th>
						<th>Data</th>
					</tr>
				</thead>
				<tbody>
		            <?php

					$query="SELECT * FROM `log_acesso` INNER JOIN logins ON log_acesso.usuario = logins.usuario ORDER BY data DESC LIMIT 10";   
				    $result = mysql_query($query);
				    
					while ($row = mysql_fetch_array($result)) {
							$usuario = $row['usuario'];
							if($row['permissao']==1){
								$query="SELECT nome FROM alunos where ra='$usuario'";
								$result2 = mysql_query($query);
				    			$row2 = mysql_fetch_array($result2);
				    			$nome = $row2['nome'];
							}
							else if($row['permissao']==2){
								$query="SELECT nome FROM professores where rp='$usuario'";
								$result2 = mysql_query($query);
				    			$row2 = mysql_fetch_array($result2);
				    			$nome = $row2['nome'];
							}
							else $nome="Administrador";   
				    		

						    echo "<tr>
									<td style='width:37%;'>".$row['ip']."</td>
									
									<td style='width:37%;'>";
									if($row['status'] == 1) echo "<span class='label label-success'>";
									else if($row['status'] == 0) echo "<span class='label label-warning'>";
									else echo "<span class='label label-important'>";
									echo $nome."</span></td>
									<td style='width:25%;'>".date('d/m/Y H:i', strtotime($row['data']))."</td>
								  </tr> ";
					}
					
					?>
				</tbody>
	        </table>

<?php
	}
	desconectaBD($db);

include "footer.php";	
}
else if($_SESSION['permissao'] == 5) echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./monitoramento.php'>";
else echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=./turmas.php'>";
?>
