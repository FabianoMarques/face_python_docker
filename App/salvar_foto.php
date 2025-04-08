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

// Executar script Python
$command = "python3 detectar_faces.py " . escapeshellarg($arquivo_temp) . " imagens/ 2>&1";
$output = shell_exec($command);
$resultado = json_decode(trim($output), true);

unlink($arquivo_temp); // Exclui a imagem temporária

if (!$resultado || isset($resultado['erro'])) {
    echo "resultado.php?status=erro&mensagem=Não foi possível identificar nenhuma face.";
    exit;
}

// Redirecionar com resultado
$score = urlencode($resultado['score']);
$imagem = urlencode($resultado['imagem']);
echo "resultado.php?status=sucesso&score={$score}&imagem={$imagem}";
exit;
?>
