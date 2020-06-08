#!/usr/bin/env bash

# Create `clientell-internal` network (useful `docker system prune` is executed on jenkins)
if ! docker network ls | grep -q " clientell-internal "; then docker network create clientell-internal; fi;

# ENV configuration and building
cp /home/jenkins/clientell.env .env

# Build and bring up containers
cd ./docker
docker-compose up --build -d
cd ../

# Build Databases
docker exec -i sourcetoad_mariadb102 mysql -uroot -proot --execute="DROP DATABASE IF EXISTS clientell_test"
docker exec -i sourcetoad_mariadb102 mysql -uroot -proot --execute="CREATE DATABASE IF NOT EXISTS clientell_test"

# Empty previous test stuff
yes | yum install kernel-devel gcc gcc-c++
yes | yum install php-devel
yum install php-pear # This line installs pecl as well as pear
yum install ImageMagick-devel
pecl install imagick
Add extension-imagick.so to /etc/php/7.2/cli/php.ini

# composer process
docker exec -i sourcetoad_clientell_php wget https://getcomposer.org/download/1.6.4/composer.phar -O composer.phar
docker exec -i sourcetoad_clientell_php php composer.phar config -g github-oauth.github.com 70a6eb096d83b91ea4710e1161d4470f78d635a3
docker exec -i sourcetoad_clientell_php php composer.phar --version
docker exec -i sourcetoad_clientell_php php composer.phar install -v
docker exec -i sourcetoad_clientell_php php -v

# composer process
docker exec -i sourcetoad_clientell_php yarn install

# test process
docker exec -i sourcetoad_clientell_php php artisan migrate --no-interaction
docker exec -i sourcetoad_clientell_php php artisan passport:install

# Setup the `API_CLIENT_SECRET` value
echo "API_CLIENT_SECRET=$(docker exec -i sourcetoad_mariadb102 mysql clientell_test -u root -proot -sse "SELECT secret FROM oauth_clients WHERE id = 2")" >> .env

# Run the tests
docker exec -i sourcetoad_clientell_php ./vendor/bin/phpunit

# Run the frontend tests
docker exec -i sourcetoad_clientell_php yarn run test

# version deploy
git remote update
echo `git describe --abbrev=0 --tags` > version