FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip

# Set working directory
WORKDIR /var/www/html

# Configure PHP
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini 

# Configure PHP-FPM to listen on the correct address
RUN echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/www.conf

# Copy nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Copy application code
COPY ./src /var/www/html/

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads \
    && chmod -R 777 /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Starting Nginx..."\n\
service nginx start\n\
echo "Starting PHP-FPM..."\n\
exec php-fpm' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Expose port
EXPOSE 80

# Start services
CMD ["/usr/local/bin/start.sh"] 