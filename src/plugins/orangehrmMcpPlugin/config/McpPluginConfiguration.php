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

use OrangeHRM\Core\Traits\EventDispatcherTrait;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Framework\Console\Console;
use OrangeHRM\Framework\Console\ConsoleConfigurationInterface;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\PluginConfigurationInterface;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Audit\AuditWriter;
use OrangeHRM\Mcp\Command\McpDoctorCommand;
use OrangeHRM\Mcp\JsonRpc\Dispatcher;
use OrangeHRM\Mcp\Method\DiscoverToolsHandler;
use OrangeHRM\Mcp\Method\InitializeHandler;
use OrangeHRM\Mcp\Method\PingHandler;
use OrangeHRM\Mcp\Method\ToolsCallHandler;
use OrangeHRM\Mcp\Method\ToolsListHandler;
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Service\McpApiPermissionService;
use OrangeHRM\Mcp\Service\McpConfigurationService;
use OrangeHRM\Mcp\Service\McpOperatorLogger;
use OrangeHRM\Mcp\Service\TokenClientResolver;
use OrangeHRM\Mcp\Session\SessionContext;
use OrangeHRM\Mcp\Subscriber\McpModuleStatusChangeSubscriber;
use OrangeHRM\Mcp\Tool\Curated\CurrentEmployeeTool;
use OrangeHRM\Mcp\Tool\Curated\ListEmployeesTool;
use OrangeHRM\Mcp\Tool\Curated\ListJobTitlesTool;
use OrangeHRM\Mcp\Tool\Curated\ListLeaveTypesTool;
use OrangeHRM\Mcp\Tool\Rest\GenericToolFactory;
use OrangeHRM\Mcp\Tool\Rest\OpenApiLoader;
use OrangeHRM\Mcp\Tool\Rest\RouteMap;
use OrangeHRM\Mcp\Tool\RestDispatcher;
use OrangeHRM\Mcp\Tool\ToolPolicyGate;
use OrangeHRM\Mcp\Tool\ToolRegistry;

class McpPluginConfiguration implements PluginConfigurationInterface, ConsoleConfigurationInterface
{
    use ServiceContainerTrait;
    use EventDispatcherTrait;

    /**
     * @inheritDoc
     */
    public function initialize(Request $request): void
    {
        $this->getContainer()->register(
            Services::MCP_CONFIG_SERVICE,
            McpConfigurationService::class
        );

        $this->getContainer()->register(
            Services::MCP_OPERATOR_LOGGER,
            McpOperatorLogger::class
        );

        // Session identity is per-request; Streamable HTTP is one HTTP request
        // per JSON-RPC message, so a shared instance is request-scoped already.
        $this->getContainer()->register(Services::MCP_SESSION_CONTEXT, SessionContext::class);

        $this->getContainer()->register(Services::MCP_REST_DISPATCHER, RestDispatcher::class);
        $this->getContainer()->register(Services::MCP_ACCESS_POLICY, McpAccessPolicy::class);
        $this->getContainer()->register(Services::MCP_TOKEN_CLIENT_RESOLVER, TokenClientResolver::class);
        $this->getContainer()->register(Services::MCP_API_PERMISSION_SERVICE, McpApiPermissionService::class);
        $this->getContainer()->register(Services::MCP_AUDIT_WRITER, AuditWriter::class);

        // Curated read-only tool catalog. Adding a tool is a registration here,
        // never an edit to the protocol code.
        $registry = new ToolRegistry([
            new CurrentEmployeeTool(),
            new ListEmployeesTool(),
            new ListJobTitlesTool(),
            new ListLeaveTypesTool(),
        ]);
        $this->getContainer()->set(Services::MCP_TOOL_REGISTRY, $registry);

        $restDispatcher = new RestDispatcher();
        $accessPolicy = new McpAccessPolicy();
        $apiPermissions = $this->getContainer()->get(Services::MCP_API_PERMISSION_SERVICE);

        // The generic surface is always attached: every non-restricted endpoint
        // is *available*, and the per-class permission rows decide which are
        // actually offered. Registry merges it behind the curated tools, which
        // win on name.
        $registry->setGenericFactory(new GenericToolFactory(new RouteMap(), new OpenApiLoader()));

        // One gate for the whole request. It reads the permission table once,
        // and every handler asks the same instance, so a tools/list over several
        // hundred tools is one query rather than several hundred.
        $gate = new ToolPolicyGate($accessPolicy, $apiPermissions);

        $discovery = new DiscoverToolsHandler($registry, $restDispatcher, $gate);

        $dispatcher = new Dispatcher([
            new InitializeHandler(),
            new PingHandler(),
            new ToolsListHandler($registry, $restDispatcher, $gate, $apiPermissions, $discovery),
            new ToolsCallHandler(
                $registry,
                $restDispatcher,
                new AuditWriter(),
                $this->getContainer()->get(Services::MCP_SESSION_CONTEXT),
                $gate,
                $discovery
            ),
        ]);
        $this->getContainer()->set(Services::MCP_DISPATCHER, $dispatcher);

        $this->getEventDispatcher()->addSubscriber(new McpModuleStatusChangeSubscriber());
    }

    /**
     * @inheritDoc
     */
    public function registerCommands(Console $console): void
    {
        $console->add(new McpDoctorCommand());
    }
}
