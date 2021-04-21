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

namespace OrangeHRM\Core\Api\V2;

use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Dto\SearchParamHolder;
use OrangeHRM\Core\Exception\SearchParamException;
use OrangeHRM\ORM\ListSorter;

abstract class Endpoint
{
    /**
     * @var Request
     */
    private Request $request;
    /**
     * @var RequestParams
     */
    private RequestParams $requestParams;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->requestParams = new RequestParams($request);
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return RequestParams
     */
    protected function getRequestParams(): RequestParams
    {
        return $this->requestParams;
    }

    /**
     * @param RequestParams $requestParams
     */
    protected function setRequestParams(RequestParams $requestParams): void
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param SearchParamHolder $searchParamHolder
     * @param string|null $defaultSortField
     * @return SearchParamHolder
     * @throws SearchParamException
     */
    protected function setSortingAndPaginationParams(
        SearchParamHolder $searchParamHolder,
        ?string $defaultSortField = null
    ): SearchParamHolder {
        $searchParamHolder->setSortField(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                CommonParams::PARAMETER_SORT_FIELD,
                $defaultSortField ?? $searchParamHolder->getSortField()
            )
        );
        $searchParamHolder->setSortOrder(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_QUERY,
                CommonParams::PARAMETER_SORT_ORDER,
                ListSorter::ASCENDING
            )
        );
        $searchParamHolder->setLimit(
            $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_QUERY,
                CommonParams::PARAMETER_LIMIT,
                SearchParamHolder::DEFAULT_LIMIT
            )
        );
        $searchParamHolder->setOffset(
            $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_QUERY,
                CommonParams::PARAMETER_OFFSET,
                SearchParamHolder::DEFAULT_OFFSET
            )
        );
        return $searchParamHolder;
    }
}
