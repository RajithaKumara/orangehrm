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

namespace OrangeHRM\Rest\Admin;

use JobTitleService;
use OrangeHRM\Entity\JobTitle;
use Orangehrm\Rest\Api\EndPoint;
use Orangehrm\Rest\Api\Exception\RecordNotFoundException;
use OrangeHRM\Rest\Admin\Model\JobTitleModel;
use Orangehrm\Rest\Http\Response;

class JobTitleAPI extends EndPoint
{
    /**
     * @var null|JobTitleService
     */
    protected $jobTitleService = null;

    const PARAMETER_ID = 'id';
    const PARAMETER_TITLE = 'title';
    const PARAMETER_DESCRIPTION = 'description';
    const PARAMETER_NOTE = 'note';
    const PARAMETER_SPECIFICATION = 'specification';

    /**
     * @param JobTitleService $jobTitleService
     */
    public function setJobTitleService(JobTitleService $jobTitleService)
    {
        $this->jobTitleService = $jobTitleService;
    }

    /**
     * @return JobTitleService
     */
    public function getJobTitleService(): JobTitleService
    {
        if (is_null($this->jobTitleService)) {
            $this->jobTitleService = new JobTitleService();
        }
        return $this->jobTitleService;
    }

    public function getJobTitle()
    {
        $id = $this->getRequestParams()->getQueryParam(self::PARAMETER_ID);
        $jobTitle = $this->getJobTitleService()->getJobTitleById($id);
        if (!$jobTitle instanceof JobTitle) {
            throw new RecordNotFoundException('No Record Found');
        }
        return new Response(
            (new JobTitleModel($jobTitle))->toArray()
        );
    }

    public function saveJobTitle()
    {
        // TODO:: Check data group permission
        $params = $this->getPostParameters();
        if (!empty($params[self::PARAMETER_ID])) {
            $jobTitleObj = $this->getJobTitleService()->getJobTitleById($params[self::PARAMETER_ID]);
        } else {
            $jobTitleObj = new JobTitle();
        }

        $jobTitleObj->setJobTitleName($params[self::PARAMETER_TITLE]);
        $jobTitleObj->setJobDescription($params[self::PARAMETER_DESCRIPTION]);
        $jobTitleObj->setNote($params[self::PARAMETER_NOTE]);

        $jobTitleObj = $this->getJobTitleService()->saveJobTitle($jobTitleObj);

        return new Response(
            (new JobTitleModel($jobTitleObj))->toArray()
        );
    }

    /**
     * @return array
     */
    public function getPostParameters(): array
    {
        $params = [];
        $params[self::PARAMETER_ID] = $this->getRequestParams()->getPostParam(self::PARAMETER_ID);
        $params[self::PARAMETER_TITLE] = $this->getRequestParams()->getPostParam(self::PARAMETER_TITLE);
        $params[self::PARAMETER_DESCRIPTION] = $this->getRequestParams()->getPostParam(self::PARAMETER_DESCRIPTION);
        $params[self::PARAMETER_NOTE] = $this->getRequestParams()->getPostParam(self::PARAMETER_NOTE);
        $params[self::PARAMETER_SPECIFICATION] = $this->getRequestParams()->getPostParam(self::PARAMETER_SPECIFICATION);
        return $params;
    }
}
