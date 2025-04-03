<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "mysql"; // Forçar conexão via TCP
$user = "usuario"; // Usuário do banco de dados
$password = "senha"; // Senha do banco de dados
$database = "face_recognition_db"; // Nome do banco de dados
$port = 3306; // Porta do MySQL

// Conectar ao banco de dados
$conn = @new mysqli($host, $user, $password, $database, $port);

// Verificar conexão
if ($conn->connect_error) {
    die("<b>Erro:</b> Não foi possível conectar ao banco de dados. Verifique se o MySQL está instalado e rodando.<br>Detalhes: " . $conn->connect_error);
} else {
    echo "Conexão bem-sucedida!<br>";
}

// Receber dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["password"];

    $email = $conn->real_escape_string($email);
    $senha = $conn->real_escape_string($senha); // Sem hash

    $sql = "SELECT * FROM usuario WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($senha === $row["senha"]) { // Comparação direta
            echo "Login bem-sucedido!";
            session_start();
            $_SESSION["usuario"] = $row["email"];
            header("Location: /App/menu.php");
            exit();
        } else {
            //$_SESSION["login"] = "<h1>Usuario ou Senha incorreto</h1>";
            //echo "Senha incorreta.";
            header("Location: /index.php?aviso=<h2><b>Usuario</b> ou <b>Senha</b> incorreto</h2>");
        }
    } else {
        $_SESSION["login"] = "Usuario ou Senha incorreto";
        //echo "Usuário não encontrado.";
        header("Location: /index.php?aviso=<h2><b>Usuario</b> ou <b>Senha</b> incorreto</h2>");
    }
}

$conn->close();
?>
