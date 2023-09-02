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

class TwoFactorAuthConfig
{
    private string $issuer = "OrangeHRM";
    private ?int $otpWindow = null;
    private int $otpSecretEntropy = 10;
    private int $otpPeriod = 30;

    /**
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @return int|null
     */
    public function getOTPWindow(): ?int
    {
        return $this->otpWindow;
    }

    /**
     * @return int
     */
    public function getOTPSecretEntropy(): int
    {
        return $this->otpSecretEntropy;
    }

    /**
     * @return int
     */
    public function getOTPPeriod(): int
    {
        return $this->otpPeriod;
    }
}
