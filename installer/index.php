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
 * Kept for instances whose document root is still the project directory.
 *
 * The installer is now served by web/index.php, so that web/ can be the
 * document root and this file -- along with src/, vendor/ and
 * lib/confs/Conf.php -- sits outside it. Anything following an older link
 * lands here and is sent on; the assets it needs resolve under web/ either
 * way.
 *
 * Plain PHP on purpose. Nothing here should depend on an autoloader that a
 * moved document root may already have put out of reach.
 */

$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$base = str_replace('/installer/index.php', '', $script);

header('Location: ' . $base . '/web/index.php/installer/welcome', true, 302);
