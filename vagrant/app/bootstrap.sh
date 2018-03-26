#!/usr/bin/env bash

set -e

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

sudo apt-get update
sudo apt-get install -y apache2 php phpunit postgresql php-pgsql zip libapache2-mod-php php-mbstring


sudo su --login -c "psql -c \"CREATE USER test WITH PASSWORD 'testpassword';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE test WITH OWNER test ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres

sudo su --login -c "psql -c \"CREATE USER app WITH PASSWORD 'password';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE app WITH OWNER app ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres


mkdir -p /bin
wget -O /bin/composer.phar -q https://getcomposer.org/composer.phar
wget -O /bin/phpunit.phar -q https://phar.phpunit.de/phpunit-6.3.phar

cd /vagrant
php /bin/composer.phar  install

cp /vagrant/vagrant/app/parameters_test.yml /vagrant/app/config/parameters_test.yml
cp /vagrant/vagrant/app/parameters.yml /vagrant/app/config/parameters.yml
cp /vagrant/vagrant/app/apache.conf /etc/apache2/sites-enabled/000-default.conf
cp /vagrant/vagrant/app/app_dev.php /vagrant/web/app_dev.php

if [ ! -d "/vagrant/app/cache/dev/" ]; then
	mkdir /vagrant/app/cache/dev/
fi
if [ ! -d "/vagrant/app/cache/prod/" ]; then
	mkdir /vagrant/app/cache/prod/
fi

touch /vagrant/app/logs/prod.log
touch /vagrant/app/logs/dev.log
chown -R www-data:www-data /vagrant/app/logs/prod.log
chown -R www-data:www-data /vagrant/app/logs/dev.log

a2enmod rewrite
/etc/init.d/apache2 restart



if [ -f /vagrant/import.dump ]
then
    pg_restore -f /vagrant/import.sql /vagrant/import.dump
fi

if [ -f /vagrant/import.sql ]
then
    export PGPASSWORD=password
    psql -U app -hlocalhost  app -f /vagrant/import.sql
fi

php app/console doctrine:migrations:migrate --no-interaction

php app/console assetic:dump --env=dev

chown -R www-data:www-data /vagrant/app/cache/prod/
chown -R www-data:www-data /vagrant/app/cache/dev/

echo "alias db='psql -U app app -hlocalhost'" >> /home/ubuntu/.bashrc
echo "localhost:5432:app:app:password" > /home/ubuntu/.pgpass
chown ubuntu:ubuntu /home/ubuntu/.pgpass
chmod 0600 /home/ubuntu/.pgpass

echo "cd /vagrant" >> /home/ubuntu/.bashrc
echo "alias test='php /bin/phpunit.phar -c /vagrant/app/'" >>  /home/ubuntu/.bashrc
