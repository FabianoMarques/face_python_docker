<?php
    include("valida.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Foto</title>
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="botoes.css">
    
</head>
<body>

    <div class="container">
        <h1><strong>EXCLUIR FOTO</strong></h1>
        <input type="text" id="nomeImagemExcluir" placeholder="Digite o nome da imagem para excluir" class="input_estilizado">
        <br>
        <button id="verificar" class="btn_verde">VERIFICAR E EXCLUIR IMAGEM</button>
        <div id="loading" class="loading"><strong>PROCESSANDO... POR FAVOR AGUARDE!!!</strong></div>

        <div id="imagemEncontrada" class="imagem-exibida" style="display:none;">
            <h2>Imagem Encontrada:</h2>
            <img id="imagemExibida" src="" alt="Imagem a ser excluída">
            <br>
            <button id="excluir" class="btn_vermelho">EXCLUIR IMAGEM</button>
        </div>
        <button class="btn_branco" onclick="window.location.href='/App/menu.php'">MENU</button>
    </div>

    <script>
        const verificarBtn = document.getElementById("verificar");
        const excluirBtn = document.getElementById("excluir");
        const nomeImagemExcluirInput = document.getElementById("nomeImagemExcluir");
        const loadingDiv = document.getElementById("loading");
        const imagemExibida = document.getElementById("imagemExibida");
        const imagemEncontradaDiv = document.getElementById("imagemEncontrada");

        // Verificar a imagem
        verificarBtn.addEventListener("click", () => {
            const nomeImagemExcluir = nomeImagemExcluirInput.value.trim();
            if (!nomeImagemExcluir) {
                alert("Por favor, digite o nome da imagem a ser excluída!");
                return;
            }

            loadingDiv.style.display = 'block';
            imagemEncontradaDiv.style.display = 'none'; // Esconder a imagem exibida

            fetch("excluir_verificar_imagem.php", {
                method: "POST",
                body: JSON.stringify({ nome: nomeImagemExcluir }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "sucesso") {
                    imagemExibida.src = data.imagem; // Exibe a imagem
                    imagemEncontradaDiv.style.display = 'block'; // Mostrar a imagem encontrada
                    excluirBtn.disabled = false; // Ativar o botão de exclusão
                } else {
                    alert(data.mensagem); // Exibir mensagem de erro
                    imagemEncontradaDiv.style.display = 'none'; // Esconder caso não encontre
                    excluirBtn.disabled = true; // Desativar o botão de exclusão
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Ocorreu um erro ao verificar a imagem.");
            })
            .finally(() => {
                loadingDiv.style.display = 'none';
            });
        });

        // Excluir a imagem
        excluirBtn.addEventListener("click", () => {
            const nomeImagemExcluir = nomeImagemExcluirInput.value.trim();

            if (confirm("Deseja excluir esta imagem?")) {
                loadingDiv.style.display = 'block';

                fetch("excluir_imagem.php", {
                    method: "POST",
                    body: JSON.stringify({ nome: nomeImagemExcluir }),
                    headers: { "Content-Type": "application/json" }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "sucesso") {
                        alert("Imagem excluída com sucesso!");
                        imagemEncontradaDiv.style.display = 'none'; // Esconder a imagem
                    } else {
                        alert(data.mensagem);
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    alert("Ocorreu um erro ao excluir a imagem.");
                })
                .finally(() => {
                    loadingDiv.style.display = 'none';
                });
            }
        });
    </script>
    

</body>
</html>
