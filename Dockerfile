# download base image ubuntu 16.10
FROM ubuntu:16.10

### beginning to apt install

# enable gettext support
RUN locale-gen en_US.UTF-8
RUN locale-gen de_DE.UTF-8
ENV LC_ALL en_US.UTF8

# update Ubuntu Software repository
RUN apt-get update

# installing nginx
RUN apt-get -y install nginx

# installing PHP
#RUN apt-get -y install php
RUN apt-get -y install php-sqlite3
RUN apt-get -y install nginx php7.0-cli php7.0-cgi php7.0-fpm php7.0-mbstring php7.0-xml php7.0-zip php7.0-imagick

# installing tools
RUN apt-get -y install nano
RUN apt-get -y install less
RUN apt-get -y install git
RUN apt-get -y install cron
RUN apt-get -y install wget
RUN apt-get -y install curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ocr my pdf
RUN apt-get -y install ocrmypdf
RUN apt-get -y install tesseract-ocr-deu
RUN apt-get -y install python-pip

# installing pdftotext
RUN apt-get -y install poppler-utils

# install xdebug
RUN apt-get -y install php7.0-dev
RUN apt-get -y install php-pear
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/lib/php/ -name xdebug.so)" > /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.remote_host=172.254.254.254" >> /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /etc/php/7.0/fpm/conf.d/xdebug.ini \
    && echo "xdebug.remote_log=/tmp/xdebug.log" >> /etc/php/7.0/fpm/conf.d/xdebug.ini

ENV PHP_IDE_CONFIG "serverName=docker"

WORKDIR /

# adding config folder
ADD config /config

# moving configuration for webserver
RUN cp config/nginx /etc/nginx/sites-enabled/default
RUN ln -s /var/www/html /www

# exposing webserver port
EXPOSE 80

# files for checking if the needed directories have been mounded correctly
RUN mkdir -p /data/scan \
    && mkdir -p /data/scan/error \
    && mkdir -p /data/scan/archive \
    && mkdir -p /data/inbox \
    && mkdir -p /data/outbox \
    && mkdir -p /data/sort \
    && mkdir -p /data/database \
    && touch /data/scan/paperyardDirectoryNotMounted.txt \
    && touch /data/inbox/paperyardDirectoryNotMounted.txt \
    && touch /data/outbox/paperyardDirectoryNotMounted.txt \
    && touch /data/sort/paperyardDirectoryNotMounted.txt \
    && touch /data/database/paperyardDirectoryNotMounted.txt

# adding start script
ADD /paperyard/start.sh /
RUN chmod 755 /start.sh

ENTRYPOINT ./start.sh && /bin/bash
