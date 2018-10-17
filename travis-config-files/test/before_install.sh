set -ev

uname -a
phpunit --version
mysqladmin -uroot status
composer self-update
sudo chmod 777 -R symfony/cache
sudo chmod 777 -R symfony/log

sudo /etc/init.d/mysql stop

docker pull $DB_IMAGE:$TAG
docker run --name mysql80 -e MYSQL_ROOT_PASSWORD=root -p 3306:3306 -d $DB_IMAGE:$TAG
#echo "USE mysql;\nUPDATE user SET password=PASSWORD('root') WHERE user='root';\nFLUSH PRIVILEGES;\n" | mysql -u root
