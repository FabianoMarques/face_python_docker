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

// Buscar todos os pacientes
$result = $conn->query("SELECT * FROM paciente");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; margin: 0; padding: 20px; text-align: center; }
        table { width: 80%; margin: auto; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background:rgb(238, 238, 238); color: black; }
        .buttons { text-align: center; margin-top: 20px; }
        .btn { padding: 5px 10px; border: none; cursor: pointer; text-decoration: none; color: white; border-radius: 5px; margin-right: 5px; }
        .edit { background: #f0ad4e; }
        .delete { background: #d9534f; }
        .add { background: #5bc0de; }
    </style>
</head>
<body>

<h2>PACIENTES</h2>
<?php if (!empty($mensagem)) echo "<p style='color: red;'>$mensagem</p>"; ?>

<a href="form.php" class="btn add"><i class="fa fa-plus"></i> Adicionar Paciente</a>

<table>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>CPF</th>
        <th>Profissional</th>
        <th>Ações</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['idpaciente'] ?></td>
        <td><?= $row['nome'] ?></td>
        <td><?= $row['CPF'] ?></td>
        <td><?= $row['profissional'] ?></td>
        <td>
            <a href="form.php?id=<?= $row['idpaciente'] ?>" class="btn edit"><i class="fa fa-edit"></i> Editar</a>
            <a href="cad_paciente.php?delete_id=<?= $row['idpaciente'] ?>" class="btn delete" onclick="return confirm('Tem certeza que deseja excluir?')"><i class="fa fa-trash"></i> Excluir</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
