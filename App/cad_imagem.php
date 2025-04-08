<?php
    include("valida.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="botoes.css">
    <title>Capturar e Salvar Foto</title>
    <style>
        video {
            transform: scaleX(-1);
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 4px solid #2980b9;
            width: 560px;
            height: 480px;
            object-fit: cover;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><strong>CAPTURAR E SALVAR FOTO</strong></h1>
        
        <!-- Adicionando o elemento de vídeo -->
        <video id="video" autoplay playsinline></video>

        <canvas id="canvas" width="640" height="480" style="display: none;"></canvas>

        <input type="text" id="nomeImagem" placeholder="Digite o nome da imagem" class="input_estilizado">
        <button id="capturar" class="btn_verde">CAPTURAR E SALVAR</button>
        <button class="btn_branco" onclick="location.href='menu.php'">MENU</button>
        <div id="loading" class="loading"><strong>PROCESSANDO... POR FAVOR AGUARDE!!!</strong></div>
    </div>

    <script>
        const video = document.getElementById("video");
        const canvas = document.getElementById("canvas");
        const context = canvas.getContext("2d");

        const canvasOculto = document.createElement("canvas");
        canvasOculto.width = 640;
        canvasOculto.height = 480;
        const contextOculto = canvasOculto.getContext("2d");

        const capturarBtn = document.getElementById("capturar");
        const nomeImagemInput = document.getElementById("nomeImagem");
        const loadingDiv = document.getElementById("loading");

        // Iniciar a webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                requestAnimationFrame(atualizarCanvas);
            })
            .catch(err => { 
                alert("Erro ao acessar a webcam. Verifique as permissões.");
                console.error("Erro ao acessar a webcam", err);
            });

        function atualizarCanvas() {
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const rectWidth = 250;
            const rectHeight = 350;
            const x = (canvas.width - rectWidth) / 2;
            const y = (canvas.height - rectHeight) / 2;
            context.strokeStyle = "red";
            context.lineWidth = 2;
            context.strokeRect(x, y, rectWidth, rectHeight);
            requestAnimationFrame(atualizarCanvas);
        }

        capturarBtn.addEventListener("click", () => {
            const nomeImagem = nomeImagemInput.value.trim();

            if (!nomeImagem) {
                alert("Por favor, digite um nome para a imagem!");
                return;
            }

            loadingDiv.style.display = 'block';

            //contextOculto.drawImage(video, 0, 0, canvasOculto.width, canvasOculto.height);
            contextOculto.save(); // salva o estado original
            contextOculto.scale(-1, 1); // inverte horizontalmente
            contextOculto.drawImage(video, -canvasOculto.width, 0, canvasOculto.width, canvasOculto.height);
            contextOculto.restore(); // restaura o estado original

            const dataUrl = canvasOculto.toDataURL("image/png");

            fetch("cad_salvar_foto.php", {
                method: "POST",
                body: JSON.stringify({ imagem: dataUrl, nome: nomeImagem }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json().catch(() => { throw new Error("Resposta inválida do servidor"); }))
            .then(data => {
                if (data.status === "sucesso") {
                    alert("Imagem salva com sucesso!");
                    nomeImagemInput.value = "";
                } else {
                    alert(data.mensagem);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Ocorreu um erro ao salvar a imagem.");
            })
            .finally(() => {
                loadingDiv.style.display = 'none';
            });
        });

        window.addEventListener("beforeunload", () => {
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
        });
    </script>

</body>
</html>
