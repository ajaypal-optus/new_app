FROM php:8.2-apache

# Copy files to Apache root
COPY . /var/www/html/

# Enable mod_rewrite (optional)
RUN a2enmod rewrite

EXPOSE 80