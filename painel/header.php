<?php include ('../config.php');

//Para uso de mudança de conteudo estando na web ou local
if($_GLOBAL['servidor'] == 0)$servidor = 0;
else $servidor = 1;

if(isset($_POST['login']))
{
    $admin = verificaColaborador($_POST['usuario'], $_POST['senha']);
    
    if($admin != false) {
        $_SESSION['usuario'] = $_POST['usuario'];
        $_SESSION['admin'] = $admin;  
        echo "<script language=\"javascript\" type=\"text/javascript\">window.location = \"./empresas.php\";</script>";
    }
    else
      echo "<script type=\"text/javascript\" language=\"javascript\"> alert('Verifique seu usuário e senha e tente novamente.') </script>";
}

if(isset($_GET['logout']))
{
	  unset($_SESSION['usuario']);
    unset($_SESSION['admin']);

    echo "<script language=\"javascript\" type=\"text/javascript\">window.location = \"../\";</script>";
}

if(isset($_GET['administrar'])) {
    $_SESSION['empresa_admin'] = $_GET['administrar'];
}

if((isset($_GET['administrar']) && $_GET['administrar'] == -1) || !isset($_SESSION['empresa_admin']) || isset($_GET['unset'])){
  $_SESSION['perfil'] = -1;
  $_SESSION['pk_equipamento_bd'] = -1;
}

if(isset($_GET['set']) && is_numeric($_GET['set'])){
  $_SESSION['perfil'] = $_GET['set'];
  $_SESSION['pk_equipamento_bd'] = equipamento_perfil($_SESSION['perfil']);
}

if(!isset($_SESSION['perfil']) || $_SESSION['perfil'] == -1){
  $tipo_equipamento = "0";
  $nome_equipamento = "Base";
}
else{
  $tipo_equipamento = "1";
  $nome_equipamento = "Nó";      
} 

$admin = $_SESSION['admin'];

?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="pt-br">
<head>
<title>Supê Soluções</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="js/jquery.js"></script>

<link rel="stylesheet" type="text/css" href="css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
</head>

<body>

 <?php if($_SESSION['admin'] > 0) { ?>

    <div class="container">
      <div class="navbar">
          <div class="navbar-inner">
                  <ul class="nav">
                    <?php $arquivo_nome = $pagina = end(explode("/", $_SERVER['PHP_SELF'])); ?>
                    <?php 
                    if($_SESSION['empresa_admin'] > 0){ 
                      ?>
                      <li <?php if($arquivo_nome=="equipamentos.php") echo 'class="active"'; ?>><a href="equipamentos.php"><?php echo $nome_equipamento."s";?></a></li> 
                      <?php
                      if(!isset($_SESSION['perfil']) || $_SESSION['perfil'] == -1){ ?>
                          <li <?php if($arquivo_nome=="estufas.php") echo 'class="active"'; ?>><a href="estufas.php">Estufas</a></li>
                          <li <?php if($arquivo_nome=="culturas.php") echo 'class="active"'; ?>><a href="culturas.php">Culturas</a></li>                   
                          <li <?php if($arquivo_nome=="perfis.php") echo 'class="active"'; ?>><a href="perfis.php">Perfis</a></li>                    
                        <?php
                      }?>                      
                      <?php
                      //SE NÃO TIVER PERFIL SELECIONADO, **NÃO** EXIBE OS ITENS QUE NECESSITAM TER PERFIL
                      if(!isset($_SESSION['perfil']) || $_SESSION['perfil'] != -1){ ?>
                        <li <?php if($arquivo_nome=="acionadores.php") echo 'class="active"'; ?>><a href="acionadores.php">Acionadores</a></li>
                        <li <?php if($arquivo_nome=="sensores.php") echo 'class="active"'; ?>><a href="sensores.php">Sensores</a></li>
                        <li <?php if($arquivo_nome=="controladores.php") echo 'class="active"'; ?>><a href="controladores.php">Controladores</a></li>
                        <li <?php if($arquivo_nome=="alertas.php") echo 'class="active"'; ?>><a href="alertas.php">Alertas</a></li>
                        <li <?php if($arquivo_nome=="cameras.php") echo 'class="active"'; ?>><a href="cameras.php">Câmeras</a></li>
                        <?php
                      } ?>
                      
                      <?php 
                    }else{
                      ?>
                      <li <?php if($arquivo_nome=="empresas.php") echo 'class="active"'; ?>><a href="empresas.php">Empresas</a></li>
                      
                      <?php
                    } ?>

                  </ul>
                  <ul class="nav pull-right">
                    <?php
                    if($_SESSION['perfil'] != -1){ 
                        echo '<li><a href="perfis.php?unset">Sair do <b>Perfil '.$_SESSION['perfil'].'</b></a></li>';
                    }
                    ?>
                    <li class="divider-vertical"></li>
                    <?php
                    if($_SESSION['empresa_admin'] > 0){ ?>
                      <li <?php if($arquivo_nome=="usuarios.php") echo 'class="active"'; ?>><a href="usuarios.php">Usuários</a></li>
                      <?php
                      echo '<li><a href="empresas.php?administrar=-1">Voltar ao Admin Geral</a></li>';

                    }else{?>
                      <li <?php if($arquivo_nome=="admins.php") echo 'class="active"'; ?>><a href="admins.php">Administradores</a></li>
                      <?php
                      echo '<li><a href="?logout">Logout</a></li>';
                    }
                    ?>
                  </ul>
          </div>
      </div><!-- /.navbar -->



        
        
 <?php } else { ?>
      <style type="text/css">
          body {
            padding-top: 10%;
            padding-bottom: 40px;
            background-color: #f5f5f5;
          }

          .form-signin {
            max-width: 300px;
            padding: 19px 29px 29px;
            margin: 0 auto 20px;
            background-color: #fff;
            border: 1px solid #e5e5e5;
            -webkit-border-radius: 5px;
               -moz-border-radius: 5px;
                    border-radius: 5px;
            -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
               -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                    box-shadow: 0 1px 2px rgba(0,0,0,.05);
          }
          .form-signin .form-signin-heading,
          .form-signin .checkbox {
            margin-bottom: 10px;
          }
          .form-signin input[type="text"],
          .form-signin input[type="password"] {
            font-size: 16px;
            height: auto;
            margin-bottom: 15px;
            padding: 7px 9px;
          }

        </style>
    
        <form class="form-signin" action="" method="post">
          <h2 class="form-signin-heading">Login</h2>
          <div class="control-group">
            <label class="control-label" for="usuario">Login</label>
            <div class="controls">
              <input type="text" id="usuario" class="input-block-level" name="usuario" placeholder="Usuário">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="senha">Senha</label>
            <div class="controls">
              <input type="password" id="senha" class="input-block-level" name="senha" placeholder="Password">
            </div>
          </div>
          <div class="control-group">
            <div class="controls">
              <button type="submit" name="login" class="btn btn-large btn-primary">Entrar</button>
            </div>
          </div>
        </form>
    
<?php } ?>
