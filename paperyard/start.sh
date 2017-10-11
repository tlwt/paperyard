#!/bin/bash
# for production we need to pull the current repository
echo "setting up paperyard"
if [ ! -d "/var/www/html/backend" ]; then
 mkdir -p /tmp
 mkdir -p /var/www/html
 cd /tmp
 git clone https://github.com/tlwt/paperyard.git
 mv paperyard/paperyard/* /var/www/html/
fi

# starting PHP & nginx
service php7.0-fpm start
/usr/sbin/nginx

# setting up cron jobs for backend
echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.namer.php" >>  mycron
echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.scanner.php" >>  mycron
echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.sorter.php" >>  mycron
crontab mycron
/etc/init.d/cron start

echo " * Checking for dependencies updates"
cd /var/www/html/frontend
composer update --no-interaction &>/dev/null


# creating folder structure in case it does not exist
mkdir -p /data/scan
mkdir -p /data/scan/error
mkdir -p /data/scan/archive
mkdir -p /data/inbox
mkdir -p /data/outbox
mkdir -p /data/sort
