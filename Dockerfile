#Download base image ubuntu 16.04
FROM ubuntu:16.04

### beginning to apt install

# Update Ubuntu Software repository
RUN apt-get update

# installing nginx
RUN apt-get -y install nginx

# installing PHP
#RUN apt-get -y install php
RUN apt-get -y install php-sqlite3
RUN apt-get -y install nginx php7.0-cli php7.0-cgi php7.0-fpm php7.0-mbstring php7.0-xml php7.0-zip

#installing tools
RUN apt-get -y install nano
RUN apt-get -y install less
RUN apt-get -y install git
RUN apt-get -y install cron
RUN apt-get -y install wget

### apt install end

# installing pdftotext
RUN apt-get -y install poppler-utils

# adding configuration for webserver
ADD config/nginx /etc/nginx/sites-enabled/default


#WORKDIR /tmp
#RUN git clone https://github.com/ACTtaiwan/phpLiteAdmin.git

EXPOSE 80
# WORKDIR /
# gogogo

# adding start script
ADD /paperyard/start.sh /
RUN chmod 755 /start.sh

ENTRYPOINT ./start.sh && /bin/bash
