<?php
require_once '../db.php';
$conn = (new Database())->getConnection();

// Excluir paciente
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM paciente WHERE idpaciente=?");
    $stmt->bind_param("i", $_GET['delete_id']);
    if ($stmt->execute()) {
        $mensagem = "✅ Paciente excluído com sucesso!";
    } else {
        $mensagem = "❌ Erro ao excluir paciente: " . $stmt->error;
    }
    $stmt->close();
}

// Buscar pacientes com nome do plano
$sql = "SELECT p.idpaciente, p.nome, p.CPF, p.email, p.profissional, pl.nome AS nome_plano 
        FROM paciente p
        LEFT JOIN planos pl ON p.idplano = pl.idplano";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="estilo-relatorio.css">
    <link rel="stylesheet" href="botoes.css">
    
</head>
<body>

    <h2>LISTA DE PACIENTES</h2>

    <?php if (!empty($mensagem)) echo "<p class='message'>$mensagem</p>"; ?>

    <div class="button-row" style="margin-bottom:-5px">
       
        <button onclick="window.location.href='menu.php'" ><i class="fas fa-arrow-left"></i> Voltar</button>
        <button onclick="window.location.href='form.php'" >
                    <i class="fa fa-plus"></i> Adicionar Paciente
        </button>
      

    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>CPF</th>
            <th>Email</th>
            <th>Profissional</th>
            <th>Plano</th>
            <th>Ações</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['idpaciente'] ?></td>
            <td><?= $row['nome'] ?></td>
            <td><?= $row['CPF'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['profissional'] ?></td>
            <td><?= $row['nome_plano'] ?: '—' ?></td>
            <td>
                <a href="form.php?id=<?= $row['idpaciente'] ?>" class="btn edit"><i class="fa fa-edit"></i> Editar</a>
                <a href="cad_paciente.php?delete_id=<?= $row['idpaciente'] ?>" class="btn delete" onclick="return confirm('Tem certeza que deseja excluir?')"><i class="fa fa-trash"></i> Excluir</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

</body>
</html>
