<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

use Doctrine\DBAL\Exception\ConnectionException;
use OrangeHRM\ORM\Doctrine;

define('ENVIRNOMENT', 'test');

require realpath(__DIR__ . '/../../vendor/autoload.php');

$errorMessage = "
Can't connect to database `%s`.
Run below command and try again;
$ php ./devTools/general/create-test-db.php

Error:
%s\n
";

try {
    Doctrine::getEntityManager()->getConnection()->connect();
} catch (ConnectionException $e) {
    if ($e->getErrorCode() === 1049) {
        echo sprintf(
            $errorMessage,
            Doctrine::getEntityManager()->getConnection()->getDatabase(),
            $e->getMessage()
        );
        die;
    }
}
