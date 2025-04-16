<?php
include("validar.php");
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
    <!-- Bootstrap -->
    <link rel="stylesheet" href="bootstrap-3.4/css/bootstrap.min.css">

</head>
<body>
        <div class="container">
        <div class="row" style="padding:50px">
            <h2 style="text-align: center;"><b><?php echo $idplano ? "Editar Plano" : "Cadastrar Novo Plano"; ?></b></h2>

            <form method="POST" action="form-plano.php" class="p-4 border rounded bg-light">
                <input type="hidden" name="idplano" value="<?php echo $idplano; ?>">

                <div class="form-group">
                    <label for="nome" class="form-label">Nome do Plano:</label>
                    <input type="text" class="form-control" name="nome" id="nome" required value="<?php echo htmlspecialchars($nome); ?>">
                </div>

                <div class="form-group">
                    <label for="valor" class="form-label">Valor do Plano (R$):</label>
                    <input type="number" step="0.01" class="form-control" name="valor" id="valor" required value="<?php echo htmlspecialchars($valor); ?>">
                </div>

                <div class="form-group">
                    <label for="numero_aulas" class="form-label">Número de Aulas:</label>
                    <input type="number" class="form-control" name="numero_aulas" id="numero_aulas" required value="<?php echo htmlspecialchars($numero_aulas); ?>">
                </div>

                <div class="form-group">
                    <label for="percentual" class="form-label">Percentual (%):</label>
                    <input type="number" class="form-control" name="percentual" id="percentual" required value="<?php echo htmlspecialchars($percentual); ?>">
                </div>

                
                <!-- Botões lado a lado -->
                <div class="form-group">
                    <button type="button" class="btn btn-default" onclick="window.location.href='cad_plano.php'" style="margin-left: 10px;">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Voltar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <?php echo $idplano ? "Atualizar" : "Cadastrar"; ?>
                    </button>
                   
                </div>
            </form>
        </div>
        </div>

    <script src="bootstrap-3.4/js/bootstrap.bundle.min.js"></script>

</body>
</html>
