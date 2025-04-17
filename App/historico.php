<?php
include("valida.php");
require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

// FILTROS
$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$filtro_profissional = isset($_GET['profissional']) ? trim($_GET['profissional']) : '';

// INÍCIO DA QUERY
$sql = "
    SELECT h1.*
    FROM historico h1
    INNER JOIN (
        SELECT
            profissional,
            DATE_FORMAT(data_consulta, '%Y-%m') as mes_ano,
            MAX(qtd_horas_feitas) as max_horas,
            MAX(data_consulta) as data_mais_recente
        FROM historico
        WHERE 1=1
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

$sql .= "
        GROUP BY profissional, mes_ano
    ) h2
      ON h1.profissional = h2.profissional
    AND DATE_FORMAT(h1.data_consulta, '%Y-%m') = h2.mes_ano
    AND h1.qtd_horas_feitas = h2.max_horas
    AND h1.data_consulta = h2.data_mais_recente
";

// Preparar e executar
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$registros = $result->fetch_all(MYSQLI_ASSOC); // ← agora salvamos tudo em array
$quantidade_registros = count($registros);

// Agora calculamos a soma manualmente
$soma_total = 0;
foreach ($registros as $linha) {
    $soma_total += $linha['total_colaborador'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histórico de Consultas</title>
</head>
<body>

<div class="container">
    <h2 style="text-align: center;"><b>HISTÓRICO DE CONSULTAS</b></h2>
    <hr>

    <form method="get" style="margin-bottom: 5px; text-align: center;" class="form-inline">
        <label for="mes">Filtrar por mês:</label>
        <input type="month" name="mes" id="mes" value="<?= htmlspecialchars($filtro_mes) ?>" class="form-control form-control-sm w-auto">

        <label for="profissional"> Filtrar por profissional:</label>
        <input type="text" name="profissional" id="profissional" placeholder="Digite o nome" value="<?= htmlspecialchars($filtro_profissional) ?>" class="form-control form-control-sm w-auto">

        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <hr>
    <div class="button-row" style="margin: 30px; display: flex; justify-content: center; gap: 10%;">
        <button onclick="location.href='relatorio.php'" class="btn btn-default"><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print"></i> Imprimir</button>
        <button onclick="location.href='estatisticas.php'" class="btn btn-default"><i class="fas fa-chart-line"></i> Estatísticas</button>
    </div>

    <hr>
    <div class="row mb-3 align-items-center" style="margin-top: -20px;">
        <div class="col-md-6" style="margin-top:20px">
            <h4><?= "<b>".$quantidade_registros."</b>" ?> registro(s) encontrado(s)</h4>
        </div>
        <div class="col-md-6 text-end" style="text-align: right;">
            <h2><b>R$ <?= number_format($soma_total, 2, ',', '.') ?></b></h2>
        </div>
    </div>

    <table class="table table-striped table-hover">
        <thead>
        <tr class="info">
            <th>Paciente</th>
            <th>Plano</th>
            <th>Profissional</th>
            <th>Plano (R$)</th>
            <th>Hora/plano(R$)</th>
            <th>Hora/Colaborador (R$)</th>
            <th>Hora/Atendimento</th>
            <th>Total Colaborador (R$)</th>
            <th>Data Consulta</th>
            <th>Registro/Log *</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($quantidade_registros > 0): ?>
            <?php foreach ($registros as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nome_paciente']) ?></td>
                    <td><?= htmlspecialchars($row['nome_plano']) ?></td>
                    <td><?= htmlspecialchars($row['profissional']) ?></td>
                    <td style="width: 150px;">R$ <?= number_format($row['valor_plano'], 2, ',', '.') ?> <a href="#"> <span class="badge"><?= htmlspecialchars($row['n_atendimento_plano']) ?></span></a></td>
                    <td>R$ <?= htmlspecialchars($row['valor_hora_plano']) ?></td>
                    <td>R$ <?= number_format($row['valor_hora_colaborador'], 2, ',', '.') ?><a href="#"> <span class="badge"><?= intval($row['percentual']) ?>%</span></a></td>
                    <td><?= $row['qtd_horas_feitas'] ?></td>
                    <td>R$ <?= number_format($row['total_colaborador'], 2, ',', '.') ?> </td>
                    <td><?= date('d/m/Y H:i', strtotime($row['data_consulta'])) ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($row['data_registro'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">Nenhum histórico encontrado<?= $filtro_mes || $filtro_profissional ? ' para os filtros selecionados.' : '.' ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <p style="text-align: left;">Dados armazenados permanentemente no histórico para controle. <br> * Registro feito ao clicar em "Gerar Histórico".</p>
</div>

</body>
</html>

<?php
if ($stmt) $stmt->close();
$conn->close();
?>
