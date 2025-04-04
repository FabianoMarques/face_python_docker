<?php
require_once '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = (new Database())->getConnection();

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $profissional = trim($_POST['profissional']);

    if (!empty($nome) && !empty($cpf) && !empty($profissional)) {
        $stmt = $conn->prepare("INSERT INTO paciente (nome, CPF, profissional) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $cpf, $profissional);

        if ($stmt->execute()) {
            $mensagem = "✅ Paciente cadastrado com sucesso!";
        } else {
            $mensagem = "❌ Erro ao cadastrar paciente: " . $stmt->error;
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 50%;
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .buttons {
            text-align: center;
            margin-top: 20px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-btn {
            background-color: #555;
        }

        .back-btn:hover {
            background-color: #444;
        }

        .message {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fa fa-user-plus"></i> Cadastro de Paciente</h2>

    <?php if (!empty($mensagem)) echo "<p class='message'>$mensagem</p>"; ?>

    <form method="post">
        <div class="form-group">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required>
        </div>

        <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required>
        </div>

        <div class="form-group">
            <label for="profissional">Profissional Responsável:</label>
            <input type="text" id="profissional" name="profissional" required>
        </div>

        <div class="buttons">
            <button type="submit"><i class="fa fa-save"></i> Salvar</button>
            <a href="menu.php"><button type="button" class="back-btn"><i class="fa fa-arrow-left"></i> Voltar</button></a>
        </div>
    </form>
</div>

</body>
</html>
