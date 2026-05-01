<?php

namespace OrangeHRM\Mcp\Server;

use OrangeHRM\Mcp\Tool\PimMyselfTool;

class ToolRegistry
{
    /** @var ToolInterface[] */
    private array $tools = [];

    public function __construct()
    {
        $this->register(new PimMyselfTool());
    }

    public function register(ToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /** @return ToolInterface[] */
    public function all(): array
    {
        return array_values($this->tools);
    }

    public function get(string $name): ?ToolInterface
    {
        return $this->tools[$name] ?? null;
    }
}
