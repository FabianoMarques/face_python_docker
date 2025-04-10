<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

// Capturar os filtros enviados via GET
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Montar a query com filtros
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

$params = [];
$tipos = "";

// Filtro por profissional
if (!empty($filtro_profissional)) {
    $sql .= " AND profissional LIKE ?";
    $params[] = '%' . $filtro_profissional . '%';
    $tipos .= "s";
}

// Filtro por intervalo de data
if (!empty($filtro_data_inicio) && !empty($filtro_data_fim)) {
    $sql .= " AND dt_consulta BETWEEN ? AND ?";
    $params[] = $filtro_data_inicio;
    $params[] = $filtro_data_fim;
    $tipos .= "ss";
}

// Filtro de maior quantidade de horas por mês
$sql .= "
    AND qtd_horas_feitas = (
        SELECT MAX(qtd_horas_feitas)
        FROM historico AS h2
        WHERE h2.nome_paciente = historico.nome_paciente
        AND YEAR(h2.dt_consulta) = YEAR(historico.dt_consulta)
        AND MONTH(h2.dt_consulta) = MONTH(historico.dt_consulta)
    )
ORDER BY dt_consulta DESC
";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Gerar CSV
function gerarCSV($result) {
    if ($result->num_rows > 0) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="historico_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Paciente', 'Plano', 'Profissional', 'Valor da Hora', 'Horas Feitas', 'Data da Consulta', 'Total (R$)'], ';');

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['nome_paciente'],
                $row['nome_plano'],
                $row['profissional'],
                $row['valor_hora'],
                $row['qtd_horas_feitas'],
                $row['dt_consulta'],
                $row['total']
            ], ';');
        }

        fclose($output);
        exit;
    } else {
        echo "Nenhum dado encontrado para exportar.";
    }
}

gerarCSV($result);

$stmt->close();
$conn->close();
?>
