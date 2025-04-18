<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';

$sql_base = "
    SELECT DISTINCT
        p.nome AS nome_paciente,
        pl.nome AS nome_plano,
        c.profissional,
        pl.valor AS valor_plano,
        pl.numero_aulas,
        pl.percentual,
        c.qtd_horas_feitas,
        CASE 
            WHEN pl.numero_aulas > 0 THEN pl.valor / pl.numero_aulas 
            ELSE 0 
        END AS valor_hora_plano,
        CASE 
            WHEN pl.numero_aulas > 0 THEN ((pl.valor / pl.numero_aulas) * (pl.percentual / 100)) 
            ELSE 0 
        END AS valor_hora_colaborador,
        CASE 
            WHEN pl.numero_aulas > 0 THEN ((pl.valor / pl.numero_aulas) * (pl.percentual / 100)) * c.qtd_horas_feitas
            ELSE 0 
        END AS total_colaborador,
        c.dt_consulta AS data_consulta
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
    $sql_base .= " AND DATE_FORMAT(c.dt_consulta, '%Y-%m') = ?";
    $params[] = $filtro_mes;
    $types .= 's';
}

if (!empty($filtro_profissional)) {
    $sql_base .= " AND c.profissional LIKE ?";
    $params[] = '%' . $filtro_profissional . '%';
    $types .= 's';
}

$stmt = $conn->prepare($sql_base);
if (!$stmt) {
    die("Erro ao preparar statement base: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$inseridos = 0;
$nao_inseridos = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nome_paciente = $row['nome_paciente'];
        $nome_plano = $row['nome_plano'];
        $profissional = $row['profissional'];
        $valor_plano = $row['valor_plano'];
        $numero_aulas = $row['numero_aulas'];
        $percentual = $row['percentual'];
        $qtd_horas_feitas = $row['qtd_horas_feitas'];
        $valor_hora_plano = $row['valor_hora_plano'];
        $valor_hora_colaborador = $row['valor_hora_colaborador'];
        $total_colaborador = $row['total_colaborador'];
        $data_consulta = $row['data_consulta'];
        $data_registro = date('Y-m-d H:i:s');

        // Verifica se já existe o registro
        $check_sql = "
            SELECT 1 FROM historico 
            WHERE nome_paciente = ? 
              AND nome_plano = ? 
              AND profissional = ? 
              AND valor_plano = ? 
              AND qtd_horas_feitas = ? 
              AND valor_hora_plano = ? 
              AND valor_hora_colaborador = ?
              AND total_colaborador = ? 
              AND data_consulta = ? 
              AND percentual = ?
        ";
        $stmt_check = $conn->prepare($check_sql);
        if (!$stmt_check) {
            die("Erro ao preparar check: " . $conn->error);
        }
        $stmt_check->bind_param(
            "sssddddssi",
            $nome_paciente,
            $nome_plano,
            $profissional,
            $valor_plano,
            $qtd_horas_feitas,
            $valor_hora_plano,
            $valor_hora_colaborador,
            $total_colaborador,
            $data_consulta,
            $percentual
        );
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows == 0) {
            // Insere no histórico
            $insert_sql = "
                INSERT INTO historico (
                    nome_paciente,
                    nome_plano,
                    profissional,
                    valor_plano,
                    valor_hora_plano,
                    n_atendimento_plano,
                    qtd_horas_feitas,
                    valor_hora_colaborador,
                    total_colaborador,
                    data_consulta,
                    percentual,
                    data_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $conn->prepare($insert_sql);
            if (!$stmt_insert) {
                die("Erro ao preparar insert: " . $conn->error);
            }
            $stmt_insert->bind_param(
                "sssddiiddsis",
                $nome_paciente,
                $nome_plano,
                $profissional,
                $valor_plano,
                $valor_hora_plano,
                $numero_aulas,
                $qtd_horas_feitas,
                $valor_hora_colaborador,
                $total_colaborador,
                $data_consulta,
                $percentual,
                $data_registro
            );
            $stmt_insert->execute();
            $inseridos++;
            $stmt_insert->close();
        } else {
            $nao_inseridos++;
        }

        $stmt_check->close();
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histórico Gerado</title>
    <link rel="stylesheet" href="estilo-relatorio.css">
</head>
<body>
    <div class="container">
        <div class="row" >

        <div style="padding:50px; background-color:#e3f2fd; margin-top: 150px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);">
            <h2><b>Resultado da Geração do Histórico</b></h2><hr>
            <h4 style="margin-left: 50px;"><strong>Registros inseridos:</strong> <b> <?= $inseridos ?></b></h4>
            <h4 style="margin-left: 50px;"><strong>Registros ignorados (já existiam):</strong><b> <?= $nao_inseridos ?></b> </h4>
            <br>
            <div style="text-align:center">
            <button onclick="location.href='relatorio.php'" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Voltar ao Relatório</button>
            <button onclick="location.href='historico.php'" class="btn btn-primary"><i class="fas fa-history"></i> Ver Histórico</button>
            </div>
        </div>

    </div>
</div>
</body>
</html>
