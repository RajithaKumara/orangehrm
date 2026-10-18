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

namespace OrangeHRM\Tests\Core\Registration\Helper;

use OrangeHRM\Config\Config;
use OrangeHRM\Core\Registration\Helper\SystemConfigurationHelper;
use PHPUnit\Framework\TestCase;

/**
 * @group Core
 * @group Registration
 */
class SystemConfigurationHelperTest extends TestCase
{
    /**
     * Version alone does not identify a deployment: a demo site and a customer
     * install can be running the same one. The build type is what tells them
     * apart on the receiving end.
     */
    public function testOhrmDetailsReportTheBuildType(): void
    {
        $details = (new SystemConfigurationHelper())->getOHRMDetails();

        $this->assertSame(Config::PRODUCT_VERSION, $details['version']);
        $this->assertSame(Config::PRODUCT_MODE, $details['build_type']);
    }

    /**
     * The installer sends the same payload from its own copy of this code
     * (installer/Util/SystemCheck::getSystemDetails), so the two have to agree
     * on the keys. Nothing links them but this.
     */
    public function testBuildTypeIsOneOfTheDeclaredModes(): void
    {
        $details = (new SystemConfigurationHelper())->getOHRMDetails();

        $this->assertContains($details['build_type'], [
            Config::MODE_DEV,
            Config::MODE_PROD,
            Config::MODE_TEST,
            Config::MODE_DEMO,
            Config::MODE_TRIAL,
        ]);
    }

    /**
     * Which builds report at all. Asserted as a set rather than through
     * isTelemetryReportingBuild(), because PRODUCT_MODE is a constant and a
     * test can only ever observe the build it is running on -- here, dev.
     *
     * A demo is excluded deliberately: its numbers are not a real deployment's.
     * Dev and test are excluded because every developer machine and every CI
     * run would otherwise post on install.
     */
    public function testOnlyRealDeploymentsReportTelemetry(): void
    {
        $this->assertSame([Config::MODE_PROD, Config::MODE_TRIAL], Config::TELEMETRY_MODES);

        $this->assertNotContains(Config::MODE_DEMO, Config::TELEMETRY_MODES);
        $this->assertNotContains(Config::MODE_DEV, Config::TELEMETRY_MODES);
        $this->assertNotContains(Config::MODE_TEST, Config::TELEMETRY_MODES);
    }

    /**
     * This checkout is a dev build, so nothing it does should ever reach the
     * registration endpoint. If this fails, a developer machine is reporting.
     */
    public function testThisBuildDoesNotReport(): void
    {
        $this->assertSame(Config::MODE_DEV, Config::PRODUCT_MODE);
        $this->assertFalse(Config::isTelemetryReportingBuild());
    }
}
