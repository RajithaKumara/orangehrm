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

namespace OrangeHRM\Authentication\Subscriber;

use OrangeHRM\Authentication\Controller\TwoFactorAuthenticationController;
use OrangeHRM\Core\Controller\Exception\RequestForwardableException;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Framework\Event\AbstractEventSubscriber;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TwoFactorAuthenticationSubscriber extends AbstractEventSubscriber
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [['onControllerEvent', 70000]],
        ];
    }

    /**
     * @param ControllerEvent $event
     */
    public function onControllerEvent(ControllerEvent $event)
    {
        if ($this->getControllerInstance($event) instanceof PublicControllerInterface) {
            return;
        }

        if ($this->getAuthUser()->is2FAVerified()) {
            // If already verified not need to check further
            return;
        }

        if ($event->isMainRequest()) {
            $routeKey = $event->getRequest()->attributes->get('_route');
            $twoFactorIgnoredRoutes = [
                'apiv2_auth_two_factor_auth_enrollments',
                'apiv2_auth_two_factor_auth_verification',
            ];
            if (in_array($routeKey, $twoFactorIgnoredRoutes)) {
                // Allow access enrollment & OTP verification routes without 2FA
                return;
            }
            throw new RequestForwardableException(TwoFactorAuthenticationController::class . '::handle');
        }
    }

    /**
     * @param ControllerEvent $event
     * @return mixed
     */
    private function getControllerInstance(ControllerEvent $event)
    {
        return $event->getController()[0];
    }
}
