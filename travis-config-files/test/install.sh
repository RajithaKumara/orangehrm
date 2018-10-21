composer install -d symfony/lib
composer up -d symfony/lib
composer dump-autoload -o -d symfony/lib
php installer/cli_install.php 0
php devTools/general/create-test-db.php $MYSQL_ROOT_PASSWORD

