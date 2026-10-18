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

/**
 * Refuses a PHP version the installer cannot support, before anything that
 * depends on one.
 *
 * Deliberately plain and dependency-free: it has to produce a readable message
 * on a version where the rest of the codebase may not even parse. Included by
 * the front controller only on the installer branch -- applying this cap to the
 * application would take a working instance down the day its host moves past
 * the supported maximum.
 */

$systemRequirements = require realpath(__DIR__ . '/config/system_requirements.php');

$ohrmTrimVersion = function ($currentVersion, $points) {
    $pattern = '/^(\d+)' . str_repeat('\.(\d+)', $points) . '/';
    preg_match($pattern, $currentVersion, $matches);
    return $matches[0];
};

$max = $systemRequirements['phpversion']['max'];
$min = $systemRequirements['phpversion']['min'];
$currentVersion = phpversion();

$message = "PHP version should be higher than `$min` and lower than `$max`, "
    . "detected version is `$currentVersion`.";

if (!(version_compare($ohrmTrimVersion($currentVersion, substr_count($min, '.')), $min, '>=') &&
    version_compare($ohrmTrimVersion($currentVersion, substr_count($max, '.')), $max, '<='))) {
    die($message);
}

if (in_array($currentVersion, $systemRequirements['phpversion']['excludeRange'])) {
    die($message);
}
