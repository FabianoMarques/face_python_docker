<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';
$result = false;

if (!empty($filtro_mes) || !empty($filtro_profissional)) {
    $sql = "
        SELECT 
            p.nome AS nome_paciente,
            pl.nome AS nome_plano,
            c.profissional,
            pl.valor_hora,
            c.qtd_horas_feitas,
            c.dt_consulta,
            (pl.valor_hora * c.qtd_horas_feitas) AS total
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
        WHERE 1 = 1
    ";

    $params = [];
    $types = '';

    if (!empty($filtro_mes)) {
        $sql .= " AND DATE_FORMAT(c.dt_consulta, '%Y-%m') = ?";
        $params[] = $filtro_mes;
        $types .= 's';
    }

    if (!empty($filtro_profissional)) {
        $sql .= " AND c.profissional LIKE ?";
        $params[] = '%' . $filtro_profissional . '%';
        $types .= 's';
    }

    $sql .= " ORDER BY p.nome, c.dt_consulta";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "
        SELECT 
            p.nome AS nome_paciente,
            pl.nome AS nome_plano,
            c.profissional,
            pl.valor_hora,
            c.qtd_horas_feitas,
            c.dt_consulta,
            (pl.valor_hora * c.qtd_horas_feitas) AS total
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
        ORDER BY p.nome, c.dt_consulta
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
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
    <h2>RELATÓRIO DE CONSULTAS</h2>
    <hr>
    <form method="get" style="margin-bottom: 5px;">
        <label for="mes">Filtrar por mês:</label>
        <input type="month" name="mes" id="mes" value="<?= htmlspecialchars($filtro_mes) ?>">

        <label for="profissional">Filtrar por profissional:</label>
        <input type="text" name="profissional" id="profissional" placeholder="Digite o nome" value="<?= htmlspecialchars($filtro_profissional) ?>">

        <button type="submit">Filtrar</button>
    </form>
    <hr>
    <div class="button-row" style="margin-bottom: 15px; display: flex; justify-content: center; gap: 70%;">
        <button onclick="location.href='menu.php'"><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>

    <table style="width: 100%;">
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Plano</th>
                <th>Profissional</th>
                <th>Valor da Hora</th>
                <th>Horas Feitas (Consulta)</th>
                <th>Data e Hora da Consulta</th>
                <th>Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome_paciente']) ?></td>
                        <td><?= htmlspecialchars($row['nome_plano']) ?></td>
                        <td><?= htmlspecialchars($row['profissional']) ?></td>
                        <td>R$ <?= number_format($row['valor_hora'], 2, ',', '.') ?></td>
                        <td><?= $row['qtd_horas_feitas'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['dt_consulta'])) ?></td>
                        <td>R$ <?= number_format($row['total'], 2, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nenhum dado encontrado<?= $filtro_mes || $filtro_profissional ? ' para os filtros selecionados.' : '.' ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
if ($stmt) $stmt->close();
$conn->close();
?>
