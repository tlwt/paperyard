#Download base image ubuntu 16.04
FROM ubuntu:16.04

# Update Ubuntu Software repository
RUN apt-get update

# installing PHP
RUN apt-get -y install php
RUN apt-get -y install php-sqlite3

# installing pdftotext
RUN apt-get -y install poppler-utils

# gogogo
#ENTRYPOINT ./app/start.sh && /bin/bash
ENTRYPOINT ./app/start.sh