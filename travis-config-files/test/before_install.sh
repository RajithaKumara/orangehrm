set -ev

uname -a
phpunit --version
mysqladmin -uroot status
composer self-update
sudo chmod 777 -R symfony/cache
sudo chmod 777 -R symfony/log

sudo /etc/init.d/mysql stop

docker pull $DB_IMAGE:$TAG
docker run --name $DB_IMAGE -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -p 3306:3306 -d $DB_IMAGE:$TAG

docker ps
docker exec -it dev_mariadb_102 bash | cat /etc/mysql/my.cnf

mysqladmin -uroot --host=0.0.0.0 --port=3306 status
