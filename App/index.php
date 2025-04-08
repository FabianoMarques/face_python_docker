<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login por Reconhecimento Facial</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="botoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="face-api.min.js"></script>
</head>
<body>

<h1 class="titulo-facial">
  Bem-vindo(a)!<br><span>Autentique-se com seu rosto</span>
</h1>

<div style="text-align:center;">
    <video id="videoAuto" autoplay muted playsinline style="border-radius: 10px; border: 2px solid #ccc;"></video>
    <canvas id="canvasAuto" style="display:none;"></canvas>
    <div id="contador" style="margin-top: 15px; font-size: 20px; font-weight: bold;">Carregando modelos...</div>
</div>

<div id="previewContainer" style="display:none; text-align:center; margin-top:20px;">
<img id="previewImage" src="" alt="Prévia da imagem" style="width: 300px; height: 400px; object-fit: cover; border:2px solid #ccc; border-radius:10px;">
    <div style="margin-top:10px;">
        <button id="confirmarBtn" style="padding: 10px 20px; margin-right: 10px; width:200px" class="btn_verde">Confirmar</button>
        <button id="refazerBtn" style="padding: 10px 20px; width:200px" class="btn_branco"><i class="fas fa-rotate-right"></i> Refazer</button>
    </div>
</div>

<script>
    const video = document.getElementById('videoAuto');
    const canvas = document.getElementById('canvasAuto');
    const context = canvas.getContext('2d');
    const contador = document.getElementById('contador');

    let deteccoesSeguidas = 0;
    let emCooldown = false;

    async function carregarModelos() {
        await faceapi.nets.tinyFaceDetector.loadFromUri('models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('models');
        contador.innerText = 'Modelos carregados.';
    }

    async function iniciarWebcam() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
        } catch (err) {
            console.error("Erro ao acessar webcam:", err);
            contador.innerText = "Erro ao acessar webcam.";
        }
    }

    async function detectarRostoEDesenhar() {
        const opcoes = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
        const deteccao = await faceapi.detectSingleFace(video, opcoes);

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.clearRect(0, 0, canvas.width, canvas.height);

        if (deteccao) {
            faceapi.draw.drawDetections(canvas, faceapi.resizeResults(deteccao, { width: canvas.width, height: canvas.height }));
        }
        return deteccao !== undefined;
    }

    async function capturarImagem() {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.save();
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        context.restore();

        return canvas.toDataURL('image/png');
    }

    async function enviarImagem(imagemBase64) {
        contador.innerText = "Enviando imagem...";
        contador.style.color = "green";
        const resposta = await fetch('salvar_foto.php', {
            method: 'POST',
            body: JSON.stringify({ imagem: imagemBase64 }),
            headers: { 'Content-Type': 'application/json' }
        });
        const url = await resposta.text();
        window.location.href = url;
    }

    function mostrarPreview(imagemBase64) {
        document.getElementById('previewImage').src = imagemBase64;
        document.getElementById('previewContainer').style.display = 'block';
        video.style.display = 'none';
        contador.innerText = "Confirme a imagem capturada.";
        contador.style.color = "green";

        document.getElementById('confirmarBtn').onclick = async () => {
            await enviarImagem(imagemBase64);
        };

        document.getElementById('refazerBtn').onclick = () => {
            document.getElementById('previewContainer').style.display = 'none';
            video.style.display = 'block';
            deteccoesSeguidas = 0;
            emCooldown = false;
            aguardarRostoEDisparar();
        };
    }

    async function aguardarRostoEDisparar() {
        contador.innerText = 'Aguardando rosto...';
        contador.style.color = "black";

        const intervalo = setInterval(async () => {
            if (emCooldown) return;

            const rostoDetectado = await detectarRostoEDesenhar();

            if (rostoDetectado) {
                deteccoesSeguidas++;
                console.log(`Detecções seguidas: ${deteccoesSeguidas}`);
                if (deteccoesSeguidas >= 3) {
                    clearInterval(intervalo);
                    deteccoesSeguidas = 0;
                    emCooldown = true;

                    let segundos = 3;
                    contador.innerText = `Capturando em ${segundos}...`;

                    const countdown = setInterval(() => {
                        segundos--;
                        contador.innerText = `Capturando em ${segundos}...`;
                        if (segundos < 0) clearInterval(countdown);
                    }, 1000);

                    setTimeout(async () => {
                        const imagemBase64 = await capturarImagem();
                        mostrarPreview(imagemBase64);

                        // Cooldown de 10 segundos antes de permitir novo registro
                        setTimeout(() => {
                            emCooldown = false;
                        }, 10000);
                    }, 4000);
                }
            } else {
                deteccoesSeguidas = 0;
            }
        }, 300);
    }

    window.onload = async () => {
        await carregarModelos();
        await iniciarWebcam();
        aguardarRostoEDisparar();
    };
</script>

</body>
</html>
