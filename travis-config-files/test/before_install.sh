uname -a
phpunit --version
composer self-update
sudo chmod 777 -R symfony/cache
sudo chmod 777 -R symfony/log

# Create MySQL Docker container
docker pull $DB_IMAGE:$TAG
docker run --name $DB_IMAGE -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -d $DB_IMAGE:$TAG --default-authentication-plugin=mysql_native_password

# Edit config.ini
containerIp=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $DB_IMAGE 2>&1)
export MYSQL_HOST="${containerIp}"
expression="s/HostName.*/HostName = $MYSQL_HOST/g"
sed "${expression}" installer/config.ini > config.ini
mv config.ini installer

# This sleep is need to start MySQL service in the Docker container
php ./travis-config-files/test/travis.php hasMysqlServerUp
