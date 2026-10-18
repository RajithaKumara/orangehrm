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

include_once('../src/config/log_settings.php');

use OrangeHRM\Config\Config;
use OrangeHRM\Framework\Framework;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Installer\Framework\HttpKernel as InstallerKernel;
use Symfony\Component\ErrorHandler\Debug;

require realpath(__DIR__ . '/../src/vendor/autoload.php');

$env = 'prod';
$debug = 'prod' !== $env;

if ($debug) {
    umask(0000);
    Debug::enable();
}

$request = Request::createFromGlobals();

/**
 * The single entry point for both applications, so that `web/` can be the
 * document root. Everything else -- `src/`, `installer/`, `vendor/` and
 * `lib/confs/Conf.php`, which holds the database credentials -- then sits
 * outside it and cannot be served, however the webserver is configured.
 *
 * The installer is reached by path rather than by install state, because the
 * upgrader runs against an instance that *is* installed. Install state only
 * decides where an unrecognised path goes.
 *
 * This routes; it does not authorise. Every installer controller still passes
 * through AbstractInstallerController::handle(), which refuses with 502 once
 * `lib/confs/Conf.php` is present. Reaching the kernel is not the same as
 * being allowed to install.
 */
$path = $request->getPathInfo();
$isInstallerRequest = strpos($path, '/installer') === 0
    || strpos($path, '/upgrader') === 0;

if ($isInstallerRequest) {
    // Checked here and not at the top of the file: system_requirements.php
    // caps the supported range, and applying that cap to the application would
    // take a working instance down the day its host moves past the cap. The
    // installer is where an unsupported version is worth refusing, and where
    // the message can be read. Below the floor the application never gets this
    // far -- composer's platform_check.php reports it from the autoloader.
    require realpath(__DIR__ . '/../installer/php_version_check.php');

    $kernel = new InstallerKernel($env, $debug);
    $response = $kernel->handleRequest($request);
} elseif (Config::isInstalled()) {
    $kernel = new Framework($env, $debug);
    $response = $kernel->handleRequest($request);
} else {
    $kernel = new Framework($env, $debug);
    $response = new RedirectResponse(
        $request->getSchemeAndHttpHost() . $request->getBaseUrl() . '/installer/welcome'
    );
}

$response->send();
$kernel->terminate($request, $response);
