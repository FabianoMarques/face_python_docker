<?php
require_once '../db.php'; // Inclui a classe de conexão

$db = new Database();
$conn = $db->getConnection();

// Query para buscar os dados
$sql = "SELECT 
            consultas.idconsulta, 
            consultas.idpaciente, 
            paciente.nome AS paciente_nome, 
            consultas.profissional, 
            consultas.dt_consulta 
        FROM consultas
        JOIN paciente ON consultas.idpaciente = paciente.idpaciente
        ORDER BY consultas.dt_consulta DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Consultas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #eee;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        nav {
            background-color: #f5f5f5; /* Cinza claro para combinar com o tema */
            padding: 12px 0;
            text-align: center;
            border-bottom: 0px solid #ddd; /* Linha sutil para separar do conteúdo */
            margin-bottom:30px;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            display: inline;
            margin: 0 15px;
        }

        nav ul li a {
            color: #333; /* Texto escuro para contraste */
            text-decoration: none;
            font-size: 18px;
            padding: 8px 12px;
            transition: 0.3s;
        }

        nav ul li a:hover {
            background-color: #ddd; /* Efeito hover suave */
            border-radius: 5px;
            padding: 8px 15px;
        }

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<body>
<nav>
    <ul>
        <li><a href="menu.php"><i class="fa fa-house"></i> Início</a></li>
        <li><a href="pacientes.php"><i class="fa fa-user"></i> CADASTRAR PACIENTE</a></li> 
        <li><a href="#" onclick="window.print();"><i class="fa fa-print"></i> Imprimir</a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Sair</a></li>
    </ul>
</nav>


    <div class="container">
        <h2>Lista de Consultas</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Profissional</th>
                <th>Data da Consulta</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data_br = date('d/m/Y H:i:s', strtotime($row['dt_consulta']));
                    echo "<tr>
                            <td>{$row['idconsulta']}</td>
                            <td>{$row['paciente_nome']}</td>
                            <td>{$row['profissional']}</td>
                            <td>{$data_br}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Nenhuma consulta encontrada.</td></tr>";
            }
            ?>
        </table>
        <br>
    </div>

</body>
</html>

<?php
$conn->close(); // Fecha a conexão
?>
