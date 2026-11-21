FROM php:8.2-apache
RUN apt-get update && apt-get install -y libxml2-dev libcurl4-openssl-dev libonig-dev && docker-php-ext-install mbstring && rm -rf /var/lib/apt/lists/*
