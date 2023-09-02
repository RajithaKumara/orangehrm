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

namespace OrangeHRM\Authentication\Controller;

use OrangeHRM\Authentication\Service\TwoFactorAuthService;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;

class TwoFactorAuthenticationController extends AbstractVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $twoFactorAuthService = new TwoFactorAuthService();
        // TODO:: first check 2FA applicable for LDAP and Google users. or selected subgroup, user role
        if ($twoFactorAuthService->isLoggedInUserProvisioned()) {
            $component = new Component('two-factor-auth');
            $component->addProp(new Prop('provisioning-uri', Prop::TYPE_STRING, ''));
        } else {
            $component = new Component('two-factor-auth-provisioning');
            $otpEnrollment = $twoFactorAuthService->getOTPEnrollmentForLoggedInUser();
            $component->addProp(new Prop('provisioning-uri', Prop::TYPE_STRING, $otpEnrollment->getProvisioningUri()));
            $component->addProp(new Prop('secret', Prop::TYPE_STRING, $otpEnrollment->getSecret()));
            $component->addProp(new Prop('enrollment-id', Prop::TYPE_NUMBER, $otpEnrollment->getId()));
        }

        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }
}
