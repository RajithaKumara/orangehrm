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

namespace OrangeHRM\OAuth\Traits;

use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

/**
 * Tells apart "a person is signed in here" from "a token was presented".
 *
 * Consent is the act that *creates* tokens, so reaching it has to be proof that
 * someone clicked, not proof that a token already exists. OAuthSubscriber
 * authenticates a bearer on every route, this one included, so without this
 * distinction an access token could authorise the minting of another token --
 * for any registered client, with the code readable straight off the redirect,
 * and no consent screen ever drawn.
 *
 * `AuthUser::OAUTH_CLIENT_ID` is set only where OAuthSubscriber validated a
 * token, so its presence means exactly "this request arrived as an API call".
 * The browser flow never sets it: an unauthenticated visitor to
 * `/oauth2/authorize` is forwarded to the login page, and a browser or webview
 * following that journey carries cookies, not an Authorization header. The
 * token does not exist yet -- obtaining it is what the journey is for.
 */
trait ConsentGuardTrait
{
    use AuthUserTrait;

    protected function isAuthenticatedByAccessToken(): bool
    {
        return $this->getAuthUser()->getAttribute(AuthUser::OAUTH_CLIENT_ID) !== null;
    }
}
