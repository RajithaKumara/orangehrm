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

namespace OrangeHRM\Pim\Dao;

use Exception;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\Employee;
use OrangeHRM\ORM\Doctrine;
use OrangeHRM\ORM\Paginator;
use OrangeHRM\Pim\Dto\EmployeeSearchParamHolder;

class EmployeeDao
{
    /**
     * @param EmployeeSearchParamHolder $employeeSearchParamHolder
     * @return array
     * @throws DaoException
     */
    public function getEmployeeList(EmployeeSearchParamHolder $employeeSearchParamHolder): array
    {
        try {
            $paginator = $this->getEmployeeListPaginator($employeeSearchParamHolder);
            return $paginator->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param EmployeeSearchParamHolder $employeeSearchParamHolder
     * @return int
     * @throws DaoException
     */
    public function getEmployeeCount(EmployeeSearchParamHolder $employeeSearchParamHolder): int
    {
        try {
            $paginator = $this->getEmployeeListPaginator($employeeSearchParamHolder);
            return $paginator->count();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param EmployeeSearchParamHolder $employeeSearchParamHolder
     * @return Paginator
     */
    public function getEmployeeListPaginator(EmployeeSearchParamHolder $employeeSearchParamHolder): Paginator
    {
        $q = Doctrine::getEntityManager()->getRepository(Employee::class)->createQueryBuilder('e');
        if (!is_null($employeeSearchParamHolder->getSortField())) {
            $q->addOrderBy($employeeSearchParamHolder->getSortField(), $employeeSearchParamHolder->getSortOrder());
        }
        if (!empty($employeeSearchParamHolder->getLimit())) {
            $q->setFirstResult($employeeSearchParamHolder->getOffset())
                ->setMaxResults($employeeSearchParamHolder->getLimit());
        }
        if (!$employeeSearchParamHolder->isIncludeTerminated()) {
            $q->andWhere($q->expr()->isNull('e.employeeTerminationRecord'));
        }
        if (!is_null($employeeSearchParamHolder->getName())) {
            $q->andWhere(
                $q->expr()->orX(
                    $q->expr()->like('e.firstName', ':name'),
                    $q->expr()->like('e.lastName', ':name'),
                    $q->expr()->like('e.middleName', ':name'),
                )
            );
            $q->setParameter('name', '%' . $employeeSearchParamHolder->getName() . '%');
        }
        if (!is_null($employeeSearchParamHolder->getNameOrId())) {
            $q->andWhere(
                $q->expr()->orX(
                    $q->expr()->like('e.firstName', ':nameOrId'),
                    $q->expr()->like('e.lastName', ':nameOrId'),
                    $q->expr()->like('e.middleName', ':nameOrId'),
                    $q->expr()->like('e.employeeId', ':nameOrId'),
                )
            );
            $q->setParameter('nameOrId', '%' . $employeeSearchParamHolder->getNameOrId() . '%');
        }

        return new Paginator($q);
    }

    /**
     * @param Employee $employee
     * @return Employee
     * @throws DaoException
     */
    public function saveEmployee(Employee $employee): Employee
    {
        try {
            Doctrine::getEntityManager()->persist($employee);
            Doctrine::getEntityManager()->flush();
            return $employee;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
