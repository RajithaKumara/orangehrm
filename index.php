<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

use OrangeHRM\Config\Config;

require realpath(__DIR__ . '/src/vendor/autoload.php');

/* For logging PHP errors */
include_once('./src/config/log_settings.php');

$baseDir = "/var/www/orangehrm_57/ondemand/instanceB";
Config::has(Config::CONF_FILE_PATH);
Config::set(Config::CONF_FILE_PATH, $baseDir . DIRECTORY_SEPARATOR . 'confs' . DIRECTORY_SEPARATOR . 'Conf.php');
Config::set(Config::LOG_DIR, $baseDir . DIRECTORY_SEPARATOR . 'log');
Config::set(Config::CACHE_DIR, $baseDir . DIRECTORY_SEPARATOR . 'cache');
Config::set(Config::CONFIG_DIR, $baseDir . DIRECTORY_SEPARATOR . 'confs');
Config::set(Config::CRYPTO_KEY_DIR, $baseDir . DIRECTORY_SEPARATOR . 'confs' . DIRECTORY_SEPARATOR . 'cryptokeys');


if (!Config::isInstalled()) {
    header('Location: ./installer/index.php');
} else {
    header("Location: ./web/index.php/auth/login");
}
