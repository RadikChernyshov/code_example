FROM php:7.1.0-fpm

MAINTAINER Radik Chernyshov <radik.chernyshov@gmail.com>

ENV PHANTOM_JS "phantomjs-2.1.1-linux-x86_64"

RUN apt-get update && \
    apt-get install --no-install-recommends -y \
        libicu-dev \
        imagemagick \
        openssh-client \
        sudo \
        git \
        libmemcached-dev \
        libssl-dev \
        libpng12-dev \
        libjpeg-dev \
        re2c \
        libfreetype6-dev \
        zlib1g-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxml2-dev \
        build-essential \
        chrpath \
        libssl-dev \
        libxft-dev \
        libfreetype6 \
        libfreetype6-dev \
        libfontconfig1 \
        libfontconfig1-dev \
        wget \
        supervisor

RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis && \
    cd /usr/src/php/ext/redis && \
    git checkout php7

RUN buildDeps="libpq-dev libzip-dev " && apt-get update && \
    apt-get install -y $buildDeps --no-install-recommends && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install -j$(nproc) iconv mcrypt gd redis zip pdo_mysql mbstring intl soap exif

RUN pecl install mongodb && \
    echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

RUN mkdir -p -m 0777 /app/web/uploads

RUN wget -O - http://nginx.org/keys/nginx_signing.key | sudo apt-key add -
RUN sudo cp /etc/apt/sources.list /etc/apt/sources.list.bak
RUN echo "deb http://nginx.org/packages/mainline/ubuntu/ trusty nginx" | sudo tee -a /etc/apt/sources.list
RUN echo "deb-src http://nginx.org/packages/mainline/ubuntu/ trusty nginx" | sudo tee -a /etc/apt/sources.list
RUN apt-get update && apt-get install -y nginx

COPY ./supervisor/supervisor.conf /etc/supervisor/conf.d
COPY ./nginx/nginx.conf /etc/nginx
COPY ./nginx/vhosts/virtual_host.conf /etc/nginx/conf.d
COPY ./nginx/vhosts/staff_virtual_host.conf /etc/nginx/conf.d
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]

RUN cd /tmp || git clone https://web_app.git clone
RUN cp -r /tmp/clone/sources/* /app

RUN rm -rf /tmp/*

WORKDIR /app

RUN wget https://getcomposer.org/download/1.4.1/composer.phar
RUN php composer.phar install --ignore-platform-reqs --no-dev

EXPOSE 80