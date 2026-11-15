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

namespace OrangeHRM\Tests\Core\Utility;

use OrangeHRM\Core\Utility\InstanceBaseUrl;
use OrangeHRM\Tests\Util\TestCase;

/**
 * @group Core
 * @group Utility
 */
class InstanceBaseUrlTest extends TestCase
{
    /**
     * @dataProvider normalizeDataProvider
     */
    public function testNormalize(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, InstanceBaseUrl::normalize($input));
    }

    public function normalizeDataProvider(): array
    {
        return [
            ['https://hr.example.com/orangehrm/web/index.php', 'https://hr.example.com/orangehrm/web/index.php'],
            ['http://php83/os-security/web/index.php', 'http://php83/os-security/web/index.php'],
            ['  HTTPS://HR.Example.COM:8443/web/index.php/  ', 'https://hr.example.com:8443/web/index.php'],
            ['https://hr.example.com', 'https://hr.example.com'],
            ['https://hr.example.com/', 'https://hr.example.com'],
            ['http://192.168.1.10/orangehrm/web/index.php', 'http://192.168.1.10/orangehrm/web/index.php'],
            ['http://[::1]:8080/web/index.php', 'http://[::1]:8080/web/index.php'],
            [null, null],
            ['', null],
            ['   ', null],
            ['hr.example.com/orangehrm/web/index.php', null],
            ['/orangehrm/web/index.php', null],
            ['//hr.example.com/web/index.php', null],
            ['ftp://hr.example.com/web/index.php', null],
            ['javascript:alert(1)', null],
            ['https://user:pass@hr.example.com/web/index.php', null],
            ['https://hr.example.com/web/index.php?x=1', null],
            ['https://hr.example.com/web/index.php#frag', null],
            ["https://hr.example.com/web/index.php\r\nX: y", null],
            ['https://hr example.com/web/index.php', null],
        ];
    }
}
