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
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\ReportTo;
use OrangeHRM\ORM\Paginator;
use OrangeHRM\Pim\Dto\EmployeeSearchFilterParams;

class EmployeeDao extends BaseDao
{
    /**
     * @param EmployeeSearchFilterParams $employeeSearchParamHolder
     * @return Employee[]
     * @throws DaoException
     */
    public function getEmployeeList(EmployeeSearchFilterParams $employeeSearchParamHolder): array
    {
        try {
            $paginator = $this->getEmployeeListPaginator($employeeSearchParamHolder);
            return $paginator->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param EmployeeSearchFilterParams $employeeSearchParamHolder
     * @return int
     * @throws DaoException
     */
    public function getEmployeeCount(EmployeeSearchFilterParams $employeeSearchParamHolder): int
    {
        try {
            $paginator = $this->getEmployeeListPaginator($employeeSearchParamHolder);
            return $paginator->count();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param EmployeeSearchFilterParams $employeeSearchParamHolder
     * @return Paginator
     */
    public function getEmployeeListPaginator(EmployeeSearchFilterParams $employeeSearchParamHolder): Paginator
    {
        $q = $this->createQueryBuilder(Employee::class, 'e');
        $this->setSortingAndPaginationParams($q, $employeeSearchParamHolder);
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

        if (!is_null($employeeSearchParamHolder->getEmployeeNumbers())) {
            $q->andWhere($q->expr()->in('e.empNumber', ':empNumbers'))
                ->setParameter('empNumbers', $employeeSearchParamHolder->getEmployeeNumbers());
        }

        return $this->getPaginator($q);
    }

    /**
     * @param Employee $employee
     * @return Employee
     * @throws DaoException
     */
    public function saveEmployee(Employee $employee): Employee
    {
        try {
            $this->persist($employee);
            return $employee;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int $empNumber
     * @return Employee|null
     * @throws DaoException
     */
    public function getEmployeeByEmpNumber(int $empNumber): ?Employee
    {
        try {
            $employee = $this->getRepository(Employee::class)->find($empNumber);
            if ($employee instanceof Employee) {
                return $employee;
            }
            return null;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int $supervisorId
     * @param bool $includeChain
     * @param array $supervisorIdStack
     * @param int|null $maxDepth
     * @param int $depth
     * @return int[]
     * @throws DaoException
     */
    public function getSubordinateIdListBySupervisorId(
        int $supervisorId,
        bool $includeChain = false,
        array $supervisorIdStack = [],
        ?int $maxDepth = null,
        int $depth = 1
    ): array {
        try {
            $employeeIdList = [];
            $q = $this->createQueryBuilder(ReportTo::class, 'r');
            $q->andWhere('r.supervisor = :supervisorId')
                ->setParameter('supervisorId', $supervisorId);

            /** @var ReportTo[] $reportToArray */
            $reportToArray = $q->getQuery()->execute();

            foreach ($reportToArray as $reportTo) {
                $subordinateEmpNumber = $reportTo->getSubordinate()->getEmpNumber();
                array_push($employeeIdList, $subordinateEmpNumber);

                if ($includeChain || (!is_null($maxDepth) && ($depth < $maxDepth))) {
                    if (!in_array($subordinateEmpNumber, $supervisorIdStack)) {
                        $supervisorIdStack[] = $subordinateEmpNumber;
                        $subordinateIdList = $this->getSubordinateIdListBySupervisorId(
                            $subordinateEmpNumber,
                            $includeChain,
                            $supervisorIdStack,
                            $maxDepth,
                            $depth + 1
                        );
                        if (count($subordinateIdList) > 0) {
                            foreach ($subordinateIdList as $id) {
                                array_push($employeeIdList, $id);
                            }
                        }
                    }
                }
            }
            return $employeeIdList;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return int
     */
    public function getNumberOfEmployees(): int
    {
        $q = $this->createQueryBuilder(Employee::class, 'e');
        return $this->count($q);
    }

    /**
     * Return List of Subordinates for given Supervisor
     *
     * @param int $supervisorId Supervisor Id
     * @param bool $includeTerminated Terminated status
     * @param bool $includeChain
     * @param array $supervisorIdStack
     * @return Employee[] of Subordinates
     * @throws DaoException
     */
    public function getSubordinateList(
        int $supervisorId,
        bool $includeTerminated = false,
        bool $includeChain = false,
        array $supervisorIdStack = []
    ): array {
        try {
            $employeeList = [];
            $q = $this->createQueryBuilder(ReportTo::class, 'rt');
            $q->leftJoin('rt.subordinate', 'e');
            $q->andWhere('rt.supervisor = :supervisorId')
                ->setParameter('supervisorId', $supervisorId);

            if ($includeTerminated == false) {
                $q->andWhere($q->expr()->isNull('e.employeeTerminationRecord'));
            }

            /** @var ReportTo[] $reportToArray */
            $reportToArray = $q->getQuery()->execute();

            foreach ($reportToArray as $reportTo) {
                $employeeList[] = $reportTo->getSubordinate();

                if ($includeChain) {
                    $subordinateEmpNumber = $reportTo->getSubordinate()->getEmpNumber();
                    if (!in_array($subordinateEmpNumber, $supervisorIdStack)) {
                        $supervisorIdStack[] = $subordinateEmpNumber;
                        $subordinateList = $this->getSubordinateList(
                            $subordinateEmpNumber,
                            $includeTerminated,
                            $includeChain,
                            $supervisorIdStack
                        );
                        if (count($subordinateList) > 0) {
                            foreach ($subordinateList as $sub) {
                                $employeeList[] = $sub;
                            }
                        }
                    }
                }
            }

            return $employeeList;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Check if employee with given empNumber is a supervisor
     * @param int $empNumber
     * @return bool - True if given employee is a supervisor, false if not
     */
    public function isSupervisor(int $empNumber): bool
    {
        try {
            $q = $this->createQueryBuilder(ReportTo::class, 'r');
            $q->andWhere('r.supervisor = :supervisorId')
                ->setParameter('supervisorId', $empNumber);

            return ($this->count($q) > 0);
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get Employee id list
     *
     * @param bool $excludeTerminatedEmployees
     * @returns int[] EmployeeId List
     * @throws DaoException
     */
    public function getEmployeeIdList(bool $excludeTerminatedEmployees = false)
    {
        try {
            $q = $this->createQueryBuilder(Employee::class, 'e');
            $q->select('e.empNumber');
            $q->addOrderBy('e.empNumber');

            if ($excludeTerminatedEmployees) {
                $q->andWhere($q->expr()->isNull('e.employeeTerminationRecord'));
            }

            $result = $q->getQuery()->getArrayResult();
            return array_column($result, 'empNumber');
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
