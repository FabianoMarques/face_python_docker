<?php
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["nome"])) {
    $nomeArquivo = preg_replace("/[^a-zA-Z0-9_-]/", "", $data["nome"]); // Remover caracteres inválidos
    $caminho = "imagens/" . $nomeArquivo . ".jpg"; // Caminho da imagem

    // Verificar se a imagem existe
    if (file_exists($caminho)) {
        echo json_encode(["status" => "sucesso", "imagem" => $caminho]); // Envia o caminho da imagem
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "A imagem não foi encontrada."]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos."]);
}
?>
