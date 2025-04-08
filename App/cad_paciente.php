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
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="botoes.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding-top: 10px;
            padding-left: 20px;
            padding-right: 20px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #eeeeee;
            color: black;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin: 2px;
            display: inline-block;
        }

        .edit { background: #f0ad4e; }
        .delete { background: #d9534f; }
        .add { background: #5bc0de; }
        .menu { background: #6c757d; }

        .message {
            color: green;
            font-weight: bold;
            margin-top: 15px;
        }

        .button-row {
            width: 90%;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <h2>LISTA DE PACIENTES</h2>

    <?php if (!empty($mensagem)) echo "<p class='message'>$mensagem</p>"; ?>

    <div class="button-row" style="margin-bottom:-5px">
        <a href="form.php" class="btn add"><i class="fa fa-plus"></i> Adicionar Paciente</a>
        <a href="menu.php" class="btn menu"><i class="fa fa-arrow-left"></i> Voltar ao Menu</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>CPF</th>
            <th>Email</th>
            <th>Profissional</th>
            <th>Ações</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['idpaciente'] ?></td>
            <td><?= $row['nome'] ?></td>
            <td><?= $row['CPF'] ?></td>
            <td><?= $row['email'] ?></td>
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
