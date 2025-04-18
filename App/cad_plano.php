<?php
include("valida.php");
include("menu_template.php") ;
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
    
    
</head>
<body>
    <div class="container">
        <h2 style="text-align: center;"><b>PLANOS CADASTRADOS</b></h2>

        <div class="button-row" style="margin-bottom:15px; text-align: center; margin: 30px;">
            <button onclick="window.location.href='form-plano.php'" class="btn btn-success" >
                        <i class="fa fa-plus"></i> Adicionar plano
            </button>
        </div>

        <div style="background-color: #fff8c4; border-left: 6px solid #f1c40f; padding: 10px 15px; border-radius: 8px; font-family: sans-serif; display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
            <span style="font-size: 20px; color: #f39c12;">⚠️</span>
            <span style="color: #444; font-size: 14px;">
            <strong>Atenção:</strong> Se for <b>atualizar</b> o <b>preços</b> dos planos, <a href="relatorio.php" style="color: #c0392b; text-decoration: underline;">clique aqui</a> para gerar um relatório antes e preservar os valores antigos.
            </span>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                     <th>Valor/Plano</th>
                    <th>Valor/Hora *</th>
                    <th>Quantidade de Aulas</th>
                    <th>% **</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { 
                    $valorHora = ($row["numero_aulas"] > 0) ? $row["valor"] / $row["numero_aulas"] : 0;
                ?>
                    <tr>
                        <td><?php echo $row["idplano"]; ?></td>
                        <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                        <td>R$ <?php echo number_format($row["valor"], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($valorHora, 2, ',', '.'); ?></td>
                        <td><?php echo $row["numero_aulas"]; ?></td>
                        <td><?php echo $row["percentual"]; ?></td>
                        <td>
                            <a class="btn btn-warning" href="form-plano.php?editar=<?php echo $row['idplano']; ?>">Editar</a>
                            <a class="btn btn-danger" href="cad_plano.php?excluir=<?php echo $row['idplano']; ?>" onclick="return confirm('Deseja excluir este plano?');">Excluir</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <p style="text-align: left;">
            * Valor do plano / quantidade de aulas <br>
            ** Percentual referente a hora do colaborador 
        </p>
    </div>
</body>
</html>
