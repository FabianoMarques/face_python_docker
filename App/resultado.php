<?php
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "Desconhecido";
$mensagem = isset($_GET['mensagem']) ? htmlspecialchars($_GET['mensagem']) : "";
$score = isset($_GET['score']) ? htmlspecialchars($_GET['score']) : "";
$imagem = isset($_GET['imagem']) ? htmlspecialchars($_GET['imagem']) : "";
$nomeArquivo = !empty($imagem) ? pathinfo(basename($imagem), PATHINFO_FILENAME) : "";

require_once '../db.php';

// Criar uma instância da classe Database
$db = new Database();
$conn = $db->getConnection();
// Fazer uma consulta
$result = $conn->query( "SELECT * FROM paciente WHERE CPF = '{$nomeArquivo}'");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $_SESSION['paciente_nome'] = $row["nome"];
        $_SESSION['paciente_profissional'] = $row["profissional"];
        $_SESSION['idpaciente'] = $row["idpaciente"];
    }
}

$result = $conn->query("SELECT * FROM paciente WHERE CPF = '$nomeArquivo'");


// Fechar a conexão
//$db->closeConnection();


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Verificação</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f9;
            height: 100vh;
            margin: 0;
        }
        h2 {
            color: #333;
        }
        .result-container {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width:90%;
            max-width: 800px;
        }
        .result-container2 {
            display: flex;
            align-items: center;
          
            padding: 20px;
            border-radius: 8px;
            
            width:90%;
            max-width: 800px;
        }
        .image-container {
            margin-right: 15px;
        }
        .image-container img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .result-text {
            flex: 1;
            text-align: left;
        }
       .result-text2 {
            flex: 1;
            text-align: left;
        }
        .error {
            color: red;
            font-size: 18px;
        }
        .success {
            color: green;
            font-size: 18px;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <!-- <h2>Resultado da Verificação Facial</h2> -->
    <div class="result-container" style="height: 400px;">
        <?php 
            if ($score < 0.40){
                if ($status !="erro"){ 
                    if($result->num_rows > 0){?>
                        <table style="width: 100%; border-collapse: collapse; text-align: center;">
                            <tr>
                                <td style="padding: 5px;">
                                    <h2 style="margin: 5px 0;">Seja bem-vindo(a) <?php echo $_SESSION['paciente_nome']; ?></h2>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;">
                                    <?php
                                    date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário para Brasília
                                    $data_hora = date('Y-m-d H:i:s'); // Formato: DD/MM/AAAA HH:MM:SS
                                    echo "<h2 style='color: red; margin: 5px 0;'>Entrada registrada em " . $data_hora . "</h2>";
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;">
                                    <h3 style="margin: 5px 0;">Seu horário hoje é com '<?php echo $_SESSION['paciente_profissional']; ?>'</h3>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;">
                                    <a href="./" class="back-link">Tentar Novamente</a>
                                </td>
                            </tr>
                        </table>
                        
                         <?php
                            //SCRITP PARA A INSERÇÃO DESSES DADOS NO BANCO
                            $idPaciente = (int) $_SESSION['idpaciente']; // Converte para inteiro
                            $profissional = $_SESSION['paciente_profissional']; // Evita SQL Injection
                            $data_hora = date('Y-m-d H:i:s'); // Exemplo: "2025-04-04 14:30:00"

                            $result2 = $conn->query("INSERT INTO consultas (idpaciente, profissional, dt_consulta) VALUES ($idPaciente, '$profissional', '$data_hora')");
                            if ($conn->affected_rows > 0) {
                                //echo "Paciente cadastrado com sucesso!";
                            } else {
                                echo "Erro ao cadastrar consulta.";
                                // Depuração: Exibe a query antes de executar
                            }

                            $db->closeConnection();
                         ?>




                    <?php } else {  echo "
                        <div style='display: flex; flex-direction: column; align-items: center; 
                        justify-content: center; text-align: center; font-size: 20px; 
                        font-weight: bold; margin-top: 40px; padding: 40px; 
                        background-color:rgb(250, 250, 250); border-radius: 5px; width: 50%; 
                        margin-left: auto; margin-right: auto;'>
                        <h1>Ooops!!!</h1>NENHUM REGISTRO ENCONTRADO!!!<br> 
                        <a href='./' style='text-decoration: none;'>
                            <button style='margin-top: 15px; padding: 10px 20px; font-size: 16px; 
                                    background-color: #900; color: white; border: none; 
                                    border-radius: 5px; cursor: pointer;'>
                                Tentar Novamente
                            </button>
                            </a>
                            </div>";
                     } ?>
                        
                <?php }else{ echo "
                <div style='display: flex; flex-direction: column; align-items: center; 
                    justify-content: center; text-align: center; font-size: 20px; 
                    font-weight: bold; margin-top: 40px; padding: 40px; 
                    background-color:rgb(250, 250, 250); border-radius: 5px; width: 50%; 
                    margin-left: auto; margin-right: auto;'>
                    <h1>Ooops!!!</h1>Nenhum rosto encontrado na imagem capturada!!!<br> 
                    <a href='./' style='text-decoration: none;'>
                        <button style='margin-top: 15px; padding: 10px 20px; font-size: 16px; 
                                background-color: #900; color: white; border: none; 
                                border-radius: 5px; cursor: pointer;'>
                            Tentar Novamente
                        </button>
                    </a>
                    </div>
                "; }

            }else{ echo "
            <div style='display: flex; flex-direction: column; align-items: center; 
                justify-content: center; text-align: center; font-size: 20px; 
                font-weight: bold; margin-top: 40px; padding: 40px; 
                background-color:rgb(250, 250, 250); border-radius: 5px; width: 50%; 
                margin-left: auto; margin-right: auto;'>
            <h1>Ooops!!!</h1>IMAGEM NÃO IDENTIFICADA COM SEGURANÇA, TENTE NOVAMENTE!!!<br>(".$score.")
            <a href='./' style='text-decoration: none;'>
                <button style='margin-top: 15px; padding: 10px 20px; font-size: 16px; 
                        background-color: #900; color: white; border: none; 
                        border-radius: 5px; cursor: pointer;'>
                    Tentar Novamente
                </button>
            </a>
            </div>
      "; } ?>

    </div>

    <div class="result-container2">  
        <!-- <?php if (!empty($imagem)): ?>
            <div class="image-container">
                <img src="<?php echo $imagem; ?>" alt="Imagem correspondente">
            </div>
        <?php endif; ?>

        <div class="result-text2">
            <?php if ($status == "erro"): ?>
                <p class="error"><strong>Erro:</strong> <?php  echo $mensagem; ?></p> 
            <?php else: ?>
                <p class="success"><strong>Status:</strong> <?php echo $status; ?></p>
                <p><strong>Score de Semelhança:</strong> <?php echo $score; ?></p>
                <?php if (!empty($nomeArquivo)): ?>
                    <p><strong>CPF:</strong> <?php echo $nomeArquivo; ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div> -->

    </div>

    <!-- <a href="./" class="back-link">Tentar Novamente</a> -->

</body>
</html>