<?php
session_start();
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "Desconhecido";
$mensagem = isset($_GET['mensagem']) ? htmlspecialchars($_GET['mensagem']) : "";
$score = isset($_GET['score']) ? htmlspecialchars($_GET['score']) : "";
$imagem = isset($_GET['imagem']) ? htmlspecialchars($_GET['imagem']) : "";
$nomeArquivo = !empty($imagem) ? pathinfo(basename($imagem), PATHINFO_FILENAME) : "";

require_once '../db.php';

$db = new Database();
$conn = $db->getConnection();

$result_paciente = $conn->query("SELECT * FROM paciente WHERE CPF = '{$nomeArquivo}'");
if ($result_paciente->num_rows > 0) {
    while ($row = $result_paciente->fetch_assoc()) {
        $_SESSION['paciente_nome'] = $row["nome"];
        $_SESSION['paciente_profissional'] = $row["profissional"];
        $_SESSION['idpaciente'] = $row["idpaciente"];
    }
}

date_default_timezone_set('America/Sao_Paulo');
$data_hora = date('Y-m-d H:i:s');

//A CONSULTA VAI RETORNAR A ULTIMA CONSULTA (IDCONSULTA) PARA AQUELE PACIENTE (IDPACIENTE)
$result_consulta = $conn->query("SELECT * FROM consultas WHERE idpaciente = '{$_SESSION['idpaciente']}' ORDER BY idconsulta DESC LIMIT 1");
if ($result_consulta && $result_consulta->num_rows > 0) {
    $row = $result_consulta->fetch_assoc();
    echo $dtConsulta = $row['dt_consulta'];
    $dataUltimaConsulta = DateTime::createFromFormat('Y-m-d H:i:s', $dtConsulta);
    $dataAtual = new DateTime();
    $intervaloSegundos = $dataAtual->getTimestamp() - $dataUltimaConsulta->getTimestamp();
    var_dump($intervaloSegundos);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Verificação</title>
    <link rel="stylesheet" href="botoes.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #f0f2f5, #dbe9f4);
            height: 100vh;
            margin: 0;
        }
        .result-container, .result-container2 {
            width: 90%;
            max-width: 800px;
            border-radius: 8px;
            padding: 10px;
        }
        .result-container {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .image-container img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
        }
        .mensagem {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .contador {
            font-size: 1em;
        }
    </style>
</head>
<body>

<div class="result-container" style="height: 400px;">
<?php 
if ($score < 0.40) {
    if ($status != "erro") { //não estou usando essse if porque quando não é identificado um rosto a captura não é disparada
        if ($result_paciente->num_rows > 0) {
            if ($intervaloSegundos > 3600) {
?>
                <table style="width: 100%; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td>
                            <i class="fas fa-check-circle" style="font-size: 60px; color: #27ae60; margin-bottom:20px"></i>
                            <h2>Seja bem-vindo(a) <?php echo $_SESSION['paciente_nome']; ?></h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h2 style="color: red;">Entrada registrada em <?php echo $data_hora; ?></h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Seu horário hoje é com '<?php echo $_SESSION['paciente_profissional']; ?>'</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <br><hr style="background-color:#dbe9f4; border-color: #dbe9f4; width: 50%;"><br>
                            <div class="mensagem">Você será redirecionado em <span class="contador" id="contador">5</span> segundos...</div>
                        </td>
                    </tr>
                </table>
<?php
                $idPaciente = (int) $_SESSION['idpaciente'];
                $profissional = $_SESSION['paciente_profissional'];
                $result2 = $conn->query("INSERT INTO consultas (idpaciente, profissional, dt_consulta) VALUES ($idPaciente, '$profissional', '$data_hora')");
                if ($conn->affected_rows <= 0) {
                    echo "Erro ao cadastrar consulta.";
                }
                $db->closeConnection();
            } else {
                $dataFormatada = DateTime::createFromFormat('Y-m-d H:i:s', $dtConsulta)->format('d/m/Y  \à\s  H:i:s');
                echo "<div style='text-align:center;font-size:20px;margin-top:15%;'>
                        <h1>Ooops!!!</h1><b>VOCÊ JÁ REGISTROU SUA ENTRADA <br>EM '". $dataFormatada."'</b><br> (Intervalo minimo de uma hora)<br>
                        <a href='./'><button style='margin-top:15px;padding:10px 20px;font-size:16px;color:white;border:none;border-radius:5px;' class='btn_verde'>Voltar</button></a>
                      </div>";
            }
        } else {
            echo "<div style='text-align:center;font-size:20px;margin-top:40px;margin-top:15%;'>
                    <h1>Ooops!!!</h1><b>NENHUM REGISTRO ENCONTRADO!!!</b><br> 
                    <a href='./'><button style='margin-top:15px;padding:10px 20px;font-size:16px;background-color:#900;color:white;border:none;border-radius:5px;'>Tentar Novamente</button></a>
                  </div>";
        }
    } else {
        //não estou usando esssa mensagem porque quando não é identificado um rosto a captura não é disparada
        echo "<div style='text-align:center;font-size:20px;margin-top:40px;margin-top:15%;'>
                <h1>Ooops!!!</h1>Nenhum rosto encontrado na imagem capturada!!!<br> 
                <a href='./'><button style='margin-top:15px;padding:10px 20px;font-size:16px;background-color:#900;color:white;border:none;border-radius:5px;'>Tentar Novamente</button></a>
              </div>";
    }
} else {
    echo "<div style='text-align:center;font-size:20px;margin-top:40px; margin-top:15%;'>
            <h1>Ooops!!!</h1><b>IMAGEM NÃO IDENTIFICADA COM SEGURANÇA!!!</b><br>($score)<br>
            <a href='./'><button style='margin-top:15px;padding:10px 20px;font-size:16px;background-color:#900;color:white;border:none;border-radius:5px;'>Tentar Novamente</button></a>
          </div>";
}
?>
</div>


<script>
  let segundos = 5;
  const contador = document.getElementById("contador");

  const intervalo = setInterval(() => {
    segundos--;
    contador.textContent = segundos;

    if (segundos <= 0) {
      clearInterval(intervalo);
      window.location.href = "/app/";
    }
  }, 1000);
</script>

</body>
</html>
