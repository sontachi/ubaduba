<?php include ('config.php');

  $pagina_atual = end(explode("/", $_SERVER['PHP_SELF']));

  if(isset($_POST['login']))
  {
    if(!login($_POST['admin'], $_POST['senha'])){
      echo '<script>alert("Login ou Senha incorretos!")</script>';
    }
  }

  if(isset($_GET['logout']))
  {
     logout();
  }

  ?>

  <!DOCTYPE html>
  <html lang="pt-BR">
  <head>
      <meta charset="utf-8">
      <title>Sistema GIN</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="">
      <meta name="author" content="">

      <!-- Styles -->
      <link href="css/bootstrap.css" rel="stylesheet">

      <script src="js/jquery.js"></script>
      <script src="js/bootstrap.js"></script>
      <script src="js/bootstrap-inputmask.min.js"></script>
      <!--<script src="js/jquery.maskedinput.js"></script>-->
      <script src="js/funcoes.js"></script>
  </head>
  
  <body>

  <div class="container">
   <?php
    // Se estiver logado
    if($_SESSION['permissao'] > 0)
    { ?>
      <br/>
      <div class="navbar">
          <div class="navbar-inner">
              <div class="container">
                  <ul class="nav">
                      <li <?php if($pagina_atual=="index.php") echo 'class="active"'; ?>><a href="./">Home</a></li>
                      <li <?php if($pagina_atual=="cursos.php") echo 'class="active"'; ?>><a href="cursos.php">Cursos</a></li>
                      <li <?php if($pagina_atual=="materias.php") echo 'class="active"'; ?>><a href="materias.php">Matérias</a></li>
                      <li <?php if($pagina_atual=="professores.php") echo 'class="active"'; ?>><a href="professores.php">Professores</a></li>
                      <li <?php if($pagina_atual=="alunos.php") echo 'class="active"'; ?>><a href="alunos.php">Alunos</a></li>
                      
                  </ul>

                  <ul class="nav pull-right">
                      <li><a href="?logout">Sair</a></li>
                  </ul>
              </div>
          </div>
      </div><!-- /.navbar -->

        <!--<input class="btn btn-small" value="Voltar" onClick="JavaScript: window.history.back();" />-->
      <br clear="all" />
    
   <?php 
  } else{ ?>
      
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
              <label class="control-label" for="admin">Usuário</label>
              <div class="controls">
                <input type="text" id="admin" class="input-block-level" name="admin" value="<?php echo $_POST['admin'];?>" placeholder="Usuário">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="senha">Senha</label>
              <div class="controls">
                <input type="password" id="senha" class="input-block-level" name="senha" placeholder="Senha">
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                <button type="submit" name="login" class="btn btn-large btn-primary">Entrar</button>
              </div>
            </div>
          </form>

      
<?php } ?>