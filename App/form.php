<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

$id = $nome = $cpf = $profissional = "";

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
    }
    $stmt->close();
}

// Cadastrar ou atualizar paciente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $profissional = trim($_POST['profissional']);

    if (!empty($nome) && !empty($cpf) && !empty($profissional)) {
        if (!empty($id)) {
            // Atualizar
            $stmt = $conn->prepare("UPDATE paciente SET nome=?, CPF=?, profissional=? WHERE idpaciente=?");
            $stmt->bind_param("sssi", $nome, $cpf, $profissional, $id);
            $mensagem = $stmt->execute() ? "✅ Paciente atualizado com sucesso!" : "❌ Erro ao atualizar: " . $stmt->error;
        } else {
            // Cadastrar
            $stmt = $conn->prepare("INSERT INTO paciente (nome, CPF, profissional) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $cpf, $profissional);
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
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; display: flex; flex-direction: column; align-items: center; }
        .container { width: 50%; background: #fff; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; }
        input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        .buttons { text-align: center; margin-top: 20px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; font-size: 16px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
        .back-btn { background: #555; }
        .back-btn:hover { background: #444; }
        .message { text-align: center; margin-top: 10px; font-weight: bold; color: red; }

        .back-btn {
            display: inline-block;
            background-color: #555;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-btn:hover {
            background-color: #444;
        }


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
        <div class="buttons">
            <button type="submit">Salvar</button>
            <a href="menu.php" class="back-btn"><i class="fa fa-arrow-left"></i> Voltar</a>
        </div>
    </form>
</div>

</body>
</html>
