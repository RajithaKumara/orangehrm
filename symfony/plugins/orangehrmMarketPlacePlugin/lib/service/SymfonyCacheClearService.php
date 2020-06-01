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

class SymfonyCacheClearService extends BaseExecService
{
    public function clearCache()
    {
        $this->logInfo("Start clearing symfony cache.");

        if (!sfConfig::get('sf_cache_dir') || !is_dir(sfConfig::get('sf_cache_dir'))) {
            throw new sfException(sprintf('Cache directory "%s" does not exist.', sfConfig::get('sf_cache_dir')));
        }

        $this->getFilesystem()->remove(sfFinder::type('file')->discard('.*')->in(sfConfig::get('sf_cache_dir')));

        $this->logInfo("Finish clearing symfony cache.\n");
    }

    /**
     * @inheritDoc
     */
    public function checkPrerequisites()
    {
        $result = array("errors" => array());
        if (is_writable(sfConfig::get('sf_cache_dir'))) {
            $result['status'] = true;
        } else {
            $result['status'] = false;
            array_push($result['errors'], __("File write permission required to %dir% directory.", array('%dir%' => '`symfony/cache`')));
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function exec()
    {
        try {
            $this->clearCache();
        } catch (Exception $e) {
            throw new SymfonyCacheClearException('', 0, $e);
        }
    }
}
