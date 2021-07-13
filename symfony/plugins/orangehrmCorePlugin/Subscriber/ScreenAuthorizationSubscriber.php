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

namespace OrangeHRM\Core\Subscriber;

use OrangeHRM\Authentication\Controller\ForbiddenController;
use OrangeHRM\Authentication\Exception\ForbiddenException;
use OrangeHRM\Core\Authorization\Dto\ResourcePermission;
use OrangeHRM\Core\Controller\AbstractViewController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Traits\ModuleScreenHelperTrait;
use OrangeHRM\Core\Traits\Service\TextHelperTrait;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Framework\Event\AbstractEventSubscriber;
use OrangeHRM\Framework\Framework;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\RequestStack;
use OrangeHRM\Framework\Http\Response;
use OrangeHRM\Framework\Services;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ScreenAuthorizationSubscriber extends AbstractEventSubscriber
{
    use ServiceContainerTrait;
    use UserRoleManagerTrait;
    use TextHelperTrait;
    use ModuleScreenHelperTrait;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['onControllerEvent', 80000],
            ],
            KernelEvents::EXCEPTION => [
                ['onExceptionEvent', 0],
            ],
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

        $module = $this->getCurrentModuleAndScreen()->getModule();
        $screen = $this->getCurrentModuleAndScreen()->getScreen();

        if ($module === 'auth' && $screen == 'logout') {
            return;
        }

        if ($this->getControllerInstance($event) instanceof AbstractViewController) {
            $permissions = $this->getUserRoleManager()->getScreenPermissions($module, $screen);

            if (!$permissions instanceof ResourcePermission || !$permissions->canRead()) {
                throw new ForbiddenException();
            }
        }
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onExceptionEvent(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ForbiddenException) {
            $response = $this->forward(ForbiddenController::class . '::handle');
            $event->setResponse($response);
            $event->stopPropagation();
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

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like OrangeHRM\Controller\PostController::handle)
     */
    protected function forward(string $controller, array $path = [], array $query = []): Response
    {
        $request = $this->getCurrentRequest();
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);

        /** @var Framework $kernel */
        $kernel = $this->getContainer()->get(Services::HTTP_KERNEL);
        return $kernel->handle($subRequest, Framework::SUB_REQUEST);
    }

    /**
     * @return Request|null
     */
    protected function getCurrentRequest(): ?Request
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->getContainer()->get(Services::REQUEST_STACK);
        return $requestStack->getCurrentRequest();
    }
}