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

namespace OrangeHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\TwoFactorAuthDetailDecorator;

/**
 * @method TwoFactorAuthDetailDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_user_2fa")
 * @ORM\Entity
 */
class TwoFactorAuthDetail
{
    use DecoratorTrait;
    use DateTimeHelperTrait;

    public const TYPE_TOTP = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private User $user;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private int $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="secret", type="string", length=255, nullable=true)
     */
    private ?string $secret = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="enrolled", type="boolean")
     */
    private bool $enrolled = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at_utc", type="datetime")
     */
    private DateTime $createdAtUtc;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="updated_at_utc", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAtUtc = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return bool
     */
    public function isEnrolled(): bool
    {
        return $this->enrolled;
    }

    /**
     * @param bool $enrolled
     */
    public function setEnrolled(bool $enrolled): void
    {
        $this->enrolled = $enrolled;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAtUtc(): DateTime
    {
        return $this->createdAtUtc;
    }

    public function setCreatedAtUtc(): void
    {
        $this->createdAtUtc = $this->getDateTimeHelper()->getNowInUTC();
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAtUtc(): ?DateTime
    {
        return $this->updatedAtUtc;
    }

    public function setUpdatedAtUtc(): void
    {
        $this->updatedAtUtc = $this->getDateTimeHelper()->getNowInUTC();
    }
}
