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

use OrangeHRM\Authentication\Dao\TwoFactorAuthDao;
use OrangeHRM\Authentication\Dto\OTPEnrollment;
use OrangeHRM\Authentication\Dto\TwoFactorAuthConfig;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Entity\TwoFactorAuthDetail;

class TwoFactorAuthService
{
    use UserRoleManagerTrait;

    private TwoFactorAuthDao $twoFactorAuthDao;
    private OTPService $otpService;

    /**
     * @return TwoFactorAuthDao
     */
    public function geTwoFactorAuthDao(): TwoFactorAuthDao
    {
        return $this->twoFactorAuthDao ??= new TwoFactorAuthDao();
    }

    /**
     * @return OTPService
     */
    protected function getOTPService(): OTPService
    {
        return $this->otpService ??= new OTPService();
    }

    /**
     * @return bool
     */
    public function isLoggedInUserProvisioned(): bool
    {
        $userId = $this->getUserRoleManager()->getUser()->getId();
        return $this->isUserProvisioned($userId);
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isUserProvisioned(int $userId): bool
    {
        $twoFactorAuthDetail = $this->geTwoFactorAuthDao()->getTwoFactorAuthDetailByUserId($userId);
        return $twoFactorAuthDetail instanceof TwoFactorAuthDetail && !empty($twoFactorAuthDetail->getSecret());
    }

    /**
     * @return OTPEnrollment
     */
    public function getOTPEnrollmentForLoggedInUser(): OTPEnrollment
    {
        $username = $this->getUserRoleManager()->getUser()->getUserName();
        $otpConfig = new TwoFactorAuthConfig(); // TODO
        $otpEnrollment = $this->getOTPService()->getOTPEnrollmentForProvisioning($otpConfig, $username);
        $twoFactorAuthDetail = $this->geTwoFactorAuthDao()->saveTwoFactorAuthDetail(
            $this->getTwoFactorAuthDetailFromOTPEnrollment($otpEnrollment)
        );
        $otpEnrollment->setId($twoFactorAuthDetail->getId());
        return $otpEnrollment;
    }

    /**
     * @param OTPEnrollment $otpEnrollment
     * @return TwoFactorAuthDetail
     */
    protected function getTwoFactorAuthDetailFromOTPEnrollment(OTPEnrollment $otpEnrollment): TwoFactorAuthDetail
    {
        $userId = $this->getUserRoleManager()->getUser()->getId();
        $twoFactorAuthDetail = new TwoFactorAuthDetail();
        $twoFactorAuthDetail->getDecorator()->setUserByUserId($userId);
        $twoFactorAuthDetail->setType(TwoFactorAuthDetail::TYPE_TOTP);
        $twoFactorAuthDetail->setSecret($otpEnrollment->getSecret());
        $twoFactorAuthDetail->setCreatedAtUtc();
        return $twoFactorAuthDetail;
    }

    /**
     * @param string $secret
     * @param string $otp
     * @return bool
     */
    public function verifyOTP(string $secret, string $otp): bool
    {
        $otpConfig = new TwoFactorAuthConfig(); // TODO
        return $this->getOTPService()->verifyOTP($otpConfig, $secret, $otp);
    }
}
