<?php
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["imagem"]) && isset($data["nome"])) {
    $imagem = $data["imagem"];
    $nomeArquivo = preg_replace("/[^a-zA-Z0-9_-]/", "", $data["nome"]); // Remover caracteres inválidos
    $caminho = "imagens/" . $nomeArquivo . ".jpg"; // Caminho onde a imagem será salva

    // Criar a pasta "imagens" se não existir
    if (!file_exists("imagens")) {
        mkdir("imagens", 0777, true);
    }

    // Verificar se a imagem já existe
    if (file_exists($caminho)) {
        echo json_encode(["status" => "erro", "mensagem" => "A imagem já está cadastrada."]);
        exit;
    }

    // Remover o prefixo "data:image/png;base64,"
    $imagem = str_replace("data:image/png;base64,", "", $imagem);
    $imagem = base64_decode($imagem);

    // Salvar a imagem no servidor
    if (file_put_contents($caminho, $imagem)) {
        //AO CADASTRAR NOVA IMAGEM O SCRIPT DO PYTHON É EXECUTADO PARA 
        // ATUALIZAR O ARQUIVO COM OS DADOS DAS IMAGENS (ENCODING.PICKLE)
        $comando = "python3 atualizar_encodings.py";  // Caminho absoluto para o script Python
        $resultado = shell_exec($comando);  // Executa o comando e captura o resultado
        echo json_encode(["status" => "sucesso", "mensagem" => "Imagem salva com sucesso!", "caminho" => $caminho]);

    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao salvar a imagem."]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos."]);
}
?>
