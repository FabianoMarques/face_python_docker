<?php
    include("valida.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturar e Salvar Foto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        h1 {
            color: #333;
        }
        canvas {
            border: 2px solid #333;
            border-radius: 8px;
        }
        input, button {
            width: 640px; /* Largura igual à webcam */
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            border-radius: 5px;
            text-align: center;
        }
        input {
            border: 1px solid #ccc;
        }
        button {
            height: 50px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        .capture-btn {
            background-color: #4CAF50;
            color: white;
        }
        .capture-btn:hover {
            background-color: #45a049;
        }
        .menu-btn {
            background-color: #ccc;
            color: black;
        }
        .menu-btn:hover {
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
        <h1><strong>CAPTURAR E SALVAR FOTO</strong></h1>
        <canvas id="canvas" width="640" height="480"></canvas>
        <input type="text" id="nomeImagem" placeholder="Digite o nome da imagem">
        <button id="capturar" class="capture-btn">CAPTURAR E SALVAR</button>
        <button class="menu-btn" onclick="location.href='menu.php'">MENU</button>
        <div id="loading" class="loading"><strong>PROCESSANDO... POR FAVOR AGUARDE!!!</strong></div>
    </div>

    <script>
        const video = document.createElement("video");
        video.setAttribute("autoplay", "");
        video.setAttribute("playsinline", ""); 

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

            contextOculto.drawImage(video, 0, 0, canvasOculto.width, canvasOculto.height);
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
