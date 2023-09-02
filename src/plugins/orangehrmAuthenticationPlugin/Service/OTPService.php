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
 * Boston, MA 02110-1301, USA
 */

namespace OrangeHRM\Authentication\Service;

use OrangeHRM\Authentication\Dto\OTPEnrollment;
use OrangeHRM\Authentication\Dto\TwoFactorAuthConfig;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

class OTPService
{
    use DateTimeHelperTrait;

    /**
     * @param TwoFactorAuthConfig $twoFactorAuthConfig
     * @param string $label
     * @return OTPEnrollment
     */
    public function getOTPEnrollmentForProvisioning(
        TwoFactorAuthConfig $twoFactorAuthConfig,
        string $label
    ): OTPEnrollment {
        $secret = $this->generateSecret($twoFactorAuthConfig->getOTPSecretEntropy());
        $otp = TOTP::create($secret, $twoFactorAuthConfig->getOTPPeriod());
        $otp->setLabel(str_replace(':', '-', $label));
        $otp->setIssuer($twoFactorAuthConfig->getIssuer());
        return OTPEnrollment::createFromTOTP($otp);
    }

    /**
     * @param int $randomByteLength
     * @return string
     */
    protected function generateSecret(int $randomByteLength = 10): string
    {
        return Base32::encodeUpper(random_bytes($randomByteLength));
    }

    /**
     * @param TwoFactorAuthConfig $twoFactorAuthConfig
     * @param string $secret
     * @param string $code 6 digit user input
     * @return bool
     */
    public function verifyOTP(TwoFactorAuthConfig $twoFactorAuthConfig, string $secret, string $code): bool
    {
        $otp = TOTP::create($secret, $twoFactorAuthConfig->getOTPPeriod());
        return $otp->verify(
            $code,
            $this->getDateTimeHelper()->getNowInUTC()->getTimestamp(),
            $twoFactorAuthConfig->getOTPWindow()
        );
    }
}
