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

namespace OrangeHRM\Tests\Authentication\Subscriber;

use OrangeHRM\Admin\Service\UserService;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Authentication\Subscriber\AuthenticationSubscriber;
use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\EmployeeTerminationRecord;
use OrangeHRM\Entity\User as SystemUser;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use OrangeHRM\Framework\Http\Session\MemorySessionStorage;
use OrangeHRM\Framework\Http\Session\Session;
use OrangeHRM\Framework\Services;
use OrangeHRM\Tests\Util\KernelTestCase;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Whether a bearer-authenticated request survives the account behind it being
 * switched off.
 *
 * The checks live here rather than on the OAuth path: OAuthSubscriber proves the
 * token, and this subscriber -- one event later, on KernelEvents::CONTROLLER --
 * asks whether the person it belongs to may still sign in at all. A public
 * controller such as the MCP endpoint is not handed an exception, so what has to
 * hold is that isAuthenticated() has been flipped back to false before the
 * controller runs, and the controller's own gate refuses.
 *
 * Written for review R-05: disabling a user, or terminating their employee,
 * must stop a token that was issued while they were active.
 *
 * @group Authentication
 * @group Subscriber
 */
class AuthenticationSubscriberTest extends KernelTestCase
{
    private function systemUser(bool $enabled, bool $hasEmployee, bool $terminated): SystemUser
    {
        $user = new SystemUser();
        $user->setId(7);
        $user->setStatus($enabled);

        if ($hasEmployee) {
            $employee = new Employee();
            $employee->setEmpNumber(11);
            if ($terminated) {
                $employee->setEmployeeTerminationRecord(new EmployeeTerminationRecord());
            }
            $user->setEmployee($employee);
        }

        return $user;
    }

    /**
     * @param SystemUser|null $user what the database says about the account now
     */
    private function fireControllerEvent(?SystemUser $user): bool
    {
        $userService = $this->getMockBuilder(UserService::class)->getMock();
        $userService->method('getSystemUser')->willReturn($user);

        // AuthUser is a session-backed singleton, so it is stubbed with an
        // in-memory flag: what this test cares about is whether the subscriber
        // flips it, not where it is stored.
        $authenticated = true;
        $authUser = $this->getMockBuilder(AuthUser::class)->disableOriginalConstructor()->getMock();
        $authUser->method('isAuthenticated')->willReturnCallback(
            static function () use (&$authenticated): bool {
                return $authenticated;
            }
        );
        $authUser->method('setIsAuthenticated')->willReturnCallback(
            static function (bool $status) use (&$authenticated): void {
                $authenticated = $status;
            }
        );
        $authUser->method('getUserId')->willReturn(7);

        $session = new Session(new MemorySessionStorage());
        $session->start();

        $this->createKernelWithMockServices([
            Services::USER_SERVICE => $userService,
            Services::AUTH_USER => $authUser,
            Services::SESSION => $session,
        ]);

        $controller = new class () extends AbstractController implements PublicControllerInterface {
            public function handle(Request $request): Response
            {
                return new Response();
            }
        };

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new ControllerEvent(
            $kernel,
            [$controller, 'handle'],
            Request::create('/api/mcp', 'POST'),
            HttpKernelInterface::MAIN_REQUEST
        );

        (new AuthenticationSubscriber())->onControllerEvent($event);

        return $authenticated;
    }

    public function testAnActiveUserStaysAuthenticated(): void
    {
        $this->assertTrue(
            $this->fireControllerEvent($this->systemUser(true, true, false)),
            'an active user with an unterminated employee must stay authenticated'
        );
    }

    /**
     * An administrator switching a user off is the lever reached for when
     * someone leaves or an account is compromised.
     */
    public function testADisabledUserIsDeauthenticated(): void
    {
        $this->assertFalse($this->fireControllerEvent($this->systemUser(false, true, false)));
    }

    /**
     * Termination is the HR act; disabling the user account is a separate one
     * that an administrator may not remember to do. Both have to stop a token.
     */
    public function testATerminatedEmployeeIsDeauthenticated(): void
    {
        $this->assertFalse($this->fireControllerEvent($this->systemUser(true, true, true)));
    }

    public function testAUserWithNoEmployeeIsDeauthenticated(): void
    {
        $this->assertFalse($this->fireControllerEvent($this->systemUser(true, false, false)));
    }

    /**
     * getSystemUser() filters on deleted, so a deleted account resolves to
     * nothing rather than to a disabled user.
     */
    public function testADeletedUserIsDeauthenticated(): void
    {
        $this->assertFalse($this->fireControllerEvent(null));
    }
}
