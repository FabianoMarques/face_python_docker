<?php
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "Desconhecido";
$mensagem = isset($_GET['mensagem']) ? htmlspecialchars($_GET['mensagem']) : "";
$score = isset($_GET['score']) ? htmlspecialchars($_GET['score']) : "";
$imagem = isset($_GET['imagem']) ? htmlspecialchars($_GET['imagem']) : "";
$nomeArquivo = !empty($imagem) ? pathinfo(basename($imagem), PATHINFO_FILENAME) : "";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Verificação</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f9;
            height: 100vh;
            margin: 0;
        }
        h2 {
            color: #333;
        }
        .result-container {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }
        .image-container {
            margin-right: 15px;
        }
        .image-container img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .result-text {
            flex: 1;
            text-align: left;
        }
        .error {
            color: red;
            font-size: 18px;
        }
        .success {
            color: green;
            font-size: 18px;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h2>Resultado da Verificação Facial</h2>

    <div class="result-container">
        <?php if (!empty($imagem)): ?>
            <div class="image-container">
                <img src="<?php echo $imagem; ?>" alt="Imagem correspondente">
            </div>
        <?php endif; ?>
        <div class="result-text">
            <?php if ($status == "erro"): ?>
                <p class="error"><strong>Erro:</strong> <?php echo $mensagem; ?></p>
            <?php else: ?>
                <p class="success"><strong>Status:</strong> <?php echo $status; ?></p>
                <p><strong>Score de Semelhança:</strong> <?php echo $score; ?></p>
                <?php if (!empty($nomeArquivo)): ?>
                    <p><strong>CPF:</strong> <?php echo $nomeArquivo; ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <a href="./" class="back-link">Tentar Novamente</a>

</body>
</html>