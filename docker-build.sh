#!/bin/bash

if [ "$1" == "--nc" ]; then
    echo "building without cache"
    docker build --no-cache -t ppyrd_image .
else
    docker build -t ppyrd_image .
fi
