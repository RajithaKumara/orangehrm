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
 * One MCP prompt: a phrasing an assistant can offer a user, stored as data.
 *
 * Deliberately a table rather than PHP classes. A prompt is a piece of wording
 * whose value is only known once real people use it, and wording that can only
 * change in a release will not change. Here a customer -- or support, on their
 * behalf -- can edit one, disable one, or add their own with a few statements
 * and no deployment.
 *
 * The template is plain text with `{{argument}}` placeholders, for the same
 * reason: it has to be legible and editable in a SQL client by someone who is
 * not a PHP developer.
 *
 * @ORM\Table(name="ohrm_mcp_prompt")
 * @ORM\Entity
 */
class McpPrompt
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
     * The protocol identifier a client passes back to `prompts/get`. Stable:
     * renaming one breaks any client that saved it.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, unique=true)
     */
    private string $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=200, nullable=true)
     */
    private ?string $title = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    private ?string $description = null;

    /**
     * JSON array of `{name, description, required}`, matching what
     * `prompts/list` publishes. Null or invalid JSON means no arguments, so a
     * hand-edited row with a typo degrades to a prompt that still works rather
     * than a broken listing.
     *
     * @var string|null
     *
     * @ORM\Column(name="arguments", type="text", nullable=true)
     */
    private ?string $arguments = null;

    /**
     * The message text, with `{{argument}}` placeholders.
     *
     * @var string
     *
     * @ORM\Column(name="template", type="text")
     */
    private string $template;

    /**
     * Switched off rather than deleted keeps a customer's edits recoverable,
     * and keeps the name reserved.
     *
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean", options={"default" : 1})
     */
    private bool $enabled = true;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(?string $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
