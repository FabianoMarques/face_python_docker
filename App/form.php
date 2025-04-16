<?php
include("valida.php");
require_once '../db.php';
$conn = (new Database())->getConnection();

$id = $nome = $cpf = $profissional = $email = $idplano = $vencimento = $status = "";

// Buscar planos
$planos = [];
$resultPlanos = $conn->query("SELECT idplano, nome FROM planos");
while ($row = $resultPlanos->fetch_assoc()) {
    $planos[$row['idplano']] = $row['nome'];
}

// Se for edição
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM paciente WHERE idpaciente=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $idplano = $result['idplano'];
        $nome = $result['nome'];
        $cpf = $result['CPF'];
        $email = $result['email'];
        $profissional = $result['profissional'];
        $vencimento = $result['vencimento'];
        $status = $result['status'];
    }
    $stmt->close();
}

// Cadastro ou atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $vencimento = intval($_POST['vencimento']);
    $idplano = trim($_POST['idplano']);
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $profissional = trim($_POST['profissional']);
    $status = trim($_POST['status']);

    if (!empty($nome) && !empty($cpf) && !empty($email) && !empty($profissional) && !empty($idplano) && !empty($vencimento) && !empty($status)) {
        if (!empty($id)) {
            $stmt = $conn->prepare("UPDATE paciente SET email=?, vencimento=?, status=? WHERE idpaciente=?");
            $stmt->bind_param("sisi", $email, $vencimento, $status, $id);
            $mensagem = $stmt->execute() ? "✅ Paciente atualizado com sucesso!" : "❌ Erro ao atualizar: " . $stmt->error;
        } else {
            $stmt = $conn->prepare("INSERT INTO paciente (idplano, nome, CPF, email, profissional, vencimento, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssis", $idplano, $nome, $cpf, $email, $profissional, $vencimento, $status);
            $mensagem = $stmt->execute() ? "✅ Paciente cadastrado com sucesso!" : "❌ Erro ao cadastrar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensagem = "⚠ Todos os campos são obrigatórios!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Paciente</title>

    <style>
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; text-align: left; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
    </style>
</head>
<body>

<div class="container">
<div class="row" style="padding:50px">
    <h2 style="text-align: center;"><?= $id ? '<b>Editar</b>' : 'Cadastrar' ?> <b>Paciente</b></h2>
    <?php if (!empty($mensagem)) echo "<p class='message'>$mensagem</p>"; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="idplano" value="<?= $idplano ?>">
        <input type="hidden" name="nome" value="<?= $nome ?>">
        <input type="hidden" name="cpf" value="<?= $cpf ?>">
        <input type="hidden" name="profissional" value="<?= $profissional ?>">

        <div class="form-group">
            <label>Nome:</label>
            <input type="text" value="<?= $nome ?>" readonly style="text-align: left;">
        </div>

        <div class="form-group">
            <label>CPF:</label>
            <input type="text" value="<?= $cpf ?>" readonly style="text-align: left;">
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= $email ?>" required style="text-align: left;">
        </div>

        <div class="form-group">
            <label>Profissional:</label>
            <input type="text" value="<?= $profissional ?>" readonly style="text-align: left;">
        </div>

        <div class="form-group">
            <label>Plano:</label>
            <select disabled>
                <option value="">Selecione um plano</option>
                <?php foreach ($planos as $idPlano => $nomePlano): ?>
                    <option value="<?= $idPlano ?>" <?= $idPlano == $idplano ? 'selected' : '' ?>><?= $nomePlano ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Dia de Vencimento:</label>
            <select name="vencimento" required>
                <option value="">Selecione o dia</option>
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option value="<?= $i ?>" <?= $vencimento == $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status:</label>
            <select name="status" required>
                <option value="">Selecione o status</option>
                <option value="Ativo" <?= $status == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="Inativo" <?= $status == 'Inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <div class="form-group">
                <button type="button" onclick="window.location.href='cad_paciente.php'" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Voltar</button>
                <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </form>

</div>
</div>

</body>
</html>
