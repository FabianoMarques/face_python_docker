<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Recebendo parâmetros
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "Desconhecido";
$mensagem = isset($_GET['mensagem']) ? htmlspecialchars($_GET['mensagem']) : "";
$score = isset($_GET['score']) ? (float) $_GET['score'] : 1.0;
$imagem = isset($_GET['imagem']) ? htmlspecialchars($_GET['imagem']) : "";
$nomeArquivo = !empty($imagem) ? pathinfo(basename($imagem), PATHINFO_FILENAME) : "";

// Conexão com banco
require_once '../db.php';
$db = new Database();
$conn = $db->getConnection();

// Buscar paciente
$result_paciente = $conn->query("SELECT * FROM paciente WHERE CPF = '{$nomeArquivo}'");
if ($result_paciente && $result_paciente->num_rows > 0) {
    while ($row = $result_paciente->fetch_assoc()) {
        $_SESSION['paciente_nome'] = $row["nome"];
        $_SESSION['paciente_profissional'] = $row["profissional"];
        $_SESSION['idpaciente'] = $row["idpaciente"];
        $_SESSION['vencimento'] = $row["vencimento"];
        $_SESSION['status'] = $row["status"];
    }
}

date_default_timezone_set('America/Sao_Paulo');
$data_hora = date('Y-m-d H:i:s');

// Buscar última consulta se o paciente foi encontrado
$intervaloSegundos = PHP_INT_MAX;
$dtConsulta = '';

if (isset($_SESSION['idpaciente'])) {
    $result_consulta = $conn->query("SELECT * FROM consultas WHERE idpaciente = '{$_SESSION['idpaciente']}' ORDER BY idconsulta DESC LIMIT 1");

    if ($result_consulta && $result_consulta->num_rows > 0) {
        $row = $result_consulta->fetch_assoc();
        $dtConsulta = $row['dt_consulta'];
        $qtd_horas_feitas = $row['qtd_horas_feitas'];

        $dataUltimaConsulta = DateTime::createFromFormat('Y-m-d H:i:s.u', $dtConsulta);
        if (!$dataUltimaConsulta) {
            $dataUltimaConsulta = DateTime::createFromFormat('Y-m-d H:i:s', $dtConsulta);
        }

        if ($dataUltimaConsulta) {
            $dataAtual = new DateTime();
            $intervaloSegundos = $dataAtual->getTimestamp() - $dataUltimaConsulta->getTimestamp();
        } else {
            echo "Erro ao interpretar a data da última consulta: $dtConsulta<br>";
        }
    }

}

//var_dump($intervaloSegundos );
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Verificação</title>
    <link rel="stylesheet" href="botoes.css">
    <link rel="stylesheet" href="avisos.css">
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
        .result-container {
            width: 90%;
            max-width: 800px;
            border-radius: 8px;
            padding: 10px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 400px;
            text-align: center;
        }
        .mensagem {
            font-size: 1.2em;
            margin-top: 20px;
        }
        .contador {
            font-size: 1em;
        }
    </style>
</head>
<body>

<div class="result-container">
<?php
    // Verificar o botão clicado e definir os valores correspondentes
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['confirmar'])) {
            if ($_POST['confirmar'] == 'sim') {
                // Define valores ao clicar em "Sim"
                $confirmado= 'sim';
                $_SESSION['mensagem'] = "Você confirmou que é {$_SESSION['paciente_nome']}.";
                // Aqui, você pode redirecionar ou processar o fluxo conforme necessário
            } elseif ($_POST['confirmar'] == 'nao') {
                // Define valores ao clicar em "Não"
                // Redireciona o usuário para uma nova página
                header("Location: ./");
                exit; // Sempre use exit após header para garantir que o script seja interrompido


                // Aqui, você pode redirecionar ou tomar outra ação conforme necessário
            }
        }
    }
    // Exibe o nome do paciente e os botões
    
?>

<?php if (empty($confirmado)) { 
    echo "<i class='fas fa-question-circle' style='font-size: 64px; margin-top:80px; color:rgb(0, 0, 0);'></i>";
    echo "<p style='margin-top:0px; '><h3>OLÁ VOCÊ É </h3></p><h1 style='margin-top:-20px'>" . $_SESSION['paciente_nome']."?</h1>"; 
    
    ?>
    <!-- Formulário com dois botões -->
    <form method="POST" action="">
        <button type="submit" class="btn_verde" name="confirmar" value="sim">Sim :)</button>
        <button type="submit" class="btn_vermelho" name="confirmar" value="nao">Não :(</button>
    </form>
<?php } ?>

<?php
    // Exibe a mensagem definida pelo clique
//     if (isset($_SESSION['mensagem'])) {
//         echo "<p>{$_SESSION['mensagem']}</p>";
//     }
// ?>

<?php
    if(isset($confirmado) && $confirmado =="sim") {
        if ($score < 0.40) {
            if ($status != "erro") { //NÃO ESTOU USANDO POIS A CAPTURA SÓ É FEITA QUANDO UM ROSTO É ENCONTRADO
                if ($result_paciente && $result_paciente->num_rows > 0) {
                    if ($intervaloSegundos > 3600) { //INTERVALOR DE UMA HORA
                        if ($_SESSION['status']=="Ativo") {
                            echo "<i class='fas fa-check-circle' style='font-size: 60px; color: #27ae60; margin-bottom:10px; margin-top:35px'></i>";
                            echo "<h2>Seja bem-vindo(a) {$_SESSION['paciente_nome']}</h2>";
                            echo "<h2 style='color: red;'>Entrada registrada em $data_hora</h2>";
                            echo "<h3>Seu horário hoje é com '{$_SESSION['paciente_profissional']}'</h3>";
                            echo "<div class='aviso atencao'>Não esqueça que seu vencimento á todo dia '{$_SESSION['vencimento']}'.</div>";
                            echo "<div class='mensagem'>Você será redirecionado em <span class='contador' id='contador'>5</span> segundos...</div>";

                            //SE FOR A PRIMEIRA CONSULTA O VALOR VAI SER  1 SE NÃO VAI SOMAR O QUE TEM + 1
                            if(!empty($qtd_horas_feitas)){ $qtd_horas_feitas_ =  $qtd_horas_feitas + 1; }else{ $qtd_horas_feitas_ =  1; };
                            $idPaciente = (int) $_SESSION['idpaciente'];
                            $profissional = $_SESSION['paciente_profissional'];

                            $result2 = $conn->query("INSERT INTO consultas (idpaciente, profissional, qtd_horas_feitas, dt_consulta) VALUES ($idPaciente, '$profissional', '$qtd_horas_feitas_', '$data_hora')");
                            if ($conn->affected_rows <= 0) {
                                echo "<p style='color:red'>Erro ao cadastrar consulta.</p>";
                            }
                            $db->closeConnection();
                            // Sinaliza que deve redirecionar
                            $redirect = true;

                        } else {
                            echo "<h1 style='margin-top:15%'>Ooops!!!</h1><b>OUVE UM PROBLEMA COM SEU CADASTRO, PROCURE A RECEPÇÃO!!!";
                            echo "<br><a href='./'><button class='btn_verde' style='margin-top:15px;'>Voltar</button></a>";
                        }

                    } else {
                        $dataFormatada = DateTime::createFromFormat('Y-m-d H:i:s', $dtConsulta)->format('d/m/Y \à\s H:i:s');
                        echo "<h1 style='margin-top:15%'>Ooops!!!</h1><b><h2>Olá {$_SESSION['paciente_nome']}, </h2>VOCÊ JÁ REGISTROU SUA ENTRADA EM '$dataFormatada'</b><br>(Intervalo mínimo de uma hora)";
                        echo "<br><a href='./'><button class='btn_verde' style='margin-top:15px;'>Voltar</button></a>";
                    }
                } else {
                    echo "<h1 style='margin-top:15%'>Ooops!!!</h1><b>NENHUM REGISTRO ENCONTRADO!!!</b><br>";
                    echo "<a href='./'><button style='margin-top:15px;' class='btn_vermelho'>Tentar Novamente</button></a>";
                }
            } else {
                //NÃO ESTOU USANDO POIS A CAPTURA SÓ É FEITA QUANDO UM ROSTO É ENCONTRADO
                echo "<h1 style='margin-top:15%'>Ooops!!!</h1>Nenhum rosto encontrado na imagem capturada!!!<br>";
                echo "<a href='./'><button class='btn_vermelho' style='margin-top:15px;'>Tentar Novamente</button></a>";
            }
        } else {
            echo "<h1 style='margin-top:15%'>Ooops!!!</h1><b>IMAGEM NÃO IDENTIFICADA COM SEGURANÇA!!!</b><br>($score)<br>";
            echo "<a href='./'><button class='btn_vermelho' style='margin-top:15px;'>Tentar Novamente</button></a>";
        }

    } else {
    }
?>

</div>


<?= "<p style='color:gray'>".$score."</p>"; ?>

<?php if (isset($redirect) && $redirect): ?>
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
<?php endif; ?>

</body>
</html>
