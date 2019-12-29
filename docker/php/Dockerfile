FROM php:7.3-fpm-stretch

WORKDIR /var/www

RUN apt-get update && apt-get install -y vim git zip unzip libmcrypt-dev mysql-client openssh-client zlib1g-dev ssh \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev cron libmagickwand-dev imagemagick supervisor libzip-dev

RUN export EDITOR='/usr/bin/vim'

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

RUN docker-php-ext-install pdo_mysql zip gd exif

RUN pecl install imagick

RUN pecl install xdebug

ADD ./docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
ADD ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

ADD ./docker/php/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

ADD ./docker/php/crontab /etc/cron.d/lumen-cron
RUN crontab /etc/cron.d/lumen-cron

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
