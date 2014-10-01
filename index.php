<?php
include "header.php";

if($_SESSION['permissao'] > 0)
{
	
	$db = conectaBD();
	?>

	<h4><?php echo $_SESSION['nome']; ?>, bem-vindo(a) ao painel administrativo!</h4>
	    
	<?php 
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

					$query="SELECT * FROM `log_acesso` INNER JOIN admins ON fk_admin = pk_admin ORDER BY data DESC LIMIT 10";   
				    $result = mysql_query($query);

				    
					while ($row = mysql_fetch_array($result)) {
						    echo "<tr>
									<td style='width:37%;'>".$row['ip']."</td>
									
									<td style='width:37%;'>";
									if($row['status'] == 1) echo "<span class='label label-success'>";
									else if($row['status'] == 0) echo "<span class='label label-warning'>";
									else echo "<span class='label label-important'>";
									echo $row['nome']."</span></td>
									<td style='width:25%;'>".date('d/m/Y H:i', strtotime($row['data']))."</td>
								  </tr> ";
					}
					
					?>
				</tbody>
	        </table>

<?php
	}
	desconectaBD($db);
}

include "footer.php";
?>
