set -ev

uname -a
phpunit --version
mysqladmin -uroot status
composer self-update
sudo chmod 777 -R symfony/cache
sudo chmod 777 -R symfony/log

#sudo /etc/init.d/mysql stop

docker pull $DB_IMAGE:$TAG
docker run --name $DB_IMAGE -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -p 3306:3306 -d $DB_IMAGE:$TAG

docker ps
mysql -uroot --host=127.0.0.1 --port=3306 -p root
#echo "USE mysql;\nUPDATE user SET password=PASSWORD('root') WHERE user='root';\nFLUSH PRIVILEGES;\n" | mysql -u root
