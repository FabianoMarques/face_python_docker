<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturar e Salvar Foto</title>
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
        h1 {
            color: #333;
        }
        canvas {
            border: 2px solid #333;
            border-radius: 8px;
        }
        input {
            width: 300px;
            padding: 10px;
            margin: 10px;
            font-size: 16px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 320px;
            height: 50px;
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .loading {
            display: none;
            margin-top: 20px;
            font-size: 20px;
            color: rgb(255, 0, 0);
        }
        .container {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><strong>CAPTURAR E SALVAR FOTO</strong></h1>
        <canvas id="canvas" width="640" height="480"></canvas>
        <br>
        <input type="text" id="nomeImagem" placeholder="Digite o nome da imagem">
        <br>
        <button id="capturar">CAPTURAR E SALVAR</button>
        <div id="loading" class="loading"><strong>PROCESSANDO... POR FAVOR AGUARDE!!!</strong></div>
    </div>

    <script>
        const video = document.createElement("video");
        video.setAttribute("autoplay", "");
        video.setAttribute("playsinline", ""); 

        const canvas = document.getElementById("canvas");
        const context = canvas.getContext("2d");

        // Criar um segundo canvas oculto para capturar a imagem sem o retângulo
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
            .catch(err => { console.error("Erro ao acessar a webcam", err); });

        function atualizarCanvas() {
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Definir as dimensões do retângulo de referência
            const rectWidth = 250;
            const rectHeight = 350;
            const x = (canvas.width - rectWidth) / 2;
            const y = (canvas.height - rectHeight) / 2;

            // Desenhar o retângulo vermelho apenas na visualização
            context.strokeStyle = "red";
            context.lineWidth = 2;
            context.strokeRect(x, y, rectWidth, rectHeight);

            requestAnimationFrame(atualizarCanvas);
        }

        // Capturar e enviar imagem sem o retângulo
        capturarBtn.addEventListener("click", () => {
            const nomeImagem = nomeImagemInput.value.trim();
            if (!nomeImagem) {
                alert("Por favor, digite um nome para a imagem!");
                return;
            }

            loadingDiv.style.display = 'block';

            // Copiar a imagem do vídeo para o canvas oculto (sem o retângulo)
            contextOculto.drawImage(video, 0, 0, canvasOculto.width, canvasOculto.height);
            const dataUrl = canvasOculto.toDataURL("image/png");

            fetch("cad_salvar_foto.php", {
                method: "POST",
                body: JSON.stringify({ imagem: dataUrl, nome: nomeImagem }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json()) // Agora esperamos um JSON como resposta
            .then(data => {
                if (data.status === "sucesso") {
                    alert("Imagem salva com sucesso!");
                    nomeImagemInput.value = ""; // Limpar o campo de nome
                } else {
                    alert(data.mensagem); // Exibir mensagem de erro (ex: "A imagem já está cadastrada.")
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

    </script>

</body>
</html>
