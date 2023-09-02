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

namespace OrangeHRM\Authentication\Dto;

use OTPHP\TOTP;

class OTPEnrollment
{
    private string $secret;
    private string $label;
    private string $issuer;
    private string $provisioningUri;
    private int $period;
    private ?int $id = null;

    /**
     * @param string $secret
     * @param string $label
     * @param string $issuer
     * @param string $provisioningUri
     * @param int $period
     */
    public function __construct(string $secret, string $label, string $issuer, string $provisioningUri, int $period)
    {
        $this->secret = $secret;
        $this->label = $label;
        $this->issuer = $issuer;
        $this->provisioningUri = $provisioningUri;
        $this->period = $period;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    public function getProvisioningUri(): string
    {
        return $this->provisioningUri;
    }

    /**
     * @return int
     */
    public function getPeriod(): int
    {
        return $this->period;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param TOTP $totp
     * @return self
     */
    public static function createFromTOTP(TOTP $totp): self
    {
        return new self(
            $totp->getSecret(),
            $totp->getLabel(),
            $totp->getIssuer(),
            $totp->getProvisioningUri(),
            $totp->getPeriod(),
        );
    }
}
