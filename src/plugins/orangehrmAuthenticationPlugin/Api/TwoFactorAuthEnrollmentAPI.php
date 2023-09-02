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

namespace OrangeHRM\Authentication\Api;

use OrangeHRM\Authentication\Service\TwoFactorAuthService;
use OrangeHRM\Core\Api\V2\CollectionEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Entity\TwoFactorAuthDetail;

class TwoFactorAuthEnrollmentAPI extends Endpoint implements CollectionEndpoint
{
    use AuthUserTrait;

    private TwoFactorAuthService $twoFactorAuthService;

    /**
     * @return TwoFactorAuthService
     */
    public function getTwoFactorAuthService(): TwoFactorAuthService
    {
        return $this->twoFactorAuthService ??= new TwoFactorAuthService();
    }

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        $userId = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_BODY,
            'userId',
            $this->getAuthUser()->getUserId()
        ); // TODO
        $enrollmentId = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, 'enrollmentId'); // TODO
        $otp = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'otp'); // TODO

        if ($this->getTwoFactorAuthService()->isUserProvisioned($userId)) {
            throw $this->getBadRequestException('User already enrolled.');
        }

        $twoFactorAuthDetail = $this->getTwoFactorAuthService()
            ->geTwoFactorAuthDao()
            ->getTwoFactorAuthDetailForEnrollment($userId, $enrollmentId);
        if (!$twoFactorAuthDetail instanceof TwoFactorAuthDetail) {
            throw $this->getBadRequestException('Enrollment is not initiated.');
        }

        $valid = $this->getTwoFactorAuthService()->verifyOTP($twoFactorAuthDetail->getSecret(), $otp);
        if ($valid) {
            $twoFactorAuthDetail->setEnrolled(true);
            $twoFactorAuthDetail->setUpdatedAtUtc();
            $this->getTwoFactorAuthService()
                ->geTwoFactorAuthDao()
                ->saveTwoFactorAuthDetail($twoFactorAuthDetail);
            $this->getTwoFactorAuthService()
                ->geTwoFactorAuthDao()
                ->deleteStaleTwoFactorAuthDetails($userId);

            $this->getAuthUser()->setIs2FAVerified(true); // TODO
        } else {
            throw $this->getBadRequestException('Invalid or expired OTP provided.');
        }

        return new EndpointResourceResult(ArrayModel::class, []); // TODO
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('userId'), // TODO
            new ParamRule('enrollmentId'), // TODO
            new ParamRule('otp'), // TODO
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}
