<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Login Facial</title>
  <script defer src="face-api.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: black;
      height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
      color: white;
    }

    .fullscreen-container {
      position: absolute;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    video, canvas {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
    }

    .overlay {
      position: absolute;
      top: 10%;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
      font-size: 2rem;
      z-index: 10;
    }

    .contador {
      position: absolute;
      top: 20%;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
      font-size: 2rem;
      z-index: 10;
    }

    .controls {
      position: absolute;
      bottom: 10%;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      gap: 1rem;
    }

    button {
      padding: 1rem 2rem;
      font-size: 1rem;
      background-color: #4F46E5;
      color: white;
      border: none;
      border-radius: 1rem;
      cursor: pointer;
      transition: all 0.3s;
    }

    button:hover {
      background-color: #4338ca;
    }

    .image-preview {
      position: absolute;
      top: 20%;
      left: 50%;
      transform: translateX(-50%);
      z-index: 20;
      text-align: center;
    }

    .image-preview img {
      max-width: 80%;
      margin-bottom: 2rem;
    }

    button.confirmar {
      background-color: #22c55e;
    }

    button.confirmar:hover {
      background-color: #16a34a;
    }

    button.refazer {
      background-color: #ef4444;
    }

    button.refazer:hover {
      background-color: #dc2626;
    }
  </style>
</head>
<body>

  <div class="fullscreen-container">
    <video id="videoAuto" autoplay muted playsinline></video>
    <canvas id="canvasOverlay"></canvas>

    <div class="overlay" id="capturaText">Posicione seu rosto</div>
    <div class="contador" id="contador">Aguardando rosto...</div>

    <div class="controls" id="controlsContainer" style="display: none;">
      <button id="confirmarBtn" class="confirmar">Confirmar</button>
      <button class="refazer" id="refazerBtn">Refazer</button>
    </div>
  </div>

  <script>
    const video = document.getElementById('videoAuto');
    const canvas = document.getElementById('canvasOverlay');
    const capturaText = document.getElementById('capturaText');
    const contador = document.getElementById('contador');
    const confirmarBtn = document.getElementById('confirmarBtn');
    const refazerBtn = document.getElementById('refazerBtn');
    const controlsContainer = document.getElementById('controlsContainer');

    let deteccoesSeguidas = 0;
    let emCooldown = false;

    async function carregarModelos() {
      await faceapi.nets.tinyFaceDetector.loadFromUri('models');
      await faceapi.nets.faceLandmark68Net.loadFromUri('models');
      console.log("Modelos carregados.");
      contador.innerText = 'Modelos carregados. Aguardando rosto...';
    }

    async function iniciarWebcam() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        console.log("Webcam iniciada.");
      } catch (err) {
        contador.innerText = 'Erro ao acessar a webcam';
        contador.style.color = 'red';
        console.error("Erro ao acessar a webcam:", err);
      }
    }

    async function detectarRostoEDesenhar() {
      const displaySize = { width: video.videoWidth, height: video.videoHeight };
      faceapi.matchDimensions(canvas, displaySize);

      const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });

      const result = await faceapi
        .detectSingleFace(video, options)
        .withFaceLandmarks();

      const context = canvas.getContext('2d');
      context.clearRect(0, 0, canvas.width, canvas.height);

      if (result) {
        const resized = faceapi.resizeResults(result, displaySize);
        faceapi.draw.drawFaceLandmarks(canvas, resized);
        return true;
      }

      return false;
    }

    async function capturarImagem() {
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      return canvas.toDataURL('image/png');
    }

    async function enviarImagem(imgBase64) {
      const response = await fetch('salvar_foto.php', {
        method: 'POST',
        body: JSON.stringify({ imagem: imgBase64 }),
        headers: { 'Content-Type': 'application/json' }
      });
      const url = await response.text();
      window.location.href = url;
    }

    function mostrarControles() {
      controlsContainer.style.display = 'flex';
    }

    function mostrarImagem(imgBase64) {
      const imagePreview = document.createElement('div');
      imagePreview.classList.add('image-preview');

      const previewImage = document.createElement('img');
      previewImage.src = imgBase64;
      imagePreview.appendChild(previewImage);

      document.body.appendChild(imagePreview);
    }

    async function aguardarDeteccao() {
      const loop = setInterval(async () => {
        if (emCooldown) return;
        const detectado = await detectarRostoEDesenhar();
        if (detectado) {
          deteccoesSeguidas++;
          contador.innerText = `Rosto detectado (${deteccoesSeguidas}/3)...`;
          if (deteccoesSeguidas >= 3) {
            clearInterval(loop);
            contador.innerText = 'Capturando imagem...';
            emCooldown = true;
            setTimeout(async () => {
              const img = await capturarImagem();
              mostrarImagem(img);
              mostrarControles();
              setTimeout(() => {
                emCooldown = false;
              }, 10000);
            }, 3000);
          }
        } else {
          deteccoesSeguidas = 0;
          contador.innerText = 'Aguardando rosto...';
        }
      }, 300);
    }

    window.onload = async () => {
      await carregarModelos();
      await iniciarWebcam();
      desenharMiraCentral();
      aguardarDeteccao();
    };

    refazerBtn.onclick = () => {
      deteccoesSeguidas = 0;
      contador.innerText = 'Posicione seu rosto';
      video.style.display = 'block';
      controlsContainer.style.display = 'none';
      document.querySelector('.image-preview')?.remove();
      aguardarDeteccao();
    };

    confirmarBtn.onclick = () => {
      const imgBase64 = document.querySelector('.image-preview img')?.src;
      if (imgBase64) {
        enviarImagem(imgBase64);
      } else {
        alert('Imagem não encontrada. Por favor, tente novamente.');
      }
    };

    function desenharMiraCentral() {
  const ctx = canvas.getContext('2d');
  const largura = canvas.width;
  const altura = canvas.height;

  // Aguarda o vídeo carregar para pegar as dimensões reais
  video.addEventListener('loadedmetadata', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
    ctx.lineWidth = 2;

    // Círculo central
    ctx.beginPath();
    ctx.arc(centerX, centerY, 40, 0, 2 * Math.PI);
    ctx.stroke();

    // Cruz (horizontal e vertical)
    ctx.beginPath();
    ctx.moveTo(centerX - 30, centerY);
    ctx.lineTo(centerX + 30, centerY);
    ctx.moveTo(centerX, centerY - 30);
    ctx.lineTo(centerX, centerY + 30);
    ctx.stroke();
  });
}

  </script>

</body>
</html>
