<?php

die('Upgrader not yet implemented for OrangeHRM 5.0');

define('ROOT_PATH', realpath(__DIR__ . '/../..'));

include_once(ROOT_PATH . '/lib/confs/log_settings.php');
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

if (!is_writable(dirname(__FILE__).'/../cache') || !is_writable(dirname(__FILE__).'/../log')) {
    die("'upgrader/cache' and 'upgrader/log' directories should be writable.");
}

$configuration = ProjectConfiguration::getApplicationConfiguration('upgrader', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
