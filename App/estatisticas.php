<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

// Coletar filtros
$ano = $_GET['ano'] ?? date('Y');
$mesFiltro = $_GET['mes'] ?? '';
$profissional = $_GET['profissional'] ?? '';

// Consulta para o gráfico
$sql = "
    SELECT 
        profissional,
        DATE_FORMAT(data_consulta, '%Y-%m') AS mes,
        SUM(total_colaborador) AS total_mes
    FROM historico
    WHERE YEAR(data_consulta) = ?
";
$params = [$ano];
$types = 'i';

if ($mesFiltro !== '') {
    $sql .= " AND MONTH(data_consulta) = ?";
    $params[] = $mesFiltro;
    $types .= 'i';
}

if ($profissional !== '') {
    $sql .= " AND profissional = ?";
    $params[] = $profissional;
    $types .= 's';
}

$sql .= " GROUP BY profissional, mes ORDER BY mes, profissional";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$meses = [];

while ($row = $result->fetch_assoc()) {
    $mesLabel = $row['mes'];
    $prof = $row['profissional'];
    $total = (float)$row['total_mes'];

    $data[$prof][$mesLabel] = $total;
    $meses[$mesLabel] = true;
}

ksort($meses);
$meses = array_keys($meses);

// Obter lista de profissionais para o filtro
$profissionais_result = $conn->query("SELECT DISTINCT profissional FROM historico ORDER BY profissional");
$profissionais = [];
while ($row = $profissionais_result->fetch_assoc()) {
    $profissionais[] = $row['profissional'];
}

// Consulta para a tabela
$sql2 = "
    SELECT 
        DATE_FORMAT(data_consulta, '%Y-%m') AS mes,
        profissional,
        SUM(total_colaborador) AS total
    FROM historico
    WHERE YEAR(data_consulta) = ?
";

$params2 = [$ano];
$types2 = 'i';

if ($mesFiltro !== '') {
    $sql2 .= " AND MONTH(data_consulta) = ?";
    $params2[] = $mesFiltro;
    $types2 .= 'i';
}

if ($profissional !== '') {
    $sql2 .= " AND profissional = ?";
    $params2[] = $profissional;
    $types2 .= 's';
}

$sql2 .= " GROUP BY mes, profissional ORDER BY mes, profissional";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param($types2, ...$params2);
$stmt2->execute();
$result2 = $stmt2->get_result();

$dados = [];
$totaisPorMes = [];
$totalGeral = 0;

if ($result2 && $result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $mesLabel = $row['mes'];
        $prof = $row['profissional'];
        $total = (float)$row['total'];

        $dados[$mesLabel][$prof] = $total;
        $totaisPorMes[$mesLabel] = ($totaisPorMes[$mesLabel] ?? 0) + $total;
        $totalGeral += $total;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estatísticas Contábeis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f8f9fa;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        canvas {
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        form {
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }
        form select, form button {
            padding: 6px 12px;
            font-size: 14px;
        }
        table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        tfoot {
            background: #f1f1f1;
            font-weight: bold;
        }
        tr:nth-child(even) td {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Estatísticas Contábeis</h2>

        <form method="get">
            <label for="ano">Ano:</label>
            <select name="ano" id="ano">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= $ano == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <label for="mes">Mês:</label>
            <select name="mes" id="mes">
                <option value="">Todos</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $mesFiltro == $m ? 'selected' : '' ?>>
                        <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label for="profissional">Profissional:</label>
            <select name="profissional" id="profissional">
                <option value="">Todos</option>
                <?php foreach ($profissionais as $prof): ?>
                    <option value="<?= $prof ?>" <?= $profissional == $prof ? 'selected' : '' ?>>
                        <?= $prof ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtrar</button>
        </form>

        <canvas id="grafico" height="100"></canvas>

        <br><br><h2>Relatório de Faturamento</h2>
        <table>
            <thead>
                <tr>
                    <th>Mês</th>
                    <th>Profissional</th>
                    <th>Valor Recebido (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $mesLabel => $profissionais): ?>
                    <?php foreach ($profissionais as $prof => $valor): ?>
                        <tr>
                            <td><?= $mesLabel ?></td>
                            <td><?= $prof ?></td>
                            <td>R$ <?= number_format($valor, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight:bold;">
                        <td colspan="2">Total do mês <?= $mesLabel ?></td>
                        <td>R$ <?= number_format($totaisPorMes[$mesLabel], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total Geral</td>
                    <td>R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        const ctx = document.getElementById('grafico').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [
                    <?php foreach ($data as $prof => $valores): ?>
                    {
                        label: <?= json_encode($prof) ?>,
                        data: <?= json_encode(array_map(fn($m) => $valores[$m] ?? 0, $meses)) ?>,
                        backgroundColor: 'rgba(<?= rand(50,200) ?>, <?= rand(50,200) ?>, <?= rand(50,200) ?>, 0.7)',
                        borderColor: 'rgba(0,0,0,0.8)',
                        borderWidth: 1
                    },
                    <?php endforeach; ?>
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: (value) => value ? 'R$ ' + value.toFixed(2).replace('.', ',') : '',
                        font: { weight: 'bold' },
                        color: '#000'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Valor Total (R$)' }
                    },
                    x: {
                        title: { display: true, text: 'Mês' }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>
</body>
</html>
