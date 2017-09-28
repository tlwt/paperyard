#Download base image ubuntu 16.10
FROM ubuntu:16.10

### beginning to apt install

# Update Ubuntu Software repository
RUN apt-get update

# installing nginx
RUN apt-get -y install nginx

# installing PHP
#RUN apt-get -y install php
RUN apt-get -y install php-sqlite3
RUN apt-get -y install nginx php7.0-cli php7.0-cgi php7.0-fpm

#installing tools
RUN apt-get -y install nano
RUN apt-get -y install less
RUN apt-get -y install git
RUN apt-get -y install cron
RUN apt-get -y install wget

#ocr my pdf
RUN apt-get -y install ocrmypdf
RUN apt-get -y install tesseract-ocr-deu
RUN apt-get -y install python-pip

# installing pdftotext
RUN apt-get -y install poppler-utils

# for doxygen
RUN apt-get -y install cmake
RUN apt-get -y install flex
RUN apt-get -y install bison

RUN git clone https://github.com/doxygen/doxygen.git

WORKDIR doxygen/build
RUN cmake -G "Unix Makefiles" ..
RUN make
RUN make install

WORKDIR /
# adding configuration for webserver
ADD config/nginx /etc/nginx/sites-enabled/default
RUN ln -s /var/www/html /www

# adding configuration for doxygen
ADD config/doxygenconfig /

# exposing webserver port
EXPOSE 80

# adding read the docs environment
#RUN pip install sphinx

# adding start script
ADD /paperyard/start.sh /
RUN chmod 755 /start.sh

ENTRYPOINT ./start.sh && /bin/bash
