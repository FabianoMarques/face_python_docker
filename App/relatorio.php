<?php
include("valida.php");
include("menu_template.php") ;
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

$registros = $result->fetch_all(MYSQLI_ASSOC); // ← agora salvamos tudo em array
$quantidade_registros = count($registros);

$soma_total = 0;
foreach ($registros as $linha) {
    $soma_total += $linha['total']; // ← este é o nome correto da coluna retornada no SELECT
}


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
   
    
</head>
<body>

<div class="container">
    <h2 style="text-align: center;"><b>CONSULTAS PARA HISTÓRICO</b></h2>
    <hr>
    <form method="get" class="form-inline text-center" style="margin-bottom: 5px;">
    
    <div class="form-group">
        <label for="mes">Filtrar por mês:</label>
        <input type="month" name="mes" id="mes" value="<?= htmlspecialchars($filtro_mes) ?>" class="form-control" style="width: auto; margin-right: 10px;">
    </div>

    <div class="form-group">
        <label for="profissional">Filtrar por profissional:</label>
        <input type="text" name="profissional" id="profissional" placeholder="Digite o nome" value="<?= htmlspecialchars($filtro_profissional) ?>" class="form-control" style="width: auto; margin-right: 10px;">
    </div>

    <button type="submit" class="btn btn-primary">Filtrar</button>

</form>

    <hr>
    <div class="button-row" style="margin-bottom: 20px; display: flex; justify-content: center; gap: 10%;">
        <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print"></i> Imprimir</button>

        <?php if ($result && $result->num_rows > 0): ?>
            <button onclick="location.href='gerar-historico.php'" class="btn btn-warning">
                <i class="fas fa-save"></i> Gerar Histórico
            </button>
        <?php endif; ?>

        <button onclick="location.href='historico.php'"
            style="margin-top: 0px; background-color:rgb(241, 241, 241);" class="btn btn-default">
            <i class="fas fa-history"></i> Ver Histórico
        </button>
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

    <table class="table table-striped table-hover ">
    <thead>
    <tr class="info">
        <th>Paciente</th>
        <th>Plano</th>
        <th>Profissional</th>
        <th style="width:130px">Plano (R$)</th>
        <th>Hora/Plano (R$)</th>
        <th>Hora/Colaborador (R$) </th>
        <th>Hora/Atendimento *</th>
        <th>Total Colaborador (R$)</th>
        <th>Data/Consulta**</th>
    </tr>
    </thead>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nome_paciente']) ?></td>
                <td><?= htmlspecialchars($row['nome_plano']) ?></td>
                <td><?= htmlspecialchars($row['profissional']) ?></td>
                <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?> <a href="#"> <span class="badge"><?= htmlspecialchars($row['numero_aulas']) ?></span></a></td>
                <td>R$ <?= number_format($row['valor_hora'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($row['valor_aula_colaborador'], 2, ',', '.') ?><a href="#"> <span class="badge"><?= htmlspecialchars($row['percentual']) ?></span></a></td>
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
