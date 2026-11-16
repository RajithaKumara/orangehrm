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

namespace OrangeHRM\OpenidAuthentication\Exception;

use OrangeHRM\Authentication\Exception\AuthenticationException;

/**
 * Reasons an OpenID Connect sign-in is refused, shown on the login page.
 * Every reason up to `emailNotVerified()` is decided before a local user is looked up, so those messages
 * cannot tell whether an OrangeHRM account exists. Every refusal after the lookup uses `accountNotAvailable()`.
 */
class OpenIdConnectAuthenticationException extends AuthenticationException
{
    public const PROVIDER_NOT_AVAILABLE = 'openid_provider_not_available';
    public const SIGN_IN_FAILED = 'openid_sign_in_failed';
    public const INCONSISTENT_USER_DETAILS = 'openid_inconsistent_user_details';
    public const EMAIL_NOT_SHARED = 'openid_email_not_shared';
    public const EMAIL_NOT_VERIFIED = 'openid_email_not_verified';
    public const ACCOUNT_NOT_AVAILABLE = 'openid_account_not_available';

    /**
     * The provider is unknown, disabled, or the sign-in did not start from this session.
     */
    public static function providerNotAvailable(): self
    {
        return new self(
            self::PROVIDER_NOT_AVAILABLE,
            'This sign-in option is not available. Please contact your administrator.'
        );
    }

    /**
     * The exchange with the provider failed, e.g. the user cancelled, or the response could not be validated.
     */
    public static function signInFailed(): self
    {
        return new self(self::SIGN_IN_FAILED, 'Sign-in with the provider failed. Please try again.');
    }

    /**
     * The ID token and the userinfo response do not describe the same user.
     */
    public static function inconsistentUserDetails(): self
    {
        return new self(
            self::INCONSISTENT_USER_DETAILS,
            'The provider returned inconsistent user details. Please contact your administrator.'
        );
    }

    public static function emailNotShared(): self
    {
        return new self(
            self::EMAIL_NOT_SHARED,
            'The provider did not share an email address for your account. Please contact your administrator.'
        );
    }

    public static function emailNotVerified(): self
    {
        return new self(
            self::EMAIL_NOT_VERIFIED,
            'Your email address is not verified by the provider. Please verify it with the provider or contact your administrator.'
        );
    }

    /**
     * Any refusal after the local user lookup (no user, several users, deleted, disabled, no employee,
     * terminated). One message for all of them, so a sign-in attempt does not reveal whether an account exists.
     */
    public static function accountNotAvailable(): self
    {
        return new self(
            self::ACCOUNT_NOT_AVAILABLE,
            'Unable to sign in with this account. Please contact your administrator.'
        );
    }
}
