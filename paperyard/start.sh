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

# @jannik - what the heck are you doing here?
if [ ! -f "/tmp/composer.phar" ]; then
    EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
    then
        >&2 echo 'ERROR: Invalid installer signature'
        rm composer-setup.php
        exit 1
    fi

    php composer-setup.php --quiet
    rm composer-setup.php
fi

if [ ! -d "/var/www/html/frontend/vendor" ]; then
    cd /var/www/html/frontend
    php /tmp/composer.phar update
fi
