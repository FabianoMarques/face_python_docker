<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';
$result = false;

$sql = "
    SELECT
    nome_paciente,
    nome_plano,
    profissional,
    valor_plano,
    qtd_horas_feitas,
    valor_hora_colaborador,
    total_colaborador,
    data_consulta,
    percentual,
    MIN(data_registro) AS data_registro -- ou MAX, depende do que você quiser
FROM historico
WHERE 1=1
GROUP BY
    nome_paciente,
    nome_plano,
    profissional,
    valor_plano,
    qtd_horas_feitas,
    valor_hora_colaborador,
    total_colaborador,
    data_consulta,
    percentual

";

$params = [];
$types = '';

if (!empty($filtro_mes)) {
    $sql .= " AND DATE_FORMAT(data_consulta, '%Y-%m') = ?";
    $params[] = $filtro_mes;
    $types .= 's';
}

if (!empty($filtro_profissional)) {
    $sql .= " AND profissional LIKE ?";
    $params[] = '%' . $filtro_profissional . '%';
    $types .= 's';
}

$sql .= " ORDER BY nome_paciente, data_consulta";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histórico de Consultas</title>
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
    <h2>HISTÓRICO DE CONSULTAS</h2>
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
        <button onclick="location.href='relatorio.php'"><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>
    <table style="width: 100%;">
        <thead>
        <tr>
            <th>Paciente</th>
            <th>Plano</th>
            <th>Profissional</th>
            <th>Valor/Plano (R$)</th>
            <th>Horas Feitas</th>
            <th>Hora/Plano</th>
            <th>Total Colaborador (R$)</th>
            <th>Data Consulta</th>
            <th>Registro/Log</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nome_paciente']) ?></td>
                    <td><?= htmlspecialchars($row['nome_plano']) ?></td>
                    <td><?= htmlspecialchars($row['profissional']) ?></td>
                    <td>R$ <?= number_format($row['valor_plano'], 2, ',', '.') ?></td>
                    <td><?= $row['qtd_horas_feitas'] ?></td>
                    <td>R$ <?= number_format($row['valor_hora_colaborador'], 2, ',', '.') ?> </td>
                    <td>R$ <?= number_format($row['total_colaborador'], 2, ',', '.') ?> (<?= intval($row['percentual']) ?>%)</td>
                    <td><?= date('d/m/Y H:i', strtotime($row['data_consulta'])) ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($row['data_registro'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">Nenhum histórico encontrado<?= $filtro_mes || $filtro_profissional ? ' para os filtros selecionados.' : '.' ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <p style="text-align: left;">* Dados armazenados permanentemente no histórico para controle. <br> ** Registro feito ao clicar em "Gerar Histórico".</p>
</div>

</body>
</html>

<?php
if ($stmt) $stmt->close();
$conn->close();
?>
