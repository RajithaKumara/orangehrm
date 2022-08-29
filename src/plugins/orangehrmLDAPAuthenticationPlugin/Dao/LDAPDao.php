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

namespace OrangeHRM\LDAP\Dao;

use OrangeHRM\Admin\Dto\UserSearchFilterParams;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\User;
use OrangeHRM\ORM\Paginator;

class LDAPDao extends BaseDao
{
    /**
     * @param UserSearchFilterParams $userSearchParamHolder
     * @return Paginator
     */
    public function getSearchUserPaginator(UserSearchFilterParams $userSearchParamHolder): Paginator
    {
        $q = $this->createQueryBuilder(User::class, 'user');
        $q->leftJoin('user.userRole', 'role');
        $q->leftJoin('user.employee', 'employee');
        $q->leftJoin('user.authProviders', 'providers');

        if (!is_null($userSearchParamHolder->getStatus())) {
            $q->andWhere('u.status = :status');
            $q->setParameter('status', $userSearchParamHolder->getStatus());
        }

        $q->andWhere('u.deleted = :deleted');
        $q->setParameter('deleted', false);

        return $this->getPaginator($q);
    }
}
