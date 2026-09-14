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

namespace OrangeHRM\Mcp\JsonRpc;

/**
 * JSON-RPC 2.0 reserved codes plus the MCP-specific codes this server uses.
 * Registry documented in docs/mcp/error-codes.md.
 */
final class ErrorCode
{
    // JSON-RPC 2.0 reserved range
    public const PARSE_ERROR = -32700;
    public const INVALID_REQUEST = -32600;
    public const METHOD_NOT_FOUND = -32601;
    public const INVALID_PARAMS = -32602;
    public const INTERNAL_ERROR = -32603;

    // MCP / OrangeHRM specific
    public const MODULE_DISABLED = -32000;
    public const UNAUTHENTICATED = -32001;
    public const UNSUPPORTED_PROTOCOL_VERSION = -32002;
    public const READ_ONLY_MODE = -32011;
    public const AUDIT_WRITE_FAILED = -32010;
    public const INSECURE_TRANSPORT = -32099;

    /**
     * Error categories carried in `error.data.category` so an AI client can
     * decide whether a retry, a different argument, or giving up is right.
     */
    public const CATEGORY_VALIDATION = 'validation';
    public const CATEGORY_AUTHORIZATION = 'authorization';
    public const CATEGORY_BUSINESS_RULE = 'business_rule';
    public const CATEGORY_INTERNAL = 'internal';
    public const CATEGORY_READ_ONLY = 'read_only';
    public const CATEGORY_AUDIT_WRITE_FAILED = 'audit_write_failed';
    public const CATEGORY_NOT_FOUND = 'not_found';
}
