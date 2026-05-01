<?php

namespace OrangeHRM\Mcp\Server;

interface ToolInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * JSON Schema describing arguments accepted by the tool.
     */
    public function getInputSchema(): array;

    /**
     * Execute the tool with the given arguments.
     * Return value is encoded as JSON text content in the MCP response.
     */
    public function execute(array $arguments): array;
}
