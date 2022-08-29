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

namespace OrangeHRM\Tests\LDAP\Service;

use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Framework\Services;
use OrangeHRM\LDAP\Dto\LDAPSetting;
use OrangeHRM\LDAP\Service\LDAPService;
use OrangeHRM\Tests\LDAP\LDAPConnectionHelperTrait;
use OrangeHRM\Tests\Util\KernelTestCase;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\LdapException;

class LDAPServiceTest extends KernelTestCase
{
    use LDAPConnectionHelperTrait;

    protected function setUp(): void
    {
        if (!$this->isLDAPServerConfigured()) {
            $this->markTestSkipped('Configure LDAP server config: ' . $this->getLDAPServerConfigFilePath());
        }
    }

    public function testAnonymousConnection(): void
    {
        $configService = $this->getMockBuilder(ConfigService::class)
            ->onlyMethods(['getLDAPSetting'])
            ->getMock();
        $serverConfig = $this->getLDAPServerConfig();
        $configService->expects($this->once())
            ->method('getLDAPSetting')
            ->willReturn(
                new LDAPSetting($serverConfig->host, $serverConfig->port, 'OpenLDAP', $serverConfig->encryption)
            );
        $this->createKernelWithMockServices([Services::CONFIG_SERVICE => $configService]);

        $credential = new UserCredential();
        $ldapAuthService = new LDAPService();
        $ldapAuthService->bind($credential);
        $entry = new Entry('cn=config', [
            'olcDisallows' => ['bind_anon'],
        ]);
        try {
            $ldapAuthService->getEntryManager()->add($entry);
        } catch (LdapException $e) {
            $this->assertEquals(
                'Could not add entry "cn=config": Strong(er) authentication required',
                $e->getMessage()
            );
        }

        $ldapAuthService->bind(new UserCredential($serverConfig->adminDN, $serverConfig->adminPassword));
        $entry = new Entry('cn=config', [
            'olcDisallows' => ['bind_anon'],
        ]);
        try {
            $ldapAuthService->getEntryManager()->add($entry);
        } catch (LdapException $e) {
            $this->assertEquals('Could not add entry "cn=config": Insufficient access', $e->getMessage());
        }

        $ldapAuthService->bind(new UserCredential($serverConfig->configAdminDN, $serverConfig->configAdminPassword));
        $query = $ldapAuthService->query('cn=config', 'cn=config');

        $entry = $query->execute()->toArray()[0];
//        var_dump($entry);
//        $entry->setAttribute('olcDisallows', ['bind_anon']);

        $entry = new Entry('cn=config', [
            'olcDisallows' => ['bind_anon'],
        ]);
        $ldapAuthService->getEntryManager()->update($entry);

//        ldap_connect()
//        ldap_mod_replace($ldap->getResource(), )
        $this->assertTrue(true);
    }
}
