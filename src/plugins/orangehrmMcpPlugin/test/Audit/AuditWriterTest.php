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

namespace OrangeHRM\Tests\Mcp\Audit;

use OrangeHRM\Mcp\Audit\AuditWriter;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Audit
 */
class AuditWriterTest extends TestCase
{
    /**
     * Equal calls must correlate in the audit log regardless of the order the
     * client happened to serialise the arguments in.
     */
    public function testDigestIsStableAcrossKeyOrder(): void
    {
        $a = AuditWriter::digest(['b' => 2, 'a' => 1]);
        $b = AuditWriter::digest(['a' => 1, 'b' => 2]);

        $this->assertSame($a, $b);
        $this->assertSame(64, strlen($a), 'SHA-256 hex digest is 64 characters');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $a);
    }

    public function testNestedKeyOrderIsAlsoCanonicalised(): void
    {
        $a = AuditWriter::digest(['outer' => ['z' => 1, 'y' => 2]]);
        $b = AuditWriter::digest(['outer' => ['y' => 2, 'z' => 1]]);

        $this->assertSame($a, $b);
    }

    public function testDifferentValuesProduceDifferentDigests(): void
    {
        $this->assertNotSame(
            AuditWriter::digest(['name' => 'alice']),
            AuditWriter::digest(['name' => 'bob'])
        );
    }

    /**
     * NFR-S4: the digest must not be reversible to the raw parameter values.
     * A plain hash of a known value is the closest thing to a regression guard
     * here -- assert the digest never contains the input verbatim.
     */
    public function testDigestDoesNotLeakParameterValues(): void
    {
        $digest = AuditWriter::digest(['ssn' => '123-45-6789', 'name' => 'Rajitha']);

        $this->assertStringNotContainsString('123-45-6789', $digest);
        $this->assertStringNotContainsString('Rajitha', $digest);
    }

    public function testEmptyArgumentsStillProduceADigest(): void
    {
        $digest = AuditWriter::digest([]);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $digest);
        // hash of "[]" -- stable across runs, so empty-argument calls correlate.
        $this->assertSame(AuditWriter::digest([]), $digest);
    }
}
