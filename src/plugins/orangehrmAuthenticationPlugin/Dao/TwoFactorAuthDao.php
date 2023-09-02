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

namespace OrangeHRM\Authentication\Dao;

use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\TwoFactorAuthDetail;
use OrangeHRM\ORM\ListSorter;

class TwoFactorAuthDao extends BaseDao
{
    /**
     * @param int $userId
     * @return TwoFactorAuthDetail|null
     */
    public function getTwoFactorAuthDetailByUserId(int $userId): ?TwoFactorAuthDetail
    {
        $q = $this->createQueryBuilder(TwoFactorAuthDetail::class, 'tfa')
            ->leftJoin('tfa.user', 'user')
            ->andWhere('user.id = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('tfa.enrolled = :enrolled')
            ->setParameter('enrolled', true);
        return $q->getQuery()->getOneOrNullResult();
    }

    /**
     * @param TwoFactorAuthDetail $twoFactorAuthDetail
     * @return TwoFactorAuthDetail
     */
    public function saveTwoFactorAuthDetail(TwoFactorAuthDetail $twoFactorAuthDetail): TwoFactorAuthDetail
    {
        $this->persist($twoFactorAuthDetail);
        return $twoFactorAuthDetail;
    }

    /**
     * @param int $userId
     * @param int|null $enrollmentId
     * @return TwoFactorAuthDetail|null
     */
    public function getTwoFactorAuthDetailForEnrollment(int $userId, ?int $enrollmentId): ?TwoFactorAuthDetail
    {
        $q = $this->createQueryBuilder(TwoFactorAuthDetail::class, 'tfa')
            ->leftJoin('tfa.user', 'user')
            ->andWhere('user.id = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('tfa.enrolled = :enrolled')
            ->setParameter('enrolled', false);

        if ($enrollmentId !== null) {
            $q->andWhere('tfa.id = :enrollmentId')
                ->setParameter('enrollmentId', $enrollmentId);
        }
        $q->setMaxResults(1);
        $q->orderBy('tfa.createdAtUtc', ListSorter::DESCENDING);
        return $q->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int $userId
     * @return int
     */
    public function deleteStaleTwoFactorAuthDetails(int $userId): int
    {
        $q = $this->createQueryBuilder(TwoFactorAuthDetail::class, 'tfa')
            ->delete()
            ->andWhere('tfa.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('tfa.enrolled = :enrolled')
            ->setParameter('enrolled', false);
        return $q->getQuery()->execute();
    }
}
