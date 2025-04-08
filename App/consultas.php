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
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 20px 0;
            opacity: 0.5;
        }

        .botoes-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .botao-acao {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .botao-acao:hover {
            background-color: #3e8e41;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
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

        .button-row {
            width: 90%;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
        }

    </style>
</head>
<body>
    <div class="container" style="margin-top:40px; width: 80%; max-width: 80%;">
        <h2>LISTA DE CONSULTAS</h2>

        <div class="button-row" style="margin-bottom:-5px">
        <a href="#" onclick="window.print()" class="botao-acao"><i class="fa fa-print"></i>Imprimir</a>
        <a href="menu.php" class="btn menu"><i class="fa fa-arrow-left"></i> Voltar ao Menu</a>
        </div>

        <?php
        $totalRegistros = $result->num_rows;
        echo "<p><strong>Total de registros encontrados:</strong> {$totalRegistros}</p>";
        ?>
        <hr>

        <table>
            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Profissional</th>
                <th>Data da Consulta</th>
            </tr>

            <?php
            if ($totalRegistros > 0) {
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
