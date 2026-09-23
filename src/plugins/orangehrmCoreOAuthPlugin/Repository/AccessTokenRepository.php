<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\OAuth\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use OrangeHRM\Core\Dao\BaseDao;
use Doctrine\ORM\Query\Expr\Join;
use OrangeHRM\Entity\OAuthAccessToken;
use OrangeHRM\Entity\OAuthClient;
use OrangeHRM\OAuth\Dto\Entity\AccessTokenEntity;

class AccessTokenRepository extends BaseDao implements AccessTokenRepositoryInterface
{
    use CryptTrait;

    /**
     * @inheritdoc
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $accessCode = new OAuthAccessToken();
        $accessCode->setAccessToken($accessTokenEntity->getIdentifier());
        $accessCode->setClientId($accessTokenEntity->getClient()->getIdentifier());
        $accessCode->setUserId($accessTokenEntity->getUserIdentifier());
        $accessCode->setExpiryDateTime($accessTokenEntity->getExpiryDateTime());

        try {
            $this->persist($accessCode);
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * @inheritdoc
     */
    public function revokeAccessToken($tokenId): void
    {
        $this->createQueryBuilder(OAuthAccessToken::class, 'accessToken')
            ->update()
            ->set('accessToken.revoked', ':revoked')
            ->setParameter('revoked', true)
            ->andWhere('accessToken.accessToken = :accessToken')
            ->setParameter('accessToken', $tokenId)
            ->getQuery()
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $q = $this->createQueryBuilder(OAuthAccessToken::class, 'accessToken')
            ->andWhere('accessToken.revoked = :revoked')
            ->setParameter('revoked', true)
            ->andWhere('accessToken.accessToken = :accessToken')
            ->setParameter('accessToken', $tokenId);
        return $this->getPaginator($q)->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): AccessTokenEntityInterface {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);
        $accessToken->setEncryptionKey($this->encryptionKey);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        return $accessToken;
    }

    /**
     * @param string $tokenId
     * @return OAuthAccessToken|null
     */
    public function getAccessToken(string $tokenId): ?OAuthAccessToken
    {
        return $this->getEntityManager()
            ->getRepository(OAuthAccessToken::class)
            ->findOneBy(['accessToken' => $tokenId]);
    }

    /**
     * The token, but only while the client it was issued to is still enabled.
     *
     * Disabling a client already stops it obtaining or refreshing a token --
     * ClientRepository filters on `enabled` for both grants -- but nothing
     * looked at the client again once a token was in hand, so an access token
     * kept working for the rest of its life after an administrator switched the
     * application off. For an AI client that is the one lever an incident
     * leaves you, and it did not work.
     *
     * Checked here rather than in the validator so it costs nothing: the client
     * is joined onto a query that was already being run. `clientId` is a plain
     * column rather than an association, hence the WITH join.
     *
     * Revocation and expiry are deliberately *not* filtered here. The caller
     * distinguishes them to say which one happened, and folding them in would
     * collapse three different answers into "could not be verified".
     *
     * @param string $tokenId
     * @return OAuthAccessToken|null
     */
    public function getAccessTokenForEnabledClient(string $tokenId): ?OAuthAccessToken
    {
        $q = $this->createQueryBuilder(OAuthAccessToken::class, 'accessToken')
            ->innerJoin(
                OAuthClient::class,
                'client',
                Join::WITH,
                'client.id = accessToken.clientId'
            );
        $q->andWhere($q->expr()->eq('accessToken.accessToken', ':accessToken'))
            ->setParameter('accessToken', $tokenId);
        $q->andWhere($q->expr()->eq('client.enabled', ':enabled'))
            ->setParameter('enabled', true);

        // `ohrm_oauth2_access_token.access_token` is uniquely indexed, so this
        // cannot match more than one row.
        return $q->getQuery()->getOneOrNullResult();
    }
}
