<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

$mes = $_GET['mes'] ?? '';
$profissional = $_GET['profissional'] ?? '';

// Aqui a tabela onde o histórico será salvo
$tabela_historico = 'historico_consultas';

if ($mes || $profissional) {
    $sql = "
        INSERT INTO historico (nome_paciente, nome_plano, profissional, valor_hora, qtd_horas_feitas, dt_consulta, total)
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

    if (!empty($mes)) {
        $sql .= " AND DATE_FORMAT(c.dt_consulta, '%Y-%m') = ?";
        $params[] = $mes;
        $types .= 's';
    }

    if (!empty($profissional)) {
        $sql .= " AND c.profissional LIKE ?";
        $params[] = '%' . $profissional . '%';
        $types .= 's';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Histórico gerado com sucesso!'); window.location.href = 'relatorio.php?mes=" . urlencode($mes) . "&profissional=" . urlencode($profissional) . "';</script>";
    } else {
        echo "<script>alert('Erro ao gerar histórico.'); history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Filtros não definidos.'); history.back();</script>";
}

$conn->close();
?>
