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
 * Class installAddonAPI
 */
class installAddonAPIAction extends baseAddonAction
{
    private $pluginName;
    private $licenseDownloaded = false;

    /**
     * @param sfRequest $request
     * @return mixed|string
     */
    public function execute($request)
    {
        try {
            $this->checkExecPrerequisites();

            ini_set('max_execution_time', 0);

            $addonList = $this->getAddons();
            $data = $request->getParameterHolder()->getAll();
            $addonId = $data['installAddonID'];
            $addonURL = null;
            $addonDetail = null;
            foreach ($addonList as $addon) {
                if ($addon['id'] == $addonId) {
                    $addonDetail = $addon;
                    $addonURL = $addon['links']['file'];
                }
            }
            $addonFilePath = $this->getAddonFile($addonURL, $addonDetail);
            $this->pluginName = $this->getMarcketplaceService()->extractAddonFile($addonFilePath);
            if ($addonDetail['type'] == 'paid') {
                $addonLicenseContent = $this->getApiManagerService()->getAddonLicense($addonId);
                if (is_string($addonLicenseContent) && strlen($addonLicenseContent) > 0) {
                    file_put_contents(sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $this->pluginName . DIRECTORY_SEPARATOR . 'ohrm.license.php', $addonLicenseContent);
                } else {
                    $this->cleanPluginDir();
                    throw new Exception('Error when retrieving the license file', 1008);
                }
            }
            $this->licenseDownloaded = true;
            $result = $this->installAddon($addonFilePath, $addonDetail, $this->pluginName);
            echo json_encode($result);
            return sfView::NONE;
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            $this->cleanPluginDir();
            $this->logException($e);
            echo json_encode(self::ERROR_CODE_NO_CONNECTION);
            return sfView::NONE;
        } catch (ExecServicePrerequisitesException $e) {
            $this->cleanPluginDir();
            $this->logException($e);
            return $this->renderJson(array('message' => $e->getMessage()));

        } catch (Exception $e) {
            $this->cleanPluginDir();
            $this->logException($e);
            echo json_encode($e->getCode());
            return sfView::NONE;
        }
    }

    private function cleanPluginDir()
    {
        if ($this->pluginName && !$this->licenseDownloaded) {
            $pluginPath = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $this->pluginName;
            $this->recursiveDeletePlugin($pluginPath);
        }
    }

    /**
     * @param $addonURL
     * @param $addonDetail
     * @return string
     * @throws CoreServiceException
     */
    private function getAddonFile($addonURL, $addonDetail)
    {
        $addonFilePath = $this->getApiManagerService()->getAddonFile($addonURL, $addonDetail);
        return $addonFilePath;
    }

    /**
     * @param $addonFilePath
     * @param $addonDetail
     * @param $pluginname
     * @return bool
     * @throws DaoException
     * @throws DoctrineBuildModelException
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Transaction_Exception
     * @throws Doctrine_Validator_Exception
     * @throws PublishAssetException
     * @throws SymfonyCacheClearException
     */
    protected function installAddon($addonFilePath, $addonDetail, $pluginname)
    {
        $connection = Doctrine_Manager::getInstance()->getCurrentConnection();
        $connection->beginTransaction();
        $pluginInstallFilePath = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginname . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'plugin_install.php';

        $this->getSymfonyCacheClearService()->exec();

        try {
            $install = require_once($pluginInstallFilePath);
        } catch (Exception $e) {
            $connection->rollback();
            $this->logException($e);
            throw new Exception('installation query fails', 1002);
        }
        if (!$install) {
            throw new Exception('install file execution failed.', 1003);
        }

        $this->getPublishAssetService()->exec();

        $this->getDoctrineBuildModelService()->exec();

        // commit plugin installation queries only when symfony cache clear, orangehrm publish assets, doctrine build model finished
        $connection->commit();

        if ($addonDetail['type'] != "paid") {
            $data = array(
                'id' => $addonDetail['id'],
                'addonName' => $addonDetail['title'],
                'status' => MarketplaceDao::ADDON_STATUS_INSTALLED,
                'type' => $addonDetail['type'],
                'pluginName' => $pluginname,
                'version' => $addonDetail['version']['name']
            );
            $result = $this->getMarcketplaceService()->installOrRequestAddon($data);
        } else {
            $data = array(
                'id' => $addonDetail['id'],
                'addonName' => $addonDetail['title'],
                'status' => MarketplaceDao::ADDON_STATUS_INSTALLED,
                'pluginName' => $pluginname
            );
            $result = $this->getMarcketplaceService()->updateAddon($data);
        }

        if (!$result) {
            throw new Exception('Can not add to OrangeHRM database. Uninstallation will cause errors. But plugin can used.', 1006);
        }
        // clearing menu item cache so that new menus will be added.
        $this->getUser()->getAttributeHolder()->remove(mainMenuComponent::MAIN_MENU_USER_ATTRIBUTE);
        return $result;
    }
}
