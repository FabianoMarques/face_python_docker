<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

$id = $nome = $cpf = $profissional = $email = $idplano = $vencimento = $status = "";
$vencimento = "";

// Buscar todos os planos
$planos = [];
$resultPlanos = $conn->query("SELECT idplano, nome FROM planos");
while ($row = $resultPlanos->fetch_assoc()) {
    $planos[$row['idplano']] = $row['nome'];
}

// Verificar se é edição
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $vencimento = $result['vencimento'];
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

// Cadastrar ou atualizar paciente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $vencimento = intval($_POST['vencimento']);
    $idplano = trim($_POST['idplano']);
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $profissional = trim($_POST['profissional']);
    $vencimento = trim($_POST['vencimento']);
    $status = trim($_POST['status']);

    if (!empty($nome) && !empty($cpf) && !empty($email) && !empty($profissional) && !empty($idplano) && !empty($vencimento) && !empty($status)) {
        if (!empty($id)) {
            // Atualizar
            $stmt = $conn->prepare("UPDATE paciente SET idplano=?, nome=?, CPF=?, email=?, profissional=?, vencimento=?, status=? WHERE idpaciente=?");
            $stmt->bind_param("issssisi", $idplano, $nome, $cpf, $email, $profissional, $vencimento, $status, $id);

            $mensagem = $stmt->execute() ? "✅ Paciente atualizado com sucesso!" : "❌ Erro ao atualizar: " . $stmt->error;
        } else {
            // Cadastrar
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Paciente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="botoes.css">
    <link rel="stylesheet" href="estilo.css">
    <style>
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; text-align: left;}
        input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <h2><?= $id ? 'Editar' : 'Cadastrar' ?> Paciente</h2>
    <?php if (!empty($mensagem)) echo "<p class='message'>$mensagem</p>"; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="form-group">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= $nome ?>" required>
        </div>
        
        <div class="form-group">
            <label>CPF:</label>
            <input type="text" name="cpf" value="<?= $cpf ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= $email ?>" required>
        </div>
        
        <div class="form-group">
            <label>Profissional:</label>
            <input type="text" name="profissional" value="<?= $profissional ?>" required>
        </div>
        
        <div class="form-group">
            <label>Plano:</label>
            <select name="idplano" required>
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

        <div>
            <button type="submit" class="btn_verde">Salvar</button>
            <button type="button" onclick="window.location.href='cad_paciente.php'" class="btn_branco">Voltar</button>
        </div>

        
    </form>
</div>

</body>
</html>
