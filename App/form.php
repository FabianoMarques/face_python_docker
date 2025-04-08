<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

$id = $nome = $cpf = $profissional = $email = "";

// Verificar se é edição
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM paciente WHERE idpaciente=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $nome = $result['nome'];
        $cpf = $result['CPF'];
        $profissional = $result['profissional'];
        $email = $result['email']; // novo campo
    }
    $stmt->close();
}

// Cadastrar ou atualizar paciente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $profissional = trim($_POST['profissional']);
    $email = trim($_POST['email']);

    if (!empty($nome) && !empty($cpf) && !empty($profissional) && !empty($email)) {
        if (!empty($id)) {
            // Atualizar
            $stmt = $conn->prepare("UPDATE paciente SET nome=?, CPF=?, profissional=?, email=? WHERE idpaciente=?");
            $stmt->bind_param("ssssi", $nome, $cpf, $profissional, $email, $id);
            $mensagem = $stmt->execute() ? "✅ Paciente atualizado com sucesso!" : "❌ Erro ao atualizar: " . $stmt->error;
        } else {
            // Cadastrar
            $stmt = $conn->prepare("INSERT INTO paciente (nome, CPF, profissional, email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $cpf, $profissional, $email);
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
        input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; text-align: left; }
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
            <label>Profissional:</label>
            <input type="text" name="profissional" value="<?= $profissional ?>" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= $email ?>" required>
        </div>
        <div>
            <button type="submit" class="btn_verde">Salvar</button>
            <button type="button" onclick="window.location.href='cad_paciente.php'" class="btn_branco">Voltar</button>
        </div>
    </form>
</div>

</body>
</html>
