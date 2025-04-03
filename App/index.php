<?php
    include("valida.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturar e Analisar Foto</title>
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
        .container {
            position: relative;
            text-align: center;
        }
        canvas {
            border: 2px solid #333;
            border-radius: 8px;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        button {
            width: 640px;
            height: 50px;
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        #capturar {
            background-color: #4CAF50;
            color: white;
        }
        #capturar:hover {
            background-color: #45a049;
        }
        #menu {
            background-color: #ccc;
            color: black;
        }
        #menu:hover {
            background-color: #bbb;
        }
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            color: rgb(255, 0, 0);
            background: rgba(255, 255, 255, 0.7);
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><strong>REGISTRAR ENTRADA</strong></h1>
        <div id="loading" class="loading"><strong>PROCESSANDO... POR FAVOR AGUARDE!!!</strong></div>
        <canvas id="canvas" width="640" height="480"></canvas>
        <div class="button-container">
            <button id="capturar">REGISTRAR</button>
            <button id="menu">MENU</button>
        </div>
    </div>

    <script>
        const video = document.createElement("video");
        video.setAttribute("autoplay", "");
        video.setAttribute("playsinline", ""); 

        const canvas = document.getElementById("canvas");
        const context = canvas.getContext("2d");

        const capturarBtn = document.getElementById("capturar");
        const menuBtn = document.getElementById("menu");
        const loadingDiv = document.getElementById("loading");

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                requestAnimationFrame(atualizarCanvas);
            })
            .catch(err => { console.error("Erro ao acessar a webcam", err); });

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

        function enviarImagem(url) {
            loadingDiv.style.display = 'block';
            const canvasOculto = document.createElement("canvas");
            canvasOculto.width = 640;
            canvasOculto.height = 480;
            const contextOculto = canvasOculto.getContext("2d");
            contextOculto.drawImage(video, 0, 0, canvasOculto.width, canvasOculto.height);
            const dataUrl = canvasOculto.toDataURL("image/png");

            fetch(url, {
                method: "POST",
                body: JSON.stringify({ imagem: dataUrl }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.text())
            .then(data => { window.location.href = data; })
            .catch(error => {
                console.error("Erro:", error);
                alert("Ocorreu um erro ao processar a imagem.");
            })
            .finally(() => {
                loadingDiv.style.display = 'none';
            });
        }

        capturarBtn.addEventListener("click", () => enviarImagem("salvar_foto.php"));
        menuBtn.addEventListener("click", () => window.location.href = "menu.php");
    </script>

</body>
</html>
