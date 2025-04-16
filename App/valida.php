
<?php
session_start(); // Inicia a sessão

// Verifica se a variável de sessão está definida
if (!isset($_SESSION['usuario'])) {
    // Redireciona para a página de login
    header("Location: /");
    exit(); // Encerra o script para evitar execução indevida
}

// Se a sessão estiver ativa, o código abaixo será executado normalmente
//echo "Bem-vindo, " . $_SESSION['usuario'];
?>

<link rel="stylesheet" href="bootstrap-3.4/css/bootstrap.min.css">
<script src="bootstrap-3.4/js/bootstrap.bundle.min.js"></script>
