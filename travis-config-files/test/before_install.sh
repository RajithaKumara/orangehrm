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

docker exec -it $DB_IMAGE sh -c "mysql -uroot -proot -e \"select user,host,plugin from mysql.user;\""
query="UPDATE mysql.user SET plugin='mysql_native_password' WHERE User='root';FLUSH PRIVILEGES;"
docker exec -it $DB_IMAGE sh -c "mysql -uroot -proot -e \"${query}\""
docker exec -it $DB_IMAGE sh -c "mysql -uroot -proot -e \"select user,host,plugin from mysql.user;\""
#docker exec -it $DB_IMAGE sh -c "/etc/init.d/mysql restart"
docker container restart $DB_IMAGE

sleep 15
