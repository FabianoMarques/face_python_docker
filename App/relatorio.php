<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';
$result = false;

$sql_base = "
    SELECT 
        p.nome AS nome_paciente,
        pl.nome AS nome_plano,
        c.profissional,
        pl.valor,
        pl.numero_aulas,
        pl.percentual,
        CASE 
            WHEN pl.numero_aulas > 0 THEN pl.valor / pl.numero_aulas 
            ELSE 0 
        END AS valor_hora,
        c.qtd_horas_feitas,
        c.dt_consulta,
        CASE 
            WHEN pl.numero_aulas > 0 THEN (pl.valor / pl.numero_aulas) * (pl.percentual / 100)
            ELSE 0 
        END AS valor_aula_colaborador,
        CASE 
            WHEN pl.numero_aulas > 0 THEN (pl.valor / pl.numero_aulas) * (pl.percentual / 100) * c.qtd_horas_feitas
            ELSE 0 
        END AS total
    FROM consultas c
    INNER JOIN paciente p ON c.idpaciente = p.idpaciente
    INNER JOIN planos pl ON p.idplano = pl.idplano
    INNER JOIN (
        SELECT 
            idpaciente,
            DATE_FORMAT(dt_consulta, '%Y-%m') AS mes,
            MAX(qtd_horas_feitas) AS max_horas
        FROM consultas
        GROUP BY idpaciente, mes
    ) sub ON c.idpaciente = sub.idpaciente
    AND DATE_FORMAT(c.dt_consulta, '%Y-%m') = sub.mes
    AND c.qtd_horas_feitas = sub.max_horas
";

$params = [];
$types = '';

if (!empty($filtro_mes)) {
    $sql_base .= " AND DATE_FORMAT(c.dt_consulta, '%Y-%m') = ?";
    $params[] = $filtro_mes;
    $types .= 's';
}

if (!empty($filtro_profissional)) {
    $sql_base .= " AND c.profissional LIKE ?";
    $params[] = '%' . $filtro_profissional . '%';
    $types .= 's';
}

$sql_base .= " ORDER BY p.nome, c.dt_consulta";

$stmt = $conn->prepare($sql_base);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$percentual = null;
if ($result && $result->num_rows > 0) {
    $firstRow = $result->fetch_assoc();
    $percentual = $firstRow['percentual'];
    $result->data_seek(0); // volta ao início para o while funcionar normalmente
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Consultas</title>
    <link rel="stylesheet" href="botoes.css">
    <link rel="stylesheet" href="estilo-relatorio.css">
    <style>
        form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            font-family: Arial, sans-serif;
        }

        form label {
            font-weight: bold;
            color: #333;
        }

        form input[type="month"],
        form input[type="text"] {
            padding: 6px 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        form button {
            padding: 6px 14px;
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #45a049;
        }

        hr {
            border: none;
            height: 2px;
            background-color: #ccc;
            margin: 20px 0;
        }

        table {
            width: 1200px;
            border-collapse: collapse;
            margin: 0 auto;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 1300px;
    margin: 0 auto;
    padding: 20px;
    font-family: Arial, sans-serif;">
    <h2>CONSULTAS PARA RELATÓRIO</h2>
    <hr>
    <form method="get" style="margin-bottom: 5px;">
        <label for="mes">Filtrar por mês:</label>
        <input type="month" name="mes" id="mes" value="<?= htmlspecialchars($filtro_mes) ?>">

        <label for="profissional">Filtrar por profissional:</label>
        <input type="text" name="profissional" id="profissional" placeholder="Digite o nome" value="<?= htmlspecialchars($filtro_profissional) ?>">

        <button type="submit">Filtrar</button>
    </form>
    <hr>
    <div class="button-row" style="margin-bottom: 15px; display: flex; justify-content: center; gap: 10%;">
        <button onclick="location.href='menu.php'"><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>

        <?php if ($result && $result->num_rows > 0): ?>
            <button onclick="location.href='gerar-historico.php'"
                style="margin-top: 0px; background-color:rgb(252, 246, 168);">
                <i class="fas fa-save"></i> Gerar Histórico
            </button>
        <?php endif; ?>

        <button onclick="location.href='historico.php'"
            style="margin-top: 0px; background-color:rgb(241, 241, 241);">
            <i class="fas fa-history"></i> Ver Histórico
        </button>
    </div>
    <table style="width: 100%;">
    <thead>
    <tr>
        <th>Paciente</th>
        <th>Plano</th>
        <th>Profissional</th>
        <th>Valor/Plano (R$)</th>
        <th>Hora/Plano (R$)</th>
        <th>Horas/Colaborador *</th>
        <th>Hora/Atendimento</th>
        <th>Total Colaborador (R$)</th>
        <th>Data/Hora**</th>
    </tr>
    </thead>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nome_paciente']) ?></td>
                <td><?= htmlspecialchars($row['nome_plano']) ?></td>
                <td><?= htmlspecialchars($row['profissional']) ?></td>
                <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($row['valor_hora'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($row['valor_aula_colaborador'], 2, ',', '.') ?> (<?= intval($row['percentual']) ?>%)</td>
                <td><?= $row['qtd_horas_feitas'] ?></td>
                <td>R$ <?= number_format($row['total'], 2, ',', '.') ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['dt_consulta'])) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="9">Nenhum dado encontrado<?= $filtro_mes || $filtro_profissional ? ' para os filtros selecionados.' : '.' ?></td>
        </tr>
    <?php endif; ?>
</table>

    <p style="text-align: left;">* Quantidade de horas feitas até o momento. <br> ** Última data e hora do atendimento.</p>

</body>
</html>

<?php
if ($stmt) $stmt->close();
$conn->close();
?>
