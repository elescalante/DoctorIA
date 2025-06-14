# Utiliza una imagen base oficial de PHP con Apache.
FROM php:8.2-apache

# Actualiza la lista de paquetes e instala las librerías de desarrollo de PostgreSQL.
# 'libpq-dev' proporciona los archivos necesarios como libpq-fe.h
RUN apt-get update && apt-get install -y libpq-dev \
    # Elimina los archivos de caché de apt para reducir el tamaño de la imagen
    && rm -rf /var/lib/apt/lists/*

# Establece el directorio de trabajo dentro del contenedor.
WORKDIR /var/www/html

# Copia los archivos de tu aplicación PHP.
# Si tus archivos PHP están en la raíz de tu repositorio:
COPY . .
# SI tus archivos PHP están en una SUB-CARPETA (ej. 'public/'), usa:
# COPY public/ .

# Instala y habilita la extensión pdo_pgsql.
# Ahora que libpq-dev está instalado, esto debería funcionar.
RUN docker-php-ext-install pdo_pgsql && \
    docker-php-ext-enable pdo_pgsql

# Expone el puerto 80, que es el puerto predeterminado que Apache escuchará.
EXPOSE 80
