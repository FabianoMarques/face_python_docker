<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['imagem'])) {
    echo "resultado.php?status=erro&mensagem=Nenhuma imagem recebida.";
    exit;
}

$imagem_base64 = str_replace("data:image/png;base64,", "", $data['imagem']);
$imagem_decodificada = base64_decode($imagem_base64);

if ($imagem_decodificada === false) {
    echo "resultado.php?status=erro&mensagem=Erro ao decodificar a imagem.";
    exit;
}

// Criar arquivo temporário
$arquivo_temp = tempnam(sys_get_temp_dir(), "captura_") . ".jpg";
file_put_contents($arquivo_temp, $imagem_decodificada);

$arquivo = "detectar_faces.py";

// Executar script Python
$command = "python3 ". escapeshellarg($arquivo) ." ". escapeshellarg($arquivo_temp) . " imagens/ 2>&1";
//var_dump($command);
$output = shell_exec($command);
$resultado = json_decode(trim($output), true);

unlink($arquivo_temp); // Exclui a imagem temporária

if (!$resultado || isset($resultado["error"])) {
    $mensagem = isset($resultado["error"]) ? urlencode($resultado["error"]) : "Erro no reconhecimento facial.";
    echo "resultado.php?status=erro&mensagem={$mensagem}";
    exit;
}

$status = isset($resultado["status"]) ? urlencode($resultado["status"]) : "Desconhecido";
$score = isset($resultado["score"]) ? urlencode($resultado["score"]) : "N/A";
$imagem = isset($resultado["imagem"]) ? urlencode($resultado["imagem"]) : "Nenhuma";

// Em vez de header("Location: ..."), retornamos a URL para o JavaScript
echo "resultado.php?status={$status}&score={$score}&imagem={$imagem}";
exit;
?>
