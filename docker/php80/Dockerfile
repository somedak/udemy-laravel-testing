FROM php:8.0-apache

COPY php.ini /usr/local/etc/php/
COPY ./apache/*.conf /etc/apache2/sites-enabled/

RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
        curl \
        libmemcached-dev \
        libz-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        libssl-dev \
        libwebp-dev \
        libxpm-dev \
        libmcrypt-dev \
        libonig-dev \
        libnss3 \
        vim;

RUN set -eux; \
    docker-php-ext-install pdo_mysql; \
    docker-php-ext-configure gd \
        --prefix=/usr \
        --with-jpeg \
        --with-webp \
        --with-xpm \
        --with-freetype; \
    docker-php-ext-install gd; \
    php -r 'var_dump(gd_info());'

RUN set -xe; \
    apt-get update -yqq && \
    pecl channel-update pecl.php.net && \
    apt-get install -yqq \
        apt-utils \
        gnupg2 \
        git \
        libzip-dev zip unzip && \
        docker-php-ext-configure zip; \
        docker-php-ext-install zip && \
        php -m | grep -q 'zip'

RUN set -xe; \
    pecl install xdebug-3.1.2; \
    docker-php-ext-enable xdebug
COPY ./xdebug.ini /usr/local/etc/php/xdebug.ini

RUN set -xe; \
    sed -i "s/xdebug.remote_host=/xdebug.client_host=/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.remote_connect_back=0/xdebug.discover_client_host=false/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.remote_port=9000/xdebug.client_port=9003/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.profiler_enable=0/; xdebug.profiler_enable=0/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.profiler_output_dir=/xdebug.output_dir=/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.remote_mode=req/; xdebug.remote_mode=req/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.remote_autostart=0/xdebug.start_with_request=yes/" /usr/local/etc/php/xdebug.ini && \
    sed -i "s/xdebug.remote_enable=0/xdebug.mode=debug/" /usr/local/etc/php/xdebug.ini
    RUN sed -i "s/xdebug.cli_color=0/xdebug.cli_color=1/" /usr/local/etc/php/xdebug.ini

RUN set -eux docker-php-ext-install opcache;
COPY ./opcache.ini /usr/local/etc/php/opcache.ini

RUN set -eux apt-get install -yqq jpegoptim optipng pngquant gifsicle;

RUN set -eux; \
    apt-get install -yqq libmagickwand-dev imagemagick && \
    cd /tmp && \
    git clone https://github.com/Imagick/imagick; \
    cd imagick && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    rm -r /tmp/imagick; \
    docker-php-ext-enable imagick; \
    php -m | grep -q 'imagick'

RUN set -eux apt-get -y install default-mysql-client

RUN pecl install redis-5.3.7; \
    docker-php-ext-enable redis

RUN apt-get install -y nodejs npm && npm install n -g && n 14.19.3

RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    rm /var/log/lastlog /var/log/faillog

RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php ;\
php composer-setup.php --filename=composer;\
php -r "unlink('composer-setup.php');" ;\
mv composer /usr/local/bin/composer

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite
