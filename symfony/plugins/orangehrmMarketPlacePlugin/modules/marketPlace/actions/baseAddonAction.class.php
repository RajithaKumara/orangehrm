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
 * Class baseAddonAction
 */
abstract class baseAddonAction extends sfAction
{
    /**
     * No Network Error Message
     */
    const NO_NETWORK_ERR_MESSAGE = "Please connect to the internet to view the available add-ons.";
    /**
     * Marketplace middlewere Error Message
     */
    const MP_MIDDLEWERE_ERR_MESSAGE = "Error Occur Please try again later";
    /**
     * network error code
     */
    const ERROR_CODE_NO_CONNECTION = 3000;
    /**
     * Marketplace error message code
     */
    const ERROR_CODE_EXCEPTION = 1;

    private $marcketplaceService = null;
    private $apiManagerService = null;
    private $addonList = null;

    protected $symfonyCacheClearService = null;
    protected $publishAssetService = null;
    protected $doctrineBuildModelService = null;

    /**
     * @return APIManagerService
     */
    protected function getApiManagerService()
    {
        if (!isset($this->apiManagerService)) {
            $this->apiManagerService = new APIManagerService();
        }
        return $this->apiManagerService;
    }

    /**
     * @return MarketplaceService|null
     */
    protected function getMarcketplaceService()
    {
        if (!isset($this->marcketplaceService)) {
            $this->marcketplaceService = new MarketplaceService();
        }
        return $this->marcketplaceService;
    }

    /**
     * @return array
     */
    protected function getInstalledAddons()
    {
        return $this->getMarcketplaceService()->getInstalledAddons();
    }

    /**
     * @param bool $includeDescription
     * @return array
     * @throws CoreServiceException
     */
    protected function getAddons($includeDescription = false)
    {
        if (!isset($this->addonList)) {
            $this->addonList = $this->getApiManagerService()->getAddons($includeDescription);
        }
        return $this->addonList;
    }

    /**
     * @return Logger
     */
    public function getMarketPlaceLogger()
    {
        return Logger::getLogger("marketplace");
    }

    /**
     * @param Exception $exception
     */
    public function logException($exception)
    {
        $this->getMarketPlaceLogger()->error($exception->getCode() . ' : ' . $exception->getMessage());
        $this->getMarketPlaceLogger()->error($exception->getTraceAsString());
    }

    /**
     * @return SymfonyCacheClearService|null
     */
    public function getSymfonyCacheClearService()
    {
        if (is_null($this->symfonyCacheClearService)) {
            $this->symfonyCacheClearService = new SymfonyCacheClearService();
        }
        return $this->symfonyCacheClearService;
    }

    /**
     * @return PublishAssetService|null
     */
    public function getPublishAssetService()
    {
        if (is_null($this->publishAssetService)) {
            $this->publishAssetService = new PublishAssetService();
        }
        return $this->publishAssetService;
    }

    /**
     * @return DoctrineBuildModelService|null
     */
    public function getDoctrineBuildModelService()
    {
        if (is_null($this->doctrineBuildModelService)) {
            $this->doctrineBuildModelService = new DoctrineBuildModelService();
        }
        return $this->doctrineBuildModelService;
    }

    /**
     * Return array of errors and status
     * array("status"=>true,"errors"=>array("Error message"))
     * @return array
     */
    public function getGeneralPrerequisites()
    {
        $result = array("status" => true, "errors" => array());
        if (!is_writable(sfConfig::get('sf_plugins_dir'))) {
            $result['status'] = false;
            array_push($result['errors'], __("File write permission required to %dir% directory.", array('%dir%' => '`symfony/plugins`')));
        }
        return $result;
    }

    /**
     * @param $directory
     * @return bool
     */
    public function recursiveDeletePlugin($directory)
    {
        $dir = opendir($directory);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $directory . DIRECTORY_SEPARATOR . $file;
                if (is_dir($full)) {
                    $this->recursiveDeletePlugin($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        return rmdir($directory);
    }

    /**
     * Check prerequisites to execute symfony cache clear, orangehrm publish assets, doctrine build model. Throw ExecServicePrerequisitesException if failed.
     * @return bool
     * @throws ExecServicePrerequisitesException
     */
    public function checkExecPrerequisites()
    {
        $symfonyCacheClearPrerequisites = $this->getSymfonyCacheClearService()->checkPrerequisites();
        $publishAssetPrerequisites = $this->getPublishAssetService()->checkPrerequisites();
        $doctrineBuildModelPrerequisites = $this->getDoctrineBuildModelService()->checkPrerequisites();
        $generalPrerequisites = $this->getGeneralPrerequisites();

        if (!$symfonyCacheClearPrerequisites['status'] ||
            !$publishAssetPrerequisites['status'] ||
            !$doctrineBuildModelPrerequisites['status'] ||
            !$generalPrerequisites['status']) {

            $errors = array_merge(
                $symfonyCacheClearPrerequisites['errors'],
                $publishAssetPrerequisites['errors'],
                $doctrineBuildModelPrerequisites['errors'],
                $generalPrerequisites['errors']
            );
            throw new ExecServicePrerequisitesException(implode("\n", $errors));
        }

        return true;
    }
}
