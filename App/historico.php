<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

// Parâmetros de filtro
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

$result = false;
$params = [];

// Construção da consulta SQL
$sql = "
    SELECT DISTINCT
        nome_paciente,
        nome_plano,
        profissional,
        valor_hora,
        qtd_horas_feitas,
        dt_consulta, 
        total
    FROM historico
    WHERE 1=1
";

// Filtro por profissional
if (!empty($filtro_profissional)) {
    $sql .= " AND profissional LIKE ?";
    $params[] = '%' . $filtro_profissional . '%';
}

// Filtro por intervalo de data
if (!empty($filtro_data_inicio) && !empty($filtro_data_fim)) {
    $sql .= " AND dt_consulta BETWEEN ? AND ?";
    $params[] = $filtro_data_inicio;
    $params[] = $filtro_data_fim;
}

// Consulta ajustada para pegar apenas o maior valor de horas por paciente no mês
$sql .= "
    AND (qtd_horas_feitas) = (
        SELECT MAX(qtd_horas_feitas)
        FROM historico AS h2
        WHERE h2.nome_paciente = historico.nome_paciente
        AND YEAR(h2.dt_consulta) = YEAR(historico.dt_consulta)
        AND MONTH(h2.dt_consulta) = MONTH(historico.dt_consulta)
    )
ORDER BY dt_consulta DESC
";

// Preparando a consulta
$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Função para gerar o arquivo XML
function gerarXML($result) {
    if ($result->num_rows > 0) {
        $xml = new SimpleXMLElement('<historico/>');

        while ($row = $result->fetch_assoc()) {
            $registro = $xml->addChild('registro');
            $registro->addChild('nome_paciente', $row['nome_paciente']);
            $registro->addChild('nome_plano', $row['nome_plano']);
            $registro->addChild('profissional', $row['profissional']);
            $registro->addChild('valor_hora', $row['valor_hora']);
            $registro->addChild('qtd_horas_feitas', $row['qtd_horas_feitas']);
            $registro->addChild('dt_consulta', $row['dt_consulta']);
            $registro->addChild('total', $row['total']);
        }

        // Definindo o nome do arquivo XML
        $fileName = 'historico_' . date('Ymd_His') . '.xml';

        // Definindo o cabeçalho de resposta para download do XML
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $xml->asXML();
        exit;
    } else {
        echo "Nenhum dado encontrado para exportar.";
    }
}

if (isset($_GET['exportar_xml'])) {
    gerarXML($result);
}
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

        form input[type="text"],
        form input[type="date"] {
            padding: 6px 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
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
            width: 100%;
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

        .button-row {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 1300px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <h2>HISTÓRICO DE CONSULTAS</h2>
    <hr>

    <!-- Filtro de pesquisa -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <form method="get" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0;">
            <label for="profissional">Filtrar por profissional:</label>
            <input type="text" name="profissional" id="profissional" placeholder="Digite o nome"
                value="<?= htmlspecialchars($filtro_profissional) ?>">

            <label for="data_inicio">Data Início:</label>
            <input type="date" name="data_inicio" id="data_inicio"
                value="<?= htmlspecialchars($filtro_data_inicio) ?>">

            <label for="data_fim">Data Fim:</label>
            <input type="date" name="data_fim" id="data_fim"
                value="<?= htmlspecialchars($filtro_data_fim) ?>">

            <button type="submit">Filtrar</button>
        </form>

        <form method="get" action="csv.php" style="margin: 0;">
            <input type="hidden" name="profissional" value="<?= htmlspecialchars($filtro_profissional) ?>">
            <input type="hidden" name="data_inicio" value="<?= htmlspecialchars($filtro_data_inicio) ?>">
            <input type="hidden" name="data_fim" value="<?= htmlspecialchars($filtro_data_fim) ?>">
            <button type="submit" class="btn_branco">
                <i class="fas fa-file-csv"></i> EXPORTAR CSV
            </button>
        </form>
    </div>

    <hr>

    <div class="button-row" style="margin-bottom: 15px; display: flex; justify-content: center; gap: 70%;">
        <button onclick="location.href='relatorio.php'"><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Plano</th>
                <th>Profissional</th>
                <th>Valor da Hora</th>
                <th>Horas Feitas</th>
                <th>Data da Consulta</th>
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
                    <td colspan="7">Nenhum dado encontrado com os filtros selecionados.</td>
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

