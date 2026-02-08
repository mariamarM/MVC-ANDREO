FROM php:8.2-apache

# 1. Instalar PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 2. Habilitar rewrite
RUN a2enmod rewrite

# 3. CAMBIA EL DOCUMENTROOT A PUBLIC
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# 4. PERMITIR ACCESO (SOLUCIÓN 403)
RUN echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf
RUN echo '    AllowOverride All' >> /etc/apache2/apache2.conf
RUN echo '    Require all granted' >> /etc/apache2/apache2.conf
RUN echo '</Directory>' >> /etc/apache2/apache2.conf

# 5. Copiar archivos
COPY . /var/www/html/

# 6. Crear public si no existe
RUN mkdir -p /var/www/html/public

# 7. Cambiar permisos
RUN chmod -R 755 /var/www/html
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
EXPOSE 80

