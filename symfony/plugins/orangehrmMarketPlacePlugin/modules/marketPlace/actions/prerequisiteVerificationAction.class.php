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

/**
 * Class prerequisiteVerificationAction
 */
class prerequisiteVerificationAction extends baseAddonAction
{
    /**
     * @param sfRequest $request
     * @return string
     * @throws CoreServiceException
     * return array contains not installed prerequisites
     */
    public function execute($request)
    {
        $data = $request->getParameterHolder()->getAll();
        $addonId = $data['addonID'];

        $execPrerequisites = null;
        try {
            $this->checkExecPrerequisites();
        } catch (ExecServicePrerequisitesException $e) {
            $execPrerequisites = $e->getMessage();
            $this->logException($e);
        }

        try {
            $addonList = $this->getAddons();
            foreach ($addonList as $addon) {
                if ($addon['id'] == $addonId) {
                    $addonDetail = $addon;
                }
            }

            $notInstalledPrerequisites = $this->getMarcketplaceService()->addonPrerequisitesVerify($addonDetail);
            return $this->renderJson(array(
                'notInstalledPrerequisites' => $notInstalledPrerequisites,
                'execPrerequisites' => $execPrerequisites == null ? array() : explode("\n", $execPrerequisites),
            ));

        } catch (GuzzleHttp\Exception\ConnectException $e) {
            $this->logException($e);
            return $this->renderJson(array(
                'error' => true,
                'code' => self::ERROR_CODE_NO_CONNECTION,
            ));
        } catch (Exception $e) {
            $this->logException($e);
            return $this->renderJson(array(
                'error' => true,
                'code' => self::ERROR_CODE_EXCEPTION,
            ));
        }
    }

}
