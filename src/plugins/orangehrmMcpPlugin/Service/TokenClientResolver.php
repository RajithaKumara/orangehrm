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

namespace OrangeHRM\Mcp\Service;

use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Entity\OAuthClient;
use OrangeHRM\OAuth\Traits\OAuthServiceTrait;

/**
 * Which OAuth client presented the bearer token on the current request.
 *
 * The MCP specification requires a server to refuse an access token that was not
 * issued for it, so that a token a user granted to one application cannot be
 * replayed against the AI surface (README 4.13).
 *
 * The fact itself is established during authentication: OAuthSubscriber validates
 * the token and publishes the issuing client on AuthUser, beside the user and the
 * role. This only translates the stored row id into the `client_id` an
 * administrator recognises, which is what the approved list holds and what the
 * audit trail records.
 *
 * Returns null whenever no OAuth client authenticated the request -- a session
 * login, or a client row deleted since the token was issued. Callers must treat
 * null as "unknown", never as "approved".
 */
class TokenClientResolver
{
    use AuthUserTrait;
    use OAuthServiceTrait;

    /**
     * Client id string (`ohrm_oauth2_client.client_id`), not the numeric row id.
     */
    public function resolveClientId(): ?string
    {
        $rowId = $this->getAuthUser()->getAttribute(AuthUser::OAUTH_CLIENT_ID);
        if (!is_int($rowId) && !(is_string($rowId) && $rowId !== '')) {
            return null;
        }

        $client = $this->getOAuthService()->getOAuthClientDao()->getOAuthClientById((int) $rowId);

        return $client instanceof OAuthClient ? $client->getClientId() : null;
    }
}
