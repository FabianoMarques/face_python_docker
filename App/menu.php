<?php
include("valida.php");

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Imagens</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }
        button {
            width: 100%;
            padding: 12px; /* Aumentado */
            margin-top: 12px; /* Aumentado */
            font-size: 18px; /* Aumentado */
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #45a049;
            color: white;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Gerenciar Imagens</h2>
        <p><?php echo htmlspecialchars($usuario); ?></p>
        <button class="btn-primary" onclick="window.location.href='/App/'">Ler Imagem</button>
        <button class="btn-primary" onclick="window.location.href='/App/cad_imagem.php'">Cadastrar Imagem</button>
        <button class="btn-danger" onclick="window.location.href='/App/exc_imagem.php'">Excluir Imagem</button>
        <button class="btn-default" onclick="window.location.href='/App/logout.php'">Sair</button>
    </div>

</body>
</html>
