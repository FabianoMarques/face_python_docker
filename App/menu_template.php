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
      <a class="navbar-brand" href="menu.php"><strong>Menu</strong></a>
    </div>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navbar-collapse">
      <ul class="nav navbar-nav">
        <li><a href="cad_plano.php">PLANOS</a></li>
        <li><a href="cad_paciente.php">PACIENTES</a></li>
        <li><a href="relatorio.php">RELATÓRIOS</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="logout.php" class="text-danger">SAIR <i class="fas fa-sign-out-alt"></i></a></li>
      </ul>
    </div>
  </div>
</nav>
