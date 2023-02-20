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

namespace OrangeHRM\OAuth\Repository;

use Exception;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\OAuthAuthorizationCode;
use OrangeHRM\OAuth\Dto\Entity\AuthCodeEntity;

class AuthorizationCodeRepository extends BaseDao implements AuthCodeRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $authCode = new OAuthAuthorizationCode();
        $authCode->setAuthCode($authCodeEntity->getIdentifier());
        $authCode->setClientId($authCodeEntity->getClient()->getIdentifier());
        $authCode->setUserId($authCodeEntity->getUserIdentifier());
        $authCode->setRedirectUri($authCodeEntity->getRedirectUri());
        $authCode->setExpiryDateTime($authCodeEntity->getExpiryDateTime());

        $this->persist($authCode);
    }

    /**
     * @inheritdoc
     */
    public function revokeAuthCode($codeId): void
    {
        throw new Exception(__METHOD__);
        // TODO::Some logic to revoke the auth code in a database
    }

    /**
     * @inheritdoc
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = $this->getRepository(OAuthAuthorizationCode::class)->findOneBy(['authCode' => $codeId]);
        if (!$authCode instanceof OAuthAuthorizationCode) {
            return true;
        }

        // TODO:: check whether revoked

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCodeEntity();
    }
}
