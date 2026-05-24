FROM php:8.2-apache

# Instalamos las herramientas para Laravel y la conexion a PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitamos la lectura de URL limpias y apuntamos a la carpeta public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Copiamos todo tu codigo de GitHub a la nube
COPY . /var/www/html/

# Instalamos Composer para que descargue tus librerias
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --optimize-autoloader --no-dev

# Le damos permiso a Laravel de escribir en la carpeta de almacenamiento
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
