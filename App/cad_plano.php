<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

// Exclusão
if (isset($_GET["excluir"])) {
    $id = $_GET["excluir"];
    $sql = "DELETE FROM planos WHERE idplano = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: cad_plano.php");
    exit;
}

// Listar todos os planos
$sql = "SELECT * FROM planos ORDER BY idplano DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planos</title>
    <link rel="stylesheet" href="estilo-relatorio.css">
    <link rel="stylesheet" href="botoes.css">
    
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .btn {
            padding: 4px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-editar {
            background-color: #4CAF50;
            color: white;
        }

        .btn-excluir {
            background-color: #f44336;
            color: white;
        }

        .btn-novo {
            background-color: #2196F3;
            color: white;
            margin-bottom: 15px;
            display: inline-block;
            padding: 8px 15px;
        }

      
    </style>
</head>
<body>
        <div class="container">
            <h2>PLANOS CADASTRADOS</h2>

            <div class="button-row" style="margin-bottom:15px">
                <button onclick="window.location.href='menu.php'" ><i class="fas fa-arrow-left"></i> Voltar</button>
                <button onclick="window.location.href='form-plano.php'" >
                            <i class="fa fa-plus"></i> Adicionar plano
                </button>
             </div>

             <div style="background-color: #fff8c4; border-left: 6px solid #f1c40f; padding: 10px 15px; border-radius: 8px; font-family: sans-serif; display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <span style="font-size: 20px; color: #f39c12;">⚠️</span>
                <span style="color: #444; font-size: 14px;">
                <strong>Atenção:</strong> Se for <b>atualizar</b> o <b>preços</b> dos planos, <a href="relatorio.php" style="color: #c0392b; text-decoration: underline;">clique aqui</a> para gerar um relatório antes e preservar os valores antigos.
                </span>
            </div>


        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Valor/Hora</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["idplano"]; ?></td>
                        <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                        <td>R$ <?php echo number_format($row["valor_hora"], 2, ',', '.'); ?></td>
                        <td>
                            <a class="btn btn-editar" href="form-plano.php?editar=<?php echo $row['idplano']; ?>">Editar</a>
                            <a class="btn btn-excluir" href="cad_plano.php?excluir=<?php echo $row['idplano']; ?>" onclick="return confirm('Deseja excluir este plano?');">Excluir</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
