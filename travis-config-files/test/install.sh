composer install -d symfony/lib
composer dump-autoload -o -d symfony/lib
php installer/cli_install.php 0

cat lib/confs/Conf.php

php devTools/general/create-test-db.php $MYSQL_ROOT_PASSWORD

