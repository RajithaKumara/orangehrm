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

include_once(__DIR__ . '/../src/config/log_settings.php');

use OrangeHRM\Config\Config;
use OrangeHRM\Framework\Framework;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use Symfony\Component\ErrorHandler\Debug;

require realpath(__DIR__ . '/../src/vendor/autoload.php');

$env = 'prod';
$debug = 'prod' !== $env;

if ($debug) {
    umask(0000);
    Debug::enable();
}

$kernel = new Framework($env, $debug);
$request = Request::createFromGlobals();

$baseDir = "/var/www/orangehrm_57/ondemand/instanceA";
Config::has(Config::CONF_FILE_PATH);
Config::set(Config::CONF_FILE_PATH, $baseDir . DIRECTORY_SEPARATOR . 'confs' . DIRECTORY_SEPARATOR . 'Conf.php');
Config::set(Config::LOG_DIR, $baseDir . DIRECTORY_SEPARATOR . 'log');
Config::set(Config::CACHE_DIR, $baseDir . DIRECTORY_SEPARATOR . 'cache');
Config::set(Config::CONFIG_DIR, $baseDir . DIRECTORY_SEPARATOR . 'confs');
Config::set(Config::CRYPTO_KEY_DIR, $baseDir . DIRECTORY_SEPARATOR . 'confs' . DIRECTORY_SEPARATOR . 'cryptokeys');

if (Config::isInstalled()) {
    $response = $kernel->handleRequest($request);
} else {
    // TODO:: your OrangeHRM system seems not installed, please refer these article to install
    $response = new RedirectResponse(str_replace('/web/index.php', '', $request->getBaseUrl()));
}

$response->send();
$kernel->terminate($request, $response);
