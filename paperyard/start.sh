#!/bin/bash
service php7.0-fpm start
./usr/sbin/nginx

echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.php" >>  mycron
crontab mycron

/etc/init.d/cron start
