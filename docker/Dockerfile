FROM php:7.4-apache

RUN apt-get update && apt-get install -y libmcrypt-dev zip git npm mycli && \
pecl install mcrypt && \
docker-php-ext-enable mcrypt \
&& docker-php-ext-install pdo_mysql \
&& a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY start.sh /usr/bin/start.sh
RUN chmod +x /usr/bin/start.sh
CMD ["/usr/bin/start.sh"]