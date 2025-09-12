# Use the official PHP runtime with Apache
FROM php:8.2-apache

# Install PHP extensions needed for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy Apache configuration from docker folder
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY api/ /var/www/html/api/
COPY config/ /var/www/html/config/
COPY index.php /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]