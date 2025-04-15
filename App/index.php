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
      padding: 1rem 2rem;
      background-color: rgba(0, 0, 0, 0.5); 
      color: #fff;
      font-size: 1.75rem;
      font-weight: 600;
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      text-align: center;
      z-index: 10;
      backdrop-filter: blur(4px);
    }

    .contador {
      position: absolute;
      top: 18%;
      left: 50%;
      transform: translateX(-50%);
      padding: 1rem 2rem;
      color: #fff;
      font-size: 2.5rem;
      font-weight: bold;
      color:rgb(255, 255, 255);
      border-radius: 1rem;
      text-align: center;
      z-index: 10;
      backdrop-filter: blur(4px);
      animation: popIn 0.4s ease-out;
      text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
    }

    @keyframes popIn {
      0% {
        opacity: 0;
        transform: translate(-50%, -30%) scale(0.8);
      }
      100% {
        opacity: 1;
        transform: translate(-50%, 0) scale(1);
      }
    }

    .controls {
      position: absolute;
      bottom: 10%;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: none;
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

    .reference-square {
      position: absolute;
      border: 2px solid rgba(255, 255, 255, 0.6);
      width: 650px;
      height: 850px;
      border-radius: 50%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 5;
    }

  </style>
</head>
<body>

  <div class="fullscreen-container">
    <video id="videoAuto" autoplay muted playsinline></video>
    <canvas id="canvasOverlay"></canvas>

    <div class="reference-square"></div>

    <div class="overlay" id="capturaText" style="margin-top: 0px;">POSICIONE SEU ROSTO NO CIRCULO</div>
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
      contador.innerText = 'Modelos carregados. Aguardando rosto...';
    }

    async function iniciarWebcam() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
      } catch (err) {
        contador.innerText = 'Erro ao acessar a webcam';
        contador.style.color = 'red';
      }
    }

    async function detectarRostoEDesenhar() {
      const displaySize = { width: video.videoWidth, height: video.videoHeight };
      faceapi.matchDimensions(canvas, displaySize);
      const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
      const result = await faceapi.detectSingleFace(video, options).withFaceLandmarks();

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
      const canvasTemp = document.createElement('canvas');
      const context = canvasTemp.getContext('2d');
      canvasTemp.width = video.videoWidth;
      canvasTemp.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvasTemp.width, canvasTemp.height);
      return canvasTemp.toDataURL('image/png');
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

    async function aguardarDeteccao() {
      const loop = setInterval(async () => {
        if (emCooldown) return;
        const detectado = await detectarRostoEDesenhar();

        if (detectado) {
          deteccoesSeguidas++;
          contador.innerText = `Rosto detectado (${deteccoesSeguidas}/3)...`;

          if (deteccoesSeguidas >= 3) {
            clearInterval(loop);
            contador.innerText = 'Capturando imagem em 3 segundos...';
            emCooldown = true;

            setTimeout(async () => {
              const img = await capturarImagem();
              enviarImagem(img);
              contador.innerText = 'Espere...';
              setTimeout(() => {
                emCooldown = false;
                deteccoesSeguidas = 0;
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
      aguardarDeteccao();
    };

    refazerBtn.onclick = () => {
      location.reload();
    };
  </script>

</body>
</html>
