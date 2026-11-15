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

namespace OrangeHRM\Tests\LDAP\Service;

use DateTime;
use OrangeHRM\Admin\Service\UserService;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Service\DateTimeHelperService;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\User;
use OrangeHRM\Entity\UserAuthProvider;
use OrangeHRM\Framework\Services;
use OrangeHRM\LDAP\Dto\LDAPUser;
use OrangeHRM\LDAP\Dto\LDAPUserCollection;
use OrangeHRM\LDAP\Dto\LDAPUserLookupSetting;
use OrangeHRM\LDAP\Service\LDAPSyncService;
use OrangeHRM\Pim\Service\EmployeeService;
use OrangeHRM\Tests\Util\KernelTestCase;
use OrangeHRM\Tests\Util\Mock\MockLogger;
use OrangeHRM\Tests\Util\TestDataService;
use Symfony\Component\Ldap\Entry;

/**
 * A local user stays bound to its own LDAP entry: another entry that only shares the
 * username or the user unique id must not take the user over.
 *
 * Fixture: user 8 `Linda` (Admin role, employee 2) is bound to
 * `cn=Linda.Anderson,ou=admin,ou=users,dc=example,dc=org` with unique id md5('Linda.Anderson').
 *
 * @group Admin
 * @group LDAP
 * @group Service
 */
class LDAPSyncServiceUserBindingTest extends KernelTestCase
{
    private const BOUND_DN = 'cn=Linda.Anderson,ou=admin,ou=users,dc=example,dc=org';
    private const OTHER_DN = 'cn=Evil.User,ou=sales,ou=users,dc=example,dc=org';

    private MockLogger $logger;

    protected function setUp(): void
    {
        $dateTimeHelper = $this->getMockBuilder(DateTimeHelperService::class)
            ->onlyMethods(['getNow'])
            ->getMock();
        $dateTimeHelper->method('getNow')
            ->willReturnCallback(fn () => new DateTime('2022-09-07 08:30'));
        $this->logger = new MockLogger();

        $this->createKernelWithMockServices([
            Services::DATETIME_HELPER_SERVICE => $dateTimeHelper,
            Services::USER_SERVICE => new UserService(),
            Services::EMPLOYEE_SERVICE => new EmployeeService(),
            Services::LDAP_LOGGER => $this->logger,
        ]);
        TestDataService::populate(
            Config::get(Config::PLUGINS_DIR) . '/orangehrmLDAPAuthenticationPlugin/test/fixtures/LDAPSyncUsers_4.yaml'
        );
    }

    private function getBoundUserUniqueId(): string
    {
        return md5('Linda.Anderson');
    }

    private function createLDAPUser(
        string $username,
        string $dn,
        ?string $userUniqueId,
        string $userUniqueIdAttribute = 'employeeNumber'
    ): LDAPUser {
        $lookupSetting = LDAPUserLookupSetting::createFromArray([
            'baseDN' => 'ou=users,dc=example,dc=org',
            'userUniqueIdAttribute' => $userUniqueIdAttribute,
            'employeeSelectorMapping' => [],
        ]);
        $names = explode('.', $username);
        $attributes = ['cn' => [$username], 'givenName' => [$names[0]], 'sn' => [$names[1] ?? $names[0]]];
        if ($userUniqueId !== null) {
            $attributes[$userUniqueIdAttribute] = [$userUniqueId];
        }
        $entry = new Entry($dn, $attributes);
        return (new LDAPUser())
            ->setUserDN($dn)
            ->setUsername($username)
            ->setUserUniqueId($userUniqueId)
            ->setFirstName($names[0])
            ->setLastName($names[1] ?? $names[0])
            ->setUserLookupSetting($lookupSetting)
            ->setEntry($entry);
    }

    /**
     * @param LDAPUser[] $ldapUsers
     * @param string[] $failedUserDNs
     * @param LDAPUser[] $usersOfDuplicateUsername
     */
    private function createCollection(
        array $ldapUsers,
        array $failedUserDNs = [],
        array $usersOfDuplicateUsername = []
    ): LDAPUserCollection {
        $ldapUsersByUsername = [];
        foreach ($ldapUsers as $ldapUser) {
            $ldapUsersByUsername[$ldapUser->getUsername()] = $ldapUser;
        }
        $duplicates = [];
        foreach ($usersOfDuplicateUsername as $ldapUser) {
            $duplicates[$ldapUser->getUsername()][] = $ldapUser;
        }
        return new LDAPUserCollection($ldapUsersByUsername, array_keys($duplicates), $duplicates, $failedUserDNs);
    }

    private function assertBoundUserUnchanged(string $username = 'Linda'): void
    {
        $this->getEntityManager()->clear();
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->find(8);
        $this->assertEquals($username, $user->getUserName());
        $this->assertEquals(1, $user->getUserRole()->getId());
        $this->assertEquals(2, $user->getEmployee()->getEmpNumber());
        $this->assertEquals('Linda', $user->getEmployee()->getFirstName());
        $authProvider = $this->getEntityManager()->getRepository(UserAuthProvider::class)->find(1);
        $this->assertEquals(self::BOUND_DN, $authProvider->getLDAPUserDN());
        $this->assertEquals($this->getBoundUserUniqueId(), $authProvider->getLDAPUserUniqueId());
        $this->assertNull($this->getEntityManager()->getRepository(User::class)->findOneBy(['userName' => 'Evil.User']));
        $this->assertNull($this->getEntityManager()->getRepository(Employee::class)->findOneBy(['firstName' => 'Evil']));
    }

    private function assertWarningLogged(string $message): void
    {
        $this->assertTrue(
            $this->logger->getTestHandler()->hasWarningThatContains($message),
            "Expected warning: $message"
        );
    }

    /**
     * Report scenario: the bound entry is still in the directory and a second entry carries its unique id.
     */
    public function testUniqueIdSharedWithBoundEntryDoesNotRebind(): void
    {
        $bound = $this->createLDAPUser('Linda', self::BOUND_DN, $this->getBoundUserUniqueId());
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, $this->getBoundUserUniqueId());

        (new LDAPSyncService())->createSystemUsers([$other, $bound], $this->createCollection([$other, $bound]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('User unique id is shared by more than one LDAP entry, skipped');
    }

    public function testUniqueIdSharedWithBoundEntryDoesNotRebindInEitherOrder(): void
    {
        $bound = $this->createLDAPUser('Linda', self::BOUND_DN, $this->getBoundUserUniqueId());
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, $this->getBoundUserUniqueId());

        (new LDAPSyncService())->createSystemUsers([$bound, $other], $this->createCollection([$bound, $other]));

        $this->assertBoundUserUnchanged();
    }

    public function testUniqueIdMatchDoesNotRebindWhenBoundEntryStillInDirectory(): void
    {
        // Bound entry failed to parse in this sync (or was dropped as a duplicate username),
        // so only its DN is known, but it is still in the directory.
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, $this->getBoundUserUniqueId(), 'entryUUID');

        (new LDAPSyncService())->createSystemUsers([$other], $this->createCollection([$other], [self::BOUND_DN]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('User unique id matches a user bound to a different LDAP entry, skipped');
    }

    public function testUniqueIdMatchDoesNotRebindWhenBoundEntryHiddenByDuplicateUsername(): void
    {
        // The bound entry is dropped from the user list as a duplicate username, but its DN is still known
        $bound = $this->createLDAPUser('Linda', self::BOUND_DN, null, 'entryUUID');
        $decoy = $this->createLDAPUser('Linda', 'cn=Linda,ou=sales,ou=users,dc=example,dc=org', null, 'entryUUID');
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, $this->getBoundUserUniqueId(), 'entryUUID');

        (new LDAPSyncService())->createSystemUsers(
            [$other],
            $this->createCollection([$other], [], [$bound, $decoy])
        );

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('User unique id matches a user bound to a different LDAP entry, skipped');
    }

    public function testUniqueIdDifferingOnlyInCaseDoesNotRebind(): void
    {
        // The database matches unique ids case-insensitively; the DN check still refuses the other entry
        $bound = $this->createLDAPUser('Linda', self::BOUND_DN, $this->getBoundUserUniqueId());
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, strtoupper($this->getBoundUserUniqueId()));

        (new LDAPSyncService())->createSystemUsers([$other, $bound], $this->createCollection([$other, $bound]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('User unique id matches a user bound to a different LDAP entry, skipped');
    }

    public function testUniqueIdMatchFromReassignableAttributeDoesNotRebindWhenBoundEntryGone(): void
    {
        // e.g. the bound entry was removed and its employeeNumber now appears on another entry
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, $this->getBoundUserUniqueId(), 'employeeNumber');

        (new LDAPSyncService())->createSystemUsers([$other], $this->createCollection([$other]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('User unique id matches a user bound to a different LDAP entry, skipped');
    }

    public function testUniqueIdMatchWithSameDNUpdatesUsername(): void
    {
        $bound = $this->createLDAPUser('Linda.Anderson', self::BOUND_DN, $this->getBoundUserUniqueId());

        (new LDAPSyncService())->createSystemUsers([$bound], $this->createCollection([$bound]));

        $this->assertBoundUserUnchanged('Linda.Anderson');
    }

    public function testUniqueIdMatchFollowsRenamedEntryForServerAssignedId(): void
    {
        $renamedDN = 'cn=Linda.Smith,ou=finance,ou=users,dc=example,dc=org';
        $renamed = $this->createLDAPUser('Linda.Smith', $renamedDN, $this->getBoundUserUniqueId(), 'entryUUID');

        (new LDAPSyncService())->createSystemUsers([$renamed], $this->createCollection([$renamed]));

        $this->getEntityManager()->clear();
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->find(8);
        $this->assertEquals('Linda.Smith', $user->getUserName());
        $this->assertEquals(1, $user->getUserRole()->getId());
        $this->assertEquals('Smith', $user->getEmployee()->getLastName());
        $authProvider = $this->getEntityManager()->getRepository(UserAuthProvider::class)->find(1);
        $this->assertEquals($renamedDN, $authProvider->getLDAPUserDN());
    }

    public function testUniqueIdMatchFollowsRenamedEntryForObjectGUID(): void
    {
        $renamedDN = 'CN=Linda.Smith,OU=Finance,DC=example,DC=org';
        $renamed = $this->createLDAPUser('Linda.Smith', $renamedDN, $this->getBoundUserUniqueId(), 'objectGUID');

        (new LDAPSyncService())->createSystemUsers([$renamed]);

        $this->getEntityManager()->clear();
        $authProvider = $this->getEntityManager()->getRepository(UserAuthProvider::class)->find(1);
        $this->assertEquals($renamedDN, $authProvider->getLDAPUserDN());
    }

    public function testUsernameMatchFromDifferentEntryDoesNotRebindWhenBoundEntryStillInDirectory(): void
    {
        // Same username (case-insensitive in the database) on a different entry
        $bound = $this->createLDAPUser('Linda.Anderson', self::BOUND_DN, $this->getBoundUserUniqueId());
        $other = $this->createLDAPUser('LINDA', self::OTHER_DN, '9999');

        (new LDAPSyncService())->createSystemUsers([$other], $this->createCollection([$other, $bound]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('Username matches a user bound to a different LDAP entry, skipped');
    }

    public function testUsernameMatchWithConflictingUniqueIdDoesNotRebind(): void
    {
        $other = $this->createLDAPUser('LINDA', self::OTHER_DN, '9999');

        (new LDAPSyncService())->createSystemUsers([$other], $this->createCollection([$other]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('Username matches a user bound to a different LDAP entry, skipped');
    }

    public function testUsernameMatchFollowsMovedEntry(): void
    {
        $movedDN = 'cn=Linda,ou=finance,ou=users,dc=example,dc=org';
        $moved = $this->createLDAPUser('Linda', $movedDN, $this->getBoundUserUniqueId());

        (new LDAPSyncService())->createSystemUsers([$moved], $this->createCollection([$moved]));

        $this->getEntityManager()->clear();
        $authProvider = $this->getEntityManager()->getRepository(UserAuthProvider::class)->find(1);
        $this->assertEquals($movedDN, $authProvider->getLDAPUserDN());
        $this->assertEquals(1, $authProvider->getUser()->getUserRole()->getId());
    }

    public function testUsernameMatchKeepsStoredUniqueIdWhenShared(): void
    {
        // Another provisioned entry changed its unique id to the bound user's value
        $bound = $this->createLDAPUser('Linda', self::BOUND_DN, '1111');
        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, '1111');

        (new LDAPSyncService())->createSystemUsers([$bound, $other], $this->createCollection([$bound, $other]));

        $this->getEntityManager()->clear();
        $authProvider = $this->getEntityManager()->getRepository(UserAuthProvider::class)->find(1);
        $this->assertEquals(self::BOUND_DN, $authProvider->getLDAPUserDN());
        $this->assertEquals($this->getBoundUserUniqueId(), $authProvider->getLDAPUserUniqueId());
        $this->assertNull($this->getEntityManager()->getRepository(User::class)->findOneBy(['userName' => 'Evil.User']));
        $this->assertWarningLogged('User unique id is shared by more than one LDAP entry, stored unique id kept');
    }

    public function testDeleteLocalUsersMatchesDNCaseInsensitively(): void
    {
        $bound = $this->createLDAPUser('Linda', strtoupper(self::BOUND_DN), $this->getBoundUserUniqueId());
        (new LDAPSyncService())->deleteLocalUsersWhoRemovedFromLDAPServer($this->createCollection([$bound]));
        $this->getEntityManager()->clear();
        $this->assertFalse($this->getEntityManager()->getRepository(User::class)->find(8)->isDeleted());

        (new LDAPSyncService())->deleteLocalUsersWhoRemovedFromLDAPServer($this->createCollection([]));
        $this->getEntityManager()->clear();
        $this->assertTrue($this->getEntityManager()->getRepository(User::class)->find(8)->isDeleted());
    }

    public function testUniqueIdBoundToManyUsersIsSkipped(): void
    {
        $authProvider = new UserAuthProvider();
        $authProvider->setUser($this->getEntityManager()->getRepository(User::class)->find(9));
        $authProvider->setType(UserAuthProvider::TYPE_LDAP);
        $authProvider->setLDAPUserDN('cn=Jane,ou=admin,ou=users,dc=example,dc=org');
        $authProvider->setLDAPUserUniqueId($this->getBoundUserUniqueId());
        $this->getEntityManager()->persist($authProvider);
        $this->getEntityManager()->flush();

        $other = $this->createLDAPUser('Evil.User', self::OTHER_DN, $this->getBoundUserUniqueId(), 'entryUUID');
        (new LDAPSyncService())->createSystemUsers([$other], $this->createCollection([$other]));

        $this->assertBoundUserUnchanged();
        $this->assertWarningLogged('User unique id is bound to more than one user, skipped');
    }
}
