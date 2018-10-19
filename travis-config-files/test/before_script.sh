echo "Run php in-built server - background process"
nohup bash -c "php -S 127.0.0.1:8888 2>&1 -t symfony/web  >/dev/null 2>&1 &"
sleep 4
mkdir -p build/logs

isPhp5x=$(php ./travis-config-files/test/travis.php isPhp5x 2>&1)
if [ "${isPhp5x}" = "true" ]
then
    rm codecept.phar
    wget https://codeception.com/php5/codecept.phar
fi
