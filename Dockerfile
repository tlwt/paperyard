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

WORKDIR /

# adding config folder
ADD config /config

# moving configuration for webserver
RUN cp config/nginx /etc/nginx/sites-enabled/default
RUN ln -s /var/www/html /www

# exposing webserver port
EXPOSE 80

# files for checking if the needed directories have been mounded correctly
RUN mkdir -p /data/scan
RUN mkdir -p /data/scan/error
RUN mkdir -p /data/scan/archive
RUN mkdir -p /data/inbox
RUN mkdir -p /data/outbox
RUN mkdir -p /data/sort
RUN mkdir -p /data/database

RUN touch /data/scan/paperyardDirectoryNotMounted.txt
RUN touch /data/inbox/paperyardDirectoryNotMounted.txt
RUN touch /data/outbox/paperyardDirectoryNotMounted.txt
RUN touch /data/sort/paperyardDirectoryNotMounted.txt
RUN touch /data/database/paperyardDirectoryNotMounted.txt

# adding start script
ADD /paperyard/start.sh /
RUN chmod 755 /start.sh

ENTRYPOINT ./start.sh && /bin/bash
