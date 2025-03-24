# Base mais leve para evitar pacotes desnecessários
FROM debian:bullseye-slim

# Evita prompts interativos
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Sao_Paulo

# Atualizar repositórios e instalar apenas os pacotes essenciais
RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    libapache2-mod-php \
    curl \
    unzip \
    build-essential \
    cmake \
    python3-dev \
    python3-pip \
    python3-venv \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Criar e ativar ambiente virtual Python
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Atualizar pip e instalar pacotes para reconhecimento facial
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir dlib face_recognition

# Copiar aplicação PHP para o Apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Expor porta 80 para acesso à aplicação
EXPOSE 80

# Rodar o Apache em foreground
CMD ["apachectl", "-D", "FOREGROUND"]
