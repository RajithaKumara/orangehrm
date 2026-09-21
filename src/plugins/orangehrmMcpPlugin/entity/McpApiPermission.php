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

namespace OrangeHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Which verbs MCP may use against one API class, and how sensitive that class's
 * data is.
 *
 * Modelled on `ohrm_api_permission`, which maps an API class to a data group,
 * but answering a different question and owned by a different party. That table
 * is core's: it says which *role* may read or write an endpoint, and MCP has no
 * business editing it. This one says whether the AI channel may use the endpoint
 * at all, for an administrator who has already decided who may connect.
 *
 * Both checks run, and both must pass. A row here can only narrow: enabling
 * `can_delete` for an API class does not let anyone delete anything their own
 * role could not already delete through the web UI.
 *
 * Keyed by the API class rather than by route or tool name on purpose. A class
 * is the unit OrangeHRM already authorises against, it survives a route rename,
 * and it means a new endpoint added to an existing family inherits the
 * administrator's decision instead of appearing unreviewed.
 *
 * @ORM\Table(name="ohrm_mcp_api_permission")
 * @ORM\Entity
 */
class McpApiPermission
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * Fully qualified API class name, e.g. `OrangeHRM\Pim\Api\EmployeeAPI`.
     *
     * @var string
     *
     * @ORM\Column(name="api_name", type="string", length=255, unique=true)
     */
    private string $apiName;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_read", type="boolean", options={"default" : 0})
     */
    private bool $canRead = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_create", type="boolean", options={"default" : 0})
     */
    private bool $canCreate = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_update", type="boolean", options={"default" : 0})
     */
    private bool $canUpdate = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_delete", type="boolean", options={"default" : 0})
     */
    private bool $canDelete = false;

    /**
     * `normal` or `sensitive`; see ApiSensitivity.
     *
     * Product-authored and seeded by the migration. Nothing in the UI writes it,
     * because a warning an administrator can turn off is not a warning.
     *
     * @var string
     *
     * @ORM\Column(name="sensitivity", type="string", length=20, options={"default" : "normal"})
     */
    private string $sensitivity = 'normal';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getApiName(): string
    {
        return $this->apiName;
    }

    public function setApiName(string $apiName): void
    {
        $this->apiName = $apiName;
    }

    public function canRead(): bool
    {
        return $this->canRead;
    }

    public function setCanRead(bool $canRead): void
    {
        $this->canRead = $canRead;
    }

    public function canCreate(): bool
    {
        return $this->canCreate;
    }

    public function setCanCreate(bool $canCreate): void
    {
        $this->canCreate = $canCreate;
    }

    public function canUpdate(): bool
    {
        return $this->canUpdate;
    }

    public function setCanUpdate(bool $canUpdate): void
    {
        $this->canUpdate = $canUpdate;
    }

    public function canDelete(): bool
    {
        return $this->canDelete;
    }

    public function setCanDelete(bool $canDelete): void
    {
        $this->canDelete = $canDelete;
    }

    public function getSensitivity(): string
    {
        return $this->sensitivity;
    }

    public function setSensitivity(string $sensitivity): void
    {
        $this->sensitivity = $sensitivity;
    }
}
