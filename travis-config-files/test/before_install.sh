uname -a
phpunit --version
mysqladmin -uroot status
composer self-update
sudo chmod 777 -R symfony/cache
sudo chmod 777 -R symfony/log

sudo /etc/init.d/mysql stop

docker pull $DB_IMAGE:$TAG
docker run --name $DB_IMAGE -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -p 3306:3306 -d $DB_IMAGE:$TAG

# This sleep is need to start MySQL service
sleep 15

docker exec -it mysql sh -c "mysql -uroot -proot -e \"status;\""
exp="UPDATE mysql.user SET plugin = 'mysql_native_password' WHERE user.User = 'root';FLUSH PRIVILEGES;"
docker exec -it mysql sh -c "mysql -uroot -proot -e \"${exp}\""
docker exec -it mysql sh -c "/etc/init.d/mysql restart"
