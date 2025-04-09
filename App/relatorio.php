<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

// Verifica se foi enviado o filtro de mês
$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : '';

// Inicializa a cláusula de filtro
$filtro_sql = '';
$params = [];

if (!empty($filtro_mes)) {
    $filtro_sql = " AND DATE_FORMAT(consultas.dt_consulta, '%Y-%m') = ?";
    $params[] = $filtro_mes;
}

// Consulta SQL com filtro e JOINs
$sql = "
SELECT 
    paciente.nome AS nome_paciente,
    planos.nome AS nome_plano,
    planos.valor_hora,
    consultas.qtd_horas_feitas,
    consultas.dt_consulta,
    (planos.valor_hora * consultas.qtd_horas_feitas) AS total
FROM consultas
INNER JOIN paciente ON consultas.idpaciente = paciente.idpaciente
INNER JOIN planos ON paciente.idplano = planos.idplano
WHERE consultas.qtd_horas_feitas = (
    SELECT MAX(qtd_horas_feitas) FROM consultas
)
$filtro_sql
ORDER BY paciente.nome
";

// Preparar e executar a query com ou sem parâmetro
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param("s", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Consultas</title>
    <link rel="stylesheet" href="botoes.css">
    <link rel="stylesheet" href="estilo-relatorio.css">
</head>
<body>

<div class="container">
    <h2>RELATÓRIO DE CONSULTAS</h2>

    <form method="get" style="margin-bottom: 20px;">
        <label for="mes">Filtrar por mês:</label>
        <input type="month" name="mes" id="mes" value="<?= htmlspecialchars($filtro_mes) ?>">
        <button type="submit">Filtrar</button>
    </form>

    <div class="button-row" style="margin-bottom: -5px; display: flex; justify-content: center; gap: 600px;">
        <button onclick="location.href='menu.php'"><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>

    <table style="width: 1000px;">
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Plano</th>
                <th>Valor da Hora</th>
                <th>Horas Feitas (Consulta)</th>
                <th>Ultima Consulta</th>
                <th>Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome_paciente']) ?></td>
                        <td><?= htmlspecialchars($row['nome_plano']) ?></td>
                        <td>R$ <?= number_format($row['valor_hora'], 2, ',', '.') ?></td>
                        <td><?= $row['qtd_horas_feitas'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['dt_consulta'])) ?></td>
                        <td>R$ <?= number_format($row['total'], 2, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nenhum dado encontrado para o mês selecionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
