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

use League\OAuth2\Server\CryptTrait;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Framework\Http\Request;
use Throwable;

/**
 * Resolves which OAuth client issued the bearer token on the current request.
 *
 * The MCP specification requires a server to reject access tokens that were not
 * issued for it. OrangeHRM's OAuthSubscriber validates the token and resolves its
 * user, then discards the token entity, so the issuing client is not available
 * downstream -- which is why a token minted for the mobile app was accepted on
 * the MCP endpoint (README 4.13).
 *
 * Rather than change the shared OAuth plugin, this re-resolves the token the same
 * way BearerTokenValidator does: strip the `Bearer` prefix, decrypt with the
 * OAuth token encryption key to recover the token identifier, then read the
 * client that identifier was issued to.
 *
 * Returns null whenever the client cannot be determined. Callers must treat null
 * as "unknown", never as "approved".
 */
class TokenClientResolver
{
    use CryptTrait;
    use ConfigServiceTrait;
    use EntityManagerHelperTrait;

    /**
     * Client id string (`ohrm_oauth2_client.client_id`), not the numeric row id.
     */
    public function resolveClientId(Request $request): ?string
    {
        $header = $request->headers->get('Authorization');
        if (!is_string($header) || $header === '') {
            return null;
        }

        $encrypted = trim((string) preg_replace('/^\s*Bearer\s/i', '', $header));
        if ($encrypted === '') {
            return null;
        }

        try {
            $this->setEncryptionKey($this->getConfigService()->getOAuthTokenEncryptionKey());
            $tokenId = $this->decrypt($encrypted);
        } catch (Throwable $e) {
            // A token we cannot decrypt is not one we can vouch for.
            return null;
        }

        if (!is_string($tokenId) || $tokenId === '') {
            return null;
        }

        try {
            $clientId = $this->getEntityManager()->getConnection()->fetchOne(
                'SELECT c.client_id
                   FROM ohrm_oauth2_access_token t
                   JOIN ohrm_oauth2_client c ON c.id = t.client_id
                  WHERE t.access_token = :token',
                ['token' => $tokenId]
            );
        } catch (Throwable $e) {
            return null;
        }

        return is_string($clientId) && $clientId !== '' ? $clientId : null;
    }
}
