<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

$nome = "";
$valor = "";
$numero_aulas = "";
$percentual = "";
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
    $valor = $dados["valor"];
    $numero_aulas = $dados["numero_aulas"];
    $percentual = $dados["percentual"];
    $stmt->close();
}

// Submissão do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idplano = $_POST["idplano"];
    $nome = $_POST["nome"];
    $valor = $_POST["valor"];
    $numero_aulas = $_POST["numero_aulas"];
    $percentual = $_POST["percentual"];

    if (!empty($idplano)) {
        $sql = "UPDATE planos SET nome = ?, valor = ?, numero_aulas = ?, percentual = ? WHERE idplano = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdiii", $nome, $valor, $numero_aulas, $percentual, $idplano);
    } else {
        $sql = "INSERT INTO planos (nome, valor, numero_aulas, percentual) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdii", $nome, $valor, $numero_aulas, $percentual);
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
    <div class="container" style="text-align: left;">
        <h2 style="text-align: center;"><?php echo $idplano ? "Editar Plano" : "Cadastrar Novo Plano"; ?></h2>

        <form method="POST" action="form-plano.php">
            <input type="hidden" name="idplano" value="<?php echo $idplano; ?>">

            <label for="nome">Nome do Plano:</label>
            <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome); ?>">

            <label for="valor">Valor do Plano (R$):</label>
            <input type="number" step="0.01" name="valor" required value="<?php echo htmlspecialchars($valor); ?>">

            <label for="numero_aulas">Número de Aulas:</label>
            <input type="number" name="numero_aulas" required value="<?php echo htmlspecialchars($numero_aulas); ?>">

            <label for="percentual">Percentual (%):</label>
            <input type="number" name="percentual" required value="<?php echo htmlspecialchars($percentual); ?>">

            <button type="submit" class="btn_verde"><?php echo $idplano ? "Atualizar" : "Cadastrar"; ?></button>
        </form>

        <button type="button" class="btn_branco" onclick="window.location.href='cad_plano.php'">Voltar</button>
    </div>
</body>
</html>
