<?php

/**
 * Echo "true" if PHP version is 5.x
 */
function isPhp5x()
{
    if (version_compare(PHP_VERSION, '7.0', '<')) {
        echo "true";
    } else {
        echo "false";
    }
}

/**
 * Check whether MySQL server started
 */
function hasMysqlServerUp()
{
    $startTime = time();

    $host = getEnvironmentVar('MYSQL_HOST');
    $port = getEnvironmentVar('MYSQL_PORT');
    $username = getEnvironmentVar('MYSQL_ROOT_USER');
    $password = getEnvironmentVar('MYSQL_ROOT_PASSWORD');
    $dsn = "mysql:host=%s;port=%s;charset=utf8mb4;";
    $dsn = sprintf($dsn, $host, $port);

    try {
        $dbConn = new PDO($dsn, $username, $password);
        $elapsedTime = time() - $startTime;
        echo "MySQL server ready. Elapsed time to server up : $elapsedTime\n";
        exit(0);
    } catch (PDOException $e) {
        sleep(1);
        hasMysqlServerUp();
    }
}

/**
 * Return environment variable
 * @param $envName
 * @return mixed
 */
function getEnvironmentVar($envName)
{
    return getenv($envName);
}

$function = $argv[1];
if (function_exists($function)) {
    $function();
}
