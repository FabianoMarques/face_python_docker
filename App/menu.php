<?php
include("valida.php");
require_once '../db.php';

// Criar uma instância da classe Database
$db = new Database();
$conn = $db->getConnection();

// Fazer uma consulta
$result = $conn->query( "SELECT * FROM usuario WHERE email = '{$_SESSION['usuario']}'");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $_SESSION['usuario'] = $row["nome"];
        $_SESSION['email'] = $row["email"];
        $_SESSION['empresa'] = $row["empresa"];
        $_SESSION['logo'] = $row["logo"];
        //echo "Nome: " . htmlspecialchars($row["nome"]). " - Email: " . htmlspecialchars($row["email"])." \n" ;
    }
}

// Fechar a conexão
$db->closeConnection();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Imagens</title>
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="botoes.css">

</head>
<body>
  
    <div class="container">

        <img src="<?php  echo $_SESSION['logo']; ?>" alt="Logomarca da Clínica">

        <?php
    
        
        echo "<h2 style='margin-top:10px'> Olá ".$_SESSION['usuario']."</h2>";
        //echo "<h4 style='margin-top: -20px; padding: 10px;'>(".$_SESSION['empresa'].")</h4>";
        ?>
        <!-- <h2>GERENCIAR IMAGENS</h2> -->
        <p><?php echo htmlspecialchars($usuario); ?></p>
        <button class="btn_verde" onclick="window.open('/App/', '_blank')">Ler Imagem</button>
        <button class="btn_verde" onclick="window.location.href='/App/cad_imagem.php'">Cadastrar Imagem</button>
        <button class="btn_vermelho" onclick="window.location.href='/App/exc_imagem.php'">Excluir Imagem</button>
        <button class="btn_branco" onclick="window.location.href='/App/cad_paciente.php'">Cadastrar Paciente</button>
        <button class="btn_branco" onclick="window.location.href='/App/consultas.php'">Consultas</button>
        <button class="btn_branco" onclick="window.location.href='/App/logout.php'"><i class="fas fa-sign-out-alt"></i>  Sair</button>
    </div>

</body>
</html>
