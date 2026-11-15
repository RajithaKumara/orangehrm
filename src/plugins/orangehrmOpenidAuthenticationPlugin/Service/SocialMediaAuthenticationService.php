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

namespace OrangeHRM\OpenidAuthentication\Service;

use OrangeHRM\Admin\Dao\UserDao;
use OrangeHRM\Admin\Dto\UserSearchFilterParams;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Authentication\Exception\AuthenticationException;
use OrangeHRM\Authentication\Service\AuthenticationService;
use OrangeHRM\Authentication\Traits\Service\AuthenticationServiceTrait;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\LoggerTrait;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Core\Utility\EncryptionHelperTrait;
use OrangeHRM\Entity\AuthProviderExtraDetails;
use OrangeHRM\Entity\EmployeeTerminationRecord;
use OrangeHRM\Entity\OpenIdProvider;
use OrangeHRM\Entity\OpenIdUserIdentity;
use OrangeHRM\Entity\User;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\OpenidAuthentication\Dao\AuthProviderDao;
use OrangeHRM\OpenidAuthentication\Dto\ProviderSearchFilterParams;
use OrangeHRM\OpenidAuthentication\OpenID\OpenIDConnectClient;
use OrangeHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class SocialMediaAuthenticationService
{
    use SocialMediaAuthenticationServiceTrait;
    use AuthenticationServiceTrait;
    use EncryptionHelperTrait;
    use AuthUserTrait;
    use ConfigServiceTrait;
    use LoggerTrait;

    private AuthenticationService $authenticationService;
    private AuthProviderDao $authProviderDao;
    private UserDao $userDao;

    public const SCOPE = 'email';
    public const CLAIM_SUBJECT = 'sub';
    public const CLAIM_EMAIL = 'email';
    public const CLAIM_EMAIL_VERIFIED = 'email_verified';

    /**
     * @return AuthProviderDao
     */
    public function getAuthProviderDao(): AuthProviderDao
    {
        return $this->authProviderDao ??= new AuthProviderDao();
    }

    /**
     * @return UserDao
     */
    public function getUserDao(): UserDao
    {
        return $this->userDao ??= new UserDao();
    }

    /**
     * @param AuthProviderExtraDetails $provider
     * @param string $scope
     * @param string $redirectUrl
     *
     * @return OpenIDConnectClient
     */
    public function initiateAuthentication(AuthProviderExtraDetails $provider, string $scope, string $redirectUrl): OpenIDConnectClient
    {
        $oidcClient = new OpenIDConnectClient(
            $provider->getOpenIdProvider()->getProviderUrl(),
            $provider->getClientId(),
            self::encryptionEnabled()
                ? self::getCryptographer()->decrypt($provider->getClientSecret())
                : $provider->getClientSecret(),
        );

        $oidcClient->addScope([$scope]);
        $oidcClient->setRedirectURL($redirectUrl);

        return $oidcClient;
    }

    /**
     * @return string
     */
    public function getRedirectURL(): string
    {
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        return $urlGenerator->generate('auth_oidc_login_redirect', [], UrlGenerator::ABSOLUTE_URL);
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return self::SCOPE;
    }

    /**
     * @param UserCredential $userCredential
     * @return User[]
     */
    private function getSystemUsers(UserCredential $userCredential): array
    {
        $userSearchFilterParams = new UserSearchFilterParams();
        $userSearchFilterParams->setUsername($userCredential->getUsername());

        return $this->getUserDao()->searchSystemUsers($userSearchFilterParams);
    }

    /**
     * Returns the email address the provider vouches for, to be matched against a username.
     * The userinfo response must belong to the subject of the verified ID token, carry an email,
     * and mark it as verified (`email_verified` true). A provider that sends no `email_verified`
     * claim at all is accepted only if its id is listed in the
     * `openId.trust_email_without_verified_claim` config row.
     *
     * @param mixed $idTokenClaims verified ID token claims
     * @param mixed $userInfo userinfo response
     * @param OpenIdProvider $provider
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getVerifiedEmail($idTokenClaims, $userInfo, OpenIdProvider $provider): string
    {
        if (!is_object($idTokenClaims) || !is_object($userInfo)) {
            throw $this->rejectClaims($provider, 'missing ID token claims or userinfo response');
        }

        $subject = $idTokenClaims->{self::CLAIM_SUBJECT} ?? null;
        if (!is_string($subject) || $subject === '' || ($userInfo->{self::CLAIM_SUBJECT} ?? null) !== $subject) {
            throw $this->rejectClaims($provider, 'userinfo subject does not match the ID token subject');
        }

        $email = $userInfo->{self::CLAIM_EMAIL} ?? null;
        if (!is_string($email) || trim($email) === '') {
            throw $this->rejectClaims($provider, 'no email claim');
        }
        if (property_exists($idTokenClaims, self::CLAIM_EMAIL)
            && (!is_string($idTokenClaims->{self::CLAIM_EMAIL})
                || !$this->isSameIdentifier($idTokenClaims->{self::CLAIM_EMAIL}, $email))) {
            throw $this->rejectClaims($provider, 'ID token email differs from userinfo email');
        }

        // An explicit non-true email_verified in either source refuses. A true value counts only from a
        // source that also carries the email (the ID token's email was compared above).
        $verifiedForEmail = false;
        foreach ([$userInfo, $idTokenClaims] as $claims) {
            if (!property_exists($claims, self::CLAIM_EMAIL_VERIFIED)) {
                continue;
            }
            $verified = $claims->{self::CLAIM_EMAIL_VERIFIED};
            // Some providers (e.g. Amazon Cognito userinfo) send the string "true"
            if ($verified !== true && $verified !== 'true') {
                throw $this->rejectClaims($provider, 'email is not verified by the provider');
            }
            if (property_exists($claims, self::CLAIM_EMAIL)) {
                $verifiedForEmail = true;
            }
        }

        if (!$verifiedForEmail && !in_array(
            $provider->getId(),
            $this->getConfigService()->getOpenIdProviderIdsTrustingEmailWithoutVerifiedClaim(),
            true
        )) {
            throw $this->rejectClaims(
                $provider,
                'no email_verified claim for the email; list the provider id in hs_hr_config `'
                . ConfigService::KEY_OPENID_TRUST_EMAIL_WITHOUT_VERIFIED_CLAIM
                . '` to accept its emails without it'
            );
        }

        return $email;
    }

    /**
     * @param OpenIdProvider $provider
     * @param string $reason
     * @return AuthenticationException
     */
    private function rejectClaims(OpenIdProvider $provider, string $reason): AuthenticationException
    {
        $this->getLogger()->warning(
            sprintf('OpenID Connect sign-in refused (provider id %d): %s', $provider->getId(), $reason)
        );
        return AuthenticationException::invalidCredentials();
    }

    /**
     * @param string $a
     * @param string $b
     * @return bool
     */
    private function isSameIdentifier(string $a, string $b): bool
    {
        // ASCII-only case folding: mb_strtolower() would also fold e.g. the Kelvin sign (U+212A) to "k"
        return strcasecmp($a, $b) === 0;
    }

    /**
     * @param UserCredential $userCredentials
     *
     * @return User
     * @throws AuthenticationException
     */
    public function getUserForAuthenticate(UserCredential $userCredentials): User
    {
        $username = $userCredentials->getUsername();
        if ($username === null || $username === '') {
            throw AuthenticationException::noUserFound();
        }

        $users = $this->getSystemUsers($userCredentials);
        if (empty($users)) {
            throw AuthenticationException::noUserFound();
        }

        if (sizeof($users) > 1) {
            throw AuthenticationException::multipleUserReturned();
        }

        $user = $users[0];

        if (!$user instanceof User || $user->isDeleted()) {
            throw AuthenticationException::invalidCredentials();
        } elseif (!$this->isSameIdentifier($user->getUserName(), $username)) {
            // The database collation also ignores accents and trailing spaces
            throw AuthenticationException::noUserFound();
        } else {
            if (!$user->getStatus()) {
                throw AuthenticationException::userDisabled();
            } elseif ($user->getEmpNumber() === null) {
                throw AuthenticationException::employeeNotAssigned();
            } elseif ($user->getEmployee()->getEmployeeTerminationRecord() instanceof EmployeeTerminationRecord) {
                throw AuthenticationException::employeeTerminated();
            }
            return $user;
        }
    }

    /**
     * @param User $user
     * @param OpenIdProvider $provider
     *
     * @return OpenIdUserIdentity
     * @deprecated Not used for sign-in. Fresh installs key ohrm_openid_user_identity on user_id only, so a
     *             row per sign-in hits a duplicate key; a provider/subject link needs its own schema.
     */
    public function setOIDCUserIdentity(User $user, OpenIdProvider $provider): OpenIdUserIdentity
    {
        $openIdUserIdentity = new OpenIdUserIdentity();
        $openIdUserIdentity->setUser($user);
        $openIdUserIdentity->setOpenIdProvider($provider);

        return $this->getAuthProviderDao()->saveUserIdentity($openIdUserIdentity);
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws AuthenticationException
     */
    public function handleOIDCAuthentication(User $user): bool
    {
        return $this->getAuthenticationService()->setCredentialsForUser($user);
    }

    /**
     * @return bool
     */
    public function isSocialMediaAuthEnable(): bool
    {
        $providerSearchFilterParams = new ProviderSearchFilterParams();
        $providerSearchFilterParams->setName(null);
        $providerSearchFilterParams->setStatus(true);

        $count = $this->getAuthProviderDao()->getAuthProviderCount($providerSearchFilterParams);
        return $count > 0;
    }
}
