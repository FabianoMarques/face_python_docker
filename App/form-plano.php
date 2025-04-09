<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

$nome = "";
$valor_hora = "";
$idplano = "";

// Edição: carregar dados existentes
if (isset($_GET["editar"])) {
    $idplano = $_GET["editar"];
    $sql = "SELECT * FROM planos WHERE idplano = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idplano);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = $result->fetch_assoc();

    $nome = $dados["nome"];
    $valor_hora = $dados["valor_hora"];
    $stmt->close();
}

// Submissão do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idplano = $_POST["idplano"];
    $nome = $_POST["nome"];
    $valor_hora = $_POST["valor_hora"];

    if (!empty($idplano)) {
        $sql = "UPDATE planos SET nome = ?, valor_hora = ? WHERE idplano = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdi", $nome, $valor_hora, $idplano);
    } else {
        $sql = "INSERT INTO planos (nome, valor_hora) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sd", $nome, $valor_hora);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: cad_plano.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $idplano ? "Editar Plano" : "Novo Plano"; ?></title>
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="botoes.css">
    <style>
        .container {
            max-width: 500px;
            margin: auto;
        }

        label, input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        button {
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div class="container" >
        <h2><?php echo $idplano ? "Editar Plano" : "Cadastrar Novo Plano"; ?></h2>

        <form method="POST" action="form-plano.php" style="text-align: left;">
            <input type="hidden" name="idplano" value="<?php echo $idplano; ?>">

            <label for="nome">Nome do Plano:</label>
            <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome); ?>" style="text-align: left;">

            <label for="valor_hora">Valor por Hora (R$):</label>
            <input type="number" step="0.01" name="valor_hora" required value="<?php echo htmlspecialchars($valor_hora); ?>" style="text-align: left;">

            <button type="submit" class="btn_verde"><?php echo $idplano ? "Atualizar" : "Cadastrar"; ?></button>
        </form>
        <button type="button" class="btn_branco" onclick="window.location.href='cad_plano.php'">Voltar</button>

    </div>
</body>
</html>
