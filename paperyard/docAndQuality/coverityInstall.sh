#!/bin/bash
cd /
rm -rf /cov-analysis-linux*
wget https://scan.coverity.com/download/linux64 --post-data "token=-oTLrr-4-FvxiEFj2SFjTw&project=tlwt%2Fpaperyard" -O coverity_tool.tgz
gzip -d coverity_tool.tgz
tar -xvf coverity_tool.tar
rm coverity_tool.tar
mv cov-analysis-linux64* cov-analysis-linux
