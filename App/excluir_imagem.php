<?php
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["nome"])) {
    $nomeArquivo = preg_replace("/[^a-zA-Z0-9_-]/", "", $data["nome"]); // Remover caracteres inválidos
    $caminho = "imagens/" . $nomeArquivo . ".jpg"; // Caminho da imagem

    // Verificar se a imagem existe
    if (file_exists($caminho)) {
        if (unlink($caminho)) { // Excluir a imagem
            echo json_encode(["status" => "sucesso", "mensagem" => "Imagem excluída com sucesso!"]);
        } else {
            echo json_encode(["status" => "erro", "mensagem" => "Não foi possível excluir a imagem."]);
        }
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "A imagem não foi encontrada."]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos."]);
}
?>
