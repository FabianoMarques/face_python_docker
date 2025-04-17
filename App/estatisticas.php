<?php
include("valida.php");
include("menu_template.php") ;
require_once '../db.php'; 

$db = new Database();
$conn = $db->getConnection();

$ano = $_GET['ano'] ?? date('Y');
$mesFiltro = $_GET['mes'] ?? '';
$profissional = $_GET['profissional'] ?? '';

// Etapa 1: Buscar maior qtd_horas_feitas por profissional e mês
$sql = "
    SELECT profissional, DATE_FORMAT(data_consulta, '%Y-%m') AS mes, MAX(qtd_horas_feitas) AS max_horas
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

$sql .= " GROUP BY profissional, mes";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$maxHoras = [];
while ($row = $result->fetch_assoc()) {
    $maxHoras[$row['profissional']][$row['mes']] = $row['max_horas'];
}

// Etapa 2: Buscar os totais correspondentes
$data = [];
$meses = [];

foreach ($maxHoras as $prof => $mesesHoras) {
    foreach ($mesesHoras as $mes => $horas) {
        $query = "
            SELECT total_colaborador
            FROM historico
            WHERE profissional = ? AND DATE_FORMAT(data_consulta, '%Y-%m') = ? AND qtd_horas_feitas = ?
            ORDER BY id DESC LIMIT 1
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $prof, $mes, $horas);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $data[$prof][$mes] = (float)$row['total_colaborador'];
            $meses[$mes] = true;
        }
    }
}

ksort($meses);
$meses = array_keys($meses);

// Lista de profissionais
$profissionais_result = $conn->query("SELECT DISTINCT profissional FROM historico ORDER BY profissional");
$profissionais = [];
while ($row = $profissionais_result->fetch_assoc()) {
    $profissionais[] = $row['profissional'];
}

// Preparar dados para gráfico/tabela
$dados = [];
$totaisPorMes = [];
$totalGeral = 0;

foreach ($data as $prof => $mesValores) {
    foreach ($mesValores as $mes => $valor) {
        $dados[$mes][$prof] = $valor;
        $totaisPorMes[$mes] = ($totaisPorMes[$mes] ?? 0) + $valor;
        $totalGeral += $valor;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Estatísticas Contábeis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0px;
            background-color: #f9f9f9;
            color: #333;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 25px;
        }
        label {
            margin-right: 20px;
        }
        select, input, button {
            padding: 6px 10px;
            font-size: 14px;
            margin-top: 5px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        thead {
            background-color: #34495e;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        canvas {
            margin-top: 40px;
        }

    </style>
</head>
<body>
<div class="container">
<h2 style="text-align: center;">Estatísticas Contábeis (<?= $ano ?><?= $mesFiltro ? " - Mês: $mesFiltro" : "" ?>)</h2>
<hr>
<form method="GET" style="text-align: center;">
    <label>Ano: <input type="number" name="ano" value="<?= $ano ?>"></label>
    <label>Mês:
        <select name="mes">
            <option value="">Todos</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($mesFiltro == $m) ? 'selected' : '' ?>>
                    <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                </option>
            <?php endfor; ?>
        </select>
    </label>
    <label>Profissional:
        <select name="profissional">
            <option value="">Todos</option>
            <?php foreach ($profissionais as $p): ?>
                <option value="<?= $p ?>" <?= ($profissional == $p) ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filtrar</button>
</form>

<?php if (!empty($dados)): ?>
    <table>
        <thead>
            <tr>
                <th>Mês</th>
                <?php foreach ($data as $prof => $val): ?>
                    <th><?= $prof ?></th>
                <?php endforeach; ?>
                <th>Total do Mês</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($meses as $mes): ?>
                <tr>
                    <td><?= $mes ?></td>
                    <?php foreach ($data as $prof => $mesVals): ?>
                        <td><?= isset($mesVals[$mes]) ? number_format($mesVals[$mes], 2, ',', '.') : '-' ?></td>
                    <?php endforeach; ?>
                    <td><strong><?= number_format($totaisPorMes[$mes], 2, ',', '.') ?></strong></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background-color: #ecf0f1;">
                <td><strong>Total Geral</strong></td>
                <?php foreach ($data as $prof => $mesVals): ?>
                    <td><strong><?= number_format(array_sum($mesVals), 2, ',', '.') ?></strong></td>
                <?php endforeach; ?>
                <td><strong><?= number_format($totalGeral, 2, ',', '.') ?></strong></td>
            </tr>
        </tbody>
    </table>

    <br><h2 style="">Valores Recebidos por Profissional (maior número de horas/mês)</h2>
    <canvas id="grafico" style="width: 1000px; height: 300px;"></canvas>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script>
        const ctx = document.getElementById('grafico').getContext('2d');
        const chartData = {
            labels: <?= json_encode($meses) ?>,
            datasets: [
                <?php foreach ($data as $prof => $mesVals): ?>
                {
                    label: "<?= $prof ?>",
                    data: <?= json_encode(array_values(array_replace(array_fill_keys($meses, 0), $mesVals))) ?>,
                    backgroundColor: 'rgba(<?= rand(0,255) ?>,<?= rand(0,255) ?>,<?= rand(0,255) ?>,0.6)',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1
                },
                <?php endforeach; ?>
            ]
        };

        const myChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: ''
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        },
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>

    <?php else: ?>
        <p><strong>Nenhum dado encontrado para os filtros selecionados.</strong></p>
    <?php endif; ?>


</div>
</body>
</html>
