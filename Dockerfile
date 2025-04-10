# Base mais leve para evitar pacotes desnecessários
FROM debian:bullseye-slim

# Evita prompts interativos
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Sao_Paulo

# Atualizar repositórios e instalar apenas os pacotes essenciais
RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    libapache2-mod-php \
    php-mysqli \
    curl \
    unzip \
    build-essential \
    cmake \
    python3-dev \
    python3-pip \
    python3-venv \
    libboost-all-dev \
    libprotobuf-dev \
    protobuf-compiler \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Criar e ativar ambiente virtual Python
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Atualizar pip e instalar pacotes para reconhecimento facial
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir dlib face_recognition

# Copiar aplicação PHP para o Apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Configurar index.php como página inicial
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/mods-enabled/dir.conf

# Permitir uso de .htaccess para reescrita
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Remover página padrão do Apache
RUN rm -f /var/www/html/index.html

# Expor porta 80 para acesso à aplicação
EXPOSE 80

# Rodar o Apache em foreground
CMD ["apachectl", "-D", "FOREGROUND"]
