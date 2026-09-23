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

namespace OrangeHRM\Tests\Mcp\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use OrangeHRM\Entity\McpApiPermission;
use OrangeHRM\Entity\McpPrompt;
use OrangeHRM\Mcp\Tool\ApiSensitivity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;
use Throwable;

/**
 * The mapping and the PHP declarations have to agree, and neither is checked by
 * anything else in the suite.
 *
 * Written after a column changed from a name to a level: the migration created
 * an INTEGER, the getter was retyped to int, and the annotation and property
 * were left as string. Nothing failed, because the only read path hydrates as an
 * array and casts. A single persist or a single hydrated read would have thrown.
 *
 * No database: the annotation driver is asked for the mapping directly, so this
 * runs anywhere the suite runs.
 *
 * @group Mcp
 * @group Entity
 */
class McpEntityMappingTest extends TestCase
{
    private const ENTITIES = [McpApiPermission::class, McpPrompt::class];

    private function metadataFor(string $class): ClassMetadata
    {
        $config = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/../../entity'], true);
        $metadata = new ClassMetadata($class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $config->getMetadataDriverImpl()->loadMetadataForClass($class, $metadata);

        return $metadata;
    }

    /**
     * Doctrine type versus declared property type, field by field. A mismatch
     * here is what writes the wrong thing into the column.
     */
    public function testEveryMappedFieldAgreesWithItsPropertyType(): void
    {
        $expected = ['integer' => 'int', 'bigint' => 'int', 'string' => 'string', 'text' => 'string', 'boolean' => 'bool'];

        foreach (self::ENTITIES as $class) {
            $metadata = $this->metadataFor($class);
            $reflection = new ReflectionClass($class);

            foreach ($metadata->getFieldNames() as $field) {
                $mapping = $metadata->getFieldMapping($field);
                if (!isset($expected[$mapping['type']])) {
                    // datetime and friends map to objects; not what this guards.
                    continue;
                }

                $property = $reflection->getProperty($field);
                $type = $property->getType();
                if (!$type instanceof ReflectionNamedType) {
                    continue;
                }

                $this->assertSame(
                    $expected[$mapping['type']],
                    $type->getName(),
                    sprintf('%s::$%s is %s but mapped as %s', $class, $field, $type->getName(), $mapping['type'])
                );
            }
        }
    }

    /**
     * Sensitivity is a level, and the level it starts at is the one the
     * migration writes as the column default.
     */
    public function testSensitivityIsAnIntegerLevel(): void
    {
        $mapping = $this->metadataFor(McpApiPermission::class)->getFieldMapping('sensitivity');

        $this->assertSame('integer', $mapping['type']);
        $this->assertSame(ApiSensitivity::NORMAL, $mapping['options']['default'] ?? null);
        $this->assertSame(ApiSensitivity::NORMAL, (new McpApiPermission())->getSensitivity());
    }

    /**
     * A field that starts with a default must be readable through its accessor.
     * This is the cheap check that catches a default the getter's return type
     * rejects -- the exact shape of the bug above.
     *
     * Fields with no default are skipped: a required column is legitimately
     * uninitialized until something sets it, and reading it early is a caller
     * error rather than a mapping one.
     */
    public function testDefaultsSatisfyTheirOwnGetters(): void
    {
        $checked = 0;

        foreach (self::ENTITIES as $class) {
            $entity = new $class();
            $reflection = new ReflectionClass($class);

            foreach ($this->metadataFor($class)->getFieldNames() as $field) {
                $property = $reflection->getProperty($field);
                $property->setAccessible(true);
                if (!$property->isInitialized($entity)) {
                    continue;
                }

                foreach (['get', 'is', 'can'] as $prefix) {
                    $name = $prefix . ucfirst($field);
                    if (!$reflection->hasMethod($name)) {
                        continue;
                    }
                    try {
                        $reflection->getMethod($name)->invoke($entity);
                        $checked++;
                    } catch (Throwable $e) {
                        $this->fail(sprintf('%s::%s() on a new entity: %s', $class, $name, $e->getMessage()));
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $checked, 'no defaulted field was exercised, so this proved nothing');
    }
}
