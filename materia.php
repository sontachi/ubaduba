<?php 
include "header.php";
if($_SESSION['permissao'] > 0)
{
	
		
	if(is_numeric($_GET['id'])){
		$db = conectaBD();
		$query = "SELECT * FROM materias WHERE pk_materia = ".$_GET['id'];
		$result = mysql_query($query);
		desconectaBD($db);
		$row = mysql_fetch_array($result);
	}
?>

	<?php if(isset($aviso_erro) && $aviso_erro != "")
		echo '<div class="alert"><button type="button" class="close" data-dismiss="alert">×</button>' . $aviso_erro . '</div>';
	?>

	<div class="row">
		<div class="span8">
			<h3>Matéria</h3><br/>
			<div class="row">
				<div class="span4">
					<address>
					  <strong><?php echo $row['nome'];?></strong><br>
					</address>		
					<br>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		
	</div>


<?php 
}

include "footer.php";

?>
