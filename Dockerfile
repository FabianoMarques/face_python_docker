# Usando uma imagem base para PHP
FROM php:8.0-cli

# Atualiza os pacotes e instala dependências do sistema
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-dev \
    build-essential \
    libopenblas-dev \
    liblapack-dev \
    libx11-dev \
    cmake \
    git \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Instala a biblioteca dlib (necessária para o face_recognition) e o face_recognition
RUN pip3 install dlib face_recognition

# Instalar a extensão XML do PHP
RUN docker-php-ext-install xml

# Cria um diretório de trabalho para o PHP
WORKDIR /var/www/html

# Copia o código PHP e os scripts Python para o container
COPY . /var/www/html

# Expondo a porta 8000 para o servidor PHP embutido
EXPOSE 8000

# Inicia apenas o servidor PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]
