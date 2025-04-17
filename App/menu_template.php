<head>
  <link rel="icon" type="image/png" href="favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg" />
  <link rel="shortcut icon" href="favicon/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png" />
  <link rel="manifest" href="favicon/site.webmanifest" />
</head>

<title>VERIFICAFACE</title>
<link rel="stylesheet" href="bootstrap-3.4/css/bootstrap.min.css">
<script src="bootstrap-3.4/js/bootstrap.bundle.min.js"></script>
<!-- menu.php -->
<nav class="navbar navbar-default" style="background-color: #fff; border-bottom: 2px solid #ddd; font-size: 13px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
  <div class="container-fluid">
    <!-- Logo e botão de toggle para mobile -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="menu.php"><strong><span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span></strong></a>
    </div>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navbar-collapse">
      <ul class="nav navbar-nav">
        <li><a href="cad_plano.php">PLANOS</a></li>
        <li><a href="cad_paciente.php">PACIENTES</a></li>
        <li><a href="relatorio.php">RELATÓRIOS</a></li>
        <li><a href="historico.php">HISTORICO</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="logout.php" class="text-danger">SAIR <i class="fas fa-sign-out-alt"></i></a></li>
      </ul>
    </div>
  </div>
</nav>
