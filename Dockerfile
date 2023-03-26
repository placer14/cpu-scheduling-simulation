FROM php:5.5.30-apache
COPY . /var/www/html
WORKDIR /var/www/html
EXPOSE 80:8080
