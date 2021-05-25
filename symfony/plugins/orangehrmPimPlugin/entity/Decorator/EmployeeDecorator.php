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

namespace OrangeHRM\Entity\Decorator;

use DateTime;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\Nationality;

class EmployeeDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var Employee
     */
    protected Employee $employee;

    /**
     * @param Employee $employee
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    /**
     * @return Employee
     */
    protected function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * @return string|null
     */
    public function getDrivingLicenseExpiredDate(): ?string
    {
        $date = $this->getEmployee()->getDrivingLicenseExpiredDate();
        return $date ? $date->format('Y-m-d') : null;
    }

    /**
     * @param string|null $drivingLicenseExpiredDate
     */
    public function setDrivingLicenseExpiredDate(?string $drivingLicenseExpiredDate): void
    {
        if (!is_null($drivingLicenseExpiredDate)) {
            $drivingLicenseExpiredDate = new DateTime($drivingLicenseExpiredDate);
        }
        $this->getEmployee()->setDrivingLicenseExpiredDate($drivingLicenseExpiredDate);
    }

    /**
     * @return string|null
     */
    public function getBirthday(): ?string
    {
        $date = $this->getEmployee()->getBirthday();
        return $date ? $date->format('Y-m-d') : null;
    }

    /**
     * @param string|null $birthday
     */
    public function setBirthday(?string $birthday): void
    {
        if (!is_null($birthday)) {
            $birthday = new DateTime($birthday);
        }
        $this->getEmployee()->setBirthday($birthday);
    }

    /**
     * @return bool
     */
    public function getSmoker(): bool
    {
        return $this->getEmployee()->getSmoker() == 1;
    }

    /**
     * @param bool|null $smoker
     */
    public function setSmoker(?bool $smoker): void
    {
        if (!is_null($smoker)) {
            $smoker = $smoker == 1;
        }
        $this->getEmployee()->setSmoker($smoker);
    }

    /**
     * @param int|null $id
     */
    public function setNationality(?int $id): void
    {
        $nationality = null;
        if (!is_null($id)) {
            /** @var Nationality|null $nationality */
            $nationality = $this->getReference(Nationality::class, $id);
        }
        $this->getEmployee()->setNationality($nationality);
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->getEmployee()->getEmployeeTerminationRecord() == null ?
            Employee::STATE_ACTIVE : Employee::STATE_TERMINATED;
    }
}
