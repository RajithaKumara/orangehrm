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

abstract class BaseExecService
{
    /**
     * @var sfFilesystem|null
     */
    protected $fileSystem = null;

    /**
     * @var sfApplicationConfiguration|null
     */
    protected $configuration = null;

    public function __construct()
    {
        $this->configuration = sfContext::getInstance()->getConfiguration();
    }

    /**
     * @param sfApplicationConfiguration $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Return array of errors and status
     * array("status"=>true,"errors"=>array("Error message"))
     * @return array
     */
    public abstract function checkPrerequisites();

    /**
     * Execute symfony task as service
     */
    public abstract function exec();

    /**
     * @return sfFilesystem
     */
    protected function getFilesystem()
    {
        if (is_null($this->fileSystem)) {
            $this->fileSystem = new sfFilesystem();
        }
        return $this->fileSystem;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return Logger::getLogger("marketplace");
    }

    /**
     * @param string $message
     */
    protected function logInfo($message)
    {
        $this->getLogger()->forcedLog(Logger::class, null, LoggerLevel::getLevelInfo(), $message);
    }
}
