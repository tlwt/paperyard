#!/bin/bash

# for production we need to pull the current repository
if [ ! -d '/var/www/html/backend' ]; then
    mkdir -p /paperyardSrc
    mkdir -p /var/www/html
    cd /paperyardSrc
    git clone https://github.com/tlwt/paperyard.git
    rm -rf /var/www/html
    ln -s /paperyardSrc/paperyard/paperyard /var/www/html
fi

# starting PHP & nginx
echo '* Starting Interface'
service php7.1-fpm start
/usr/sbin/nginx

# setting up cron jobs for backend
echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.namer.php" >>  paperyard_cron
echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.scanner.php" >>  paperyard_cron
echo "* * * * * /usr/bin/php /var/www/html/backend/ppyrd.sorter.php" >>  paperyard_cron
echo "*/15 * * * * su -s /bin/sh -c 'cd /paperyardSrc/paperyard && /usr/bin/git pull -q origin master' "  >>  paperyard_cron

crontab paperyard_cron
/etc/init.d/cron start

# file watcher
#echo '* Starting file watcher'
#echo '/data/scan IN_CLOSE_WRITE tsp php /var/www/html/backend/ppyrd.scanner.php' >> /etc/incron.d/watch_scan
#echo '/data/inbox IN_CLOSE_WRITE tsp php /var/www/html/backend/ppyrd.namer.php' >> /etc/incron.d/watch_inbox
#echo '/data/outbox IN_CLOSE_WRITE tsp php /var/www/html/backend/ppyrd.sorter.php' >> /etc/incron.d/watch_outbox
#echo 'root' >> /etc/incron.allow
#/etc/init.d/incron start

# composer
echo '* Checking for dependencies updates'
cd /var/www/html/frontend
composer install --no-interaction &>/dev/null

echo '* Checking for thumbnail cache folder'
if [ ! -d '/var/www/html/frontend/public/static/img/cache' ]; then
    echo ' * Created thumbnail cache folder'
    mkdir /var/www/html/frontend/public/static/img/cache
    chmod 777 /var/www/html/frontend/public/static/img/cache
fi

echo "${COMMIT_COUNT}" >> /data/version

# creating folder structure in case it does not exist
mkdir -p /data/database
mkdir -p /data/scan
mkdir -p /data/scan/error
mkdir -p /data/scan/archive
mkdir -p /data/inbox
mkdir -p /data/outbox
mkdir -p /data/sort
