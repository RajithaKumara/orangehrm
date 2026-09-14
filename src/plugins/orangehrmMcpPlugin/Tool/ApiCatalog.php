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

namespace OrangeHRM\Mcp\Tool;

use OrangeHRM\Mcp\Tool\Rest\RouteMap;

/**
 * The MCP surface as an administrator needs to see it: grouped by module, one
 * row per API class, with the verbs that class actually exposes.
 *
 * Built from the live route collection rather than from the seeded table, so the
 * screen shows what this build can do rather than what some earlier build could.
 * A class in the table but no longer in routes is dropped from the view; a class
 * in routes with no row shows as all-off, which is exactly how it behaves.
 *
 * Rows carry the verbs that *exist*, not just the ones that are enabled --
 * otherwise an administrator could never turn anything on -- and a verb the
 * class does not expose is absent rather than rendered disabled, because a
 * checkbox that can never do anything is noise in a list this long.
 */
class ApiCatalog
{
    /**
     * Display names for the module keys that appear in `/api/v2/<module>/...`.
     * Anything unlisted falls back to the key with its first letter capitalised,
     * which is right for most and wrong for none in a way that matters.
     */
    private const MODULE_LABELS = [
        'pim' => 'PIM',
        'admin' => 'Admin',
        'leave' => 'Leave',
        'time' => 'Time',
        'attendance' => 'Attendance',
        'recruitment' => 'Recruitment',
        'performance' => 'Performance',
        'buzz' => 'Buzz',
        'claim' => 'Claim',
        'dashboard' => 'Dashboard',
        'directory' => 'Directory',
        'core' => 'Core',
        'auth' => 'Authentication',
        'mobile' => 'Mobile',
    ];

    /**
     * Modules an assistant is most often asked about, first. The rest follow
     * alphabetically. Ordering is presentation, but with 14 groups it decides
     * whether the screen opens on something recognisable or on Buzz.
     */
    private const MODULE_ORDER = [
        'pim',
        'leave',
        'attendance',
        'time',
        'admin',
        'recruitment',
        'performance',
        'claim',
        'directory',
        'dashboard',
        'buzz',
        'core',
        'auth',
        'mobile',
    ];

    private RouteMap $routeMap;

    /**
     * @var array<string,array<string,mixed>>|null
     */
    private ?array $classes = null;

    public function __construct(?RouteMap $routeMap = null)
    {
        $this->routeMap = $routeMap ?? new RouteMap();
    }

    /**
     * One entry per API class, keyed by class name.
     *
     * @return array<string,array{apiClass:string,label:string,module:string,verbs:array<string,int>}>
     */
    public function getClasses(): array
    {
        if ($this->classes !== null) {
            return $this->classes;
        }

        $classes = [];
        foreach ($this->routeMap->all() as $route) {
            $apiClass = $route->getApiClass();
            if (!isset($classes[$apiClass])) {
                $classes[$apiClass] = [
                    'apiClass' => $apiClass,
                    'label' => self::toLabel($apiClass),
                    'module' => $route->getModule(),
                    'verbs' => [],
                    'paths' => [],
                ];
            }

            $verb = ApiVerb::fromMethod($route->getMethod());
            $classes[$apiClass]['verbs'][$verb] = ($classes[$apiClass]['verbs'][$verb] ?? 0) + 1;
            $classes[$apiClass]['paths'][$route->getPath()] = true;
        }

        ksort($classes);
        return $this->classes = $classes;
    }

    /**
     * The screen's view model: modules in reading order, each holding its rows.
     *
     * @param array<string,array<string,mixed>> $permissionMap from McpApiPermissionService
     * @return array<int,array<string,mixed>>
     */
    public function getGroupedByModule(array $permissionMap): array
    {
        $groups = [];

        foreach ($this->getClasses() as $apiClass => $entry) {
            $row = $permissionMap[$apiClass] ?? null;

            $verbs = [];
            foreach (ApiVerb::all() as $verb) {
                if (!isset($entry['verbs'][$verb])) {
                    continue;
                }
                $verbs[] = [
                    'verb' => $verb,
                    'tools' => $entry['verbs'][$verb],
                    'enabled' => $row !== null && !empty($row[$verb]),
                    'risk' => ApiVerb::risk($verb),
                ];
            }

            $module = $entry['module'];
            if (!isset($groups[$module])) {
                $groups[$module] = [
                    'module' => $module,
                    'label' => self::MODULE_LABELS[$module] ?? ucfirst($module),
                    'apis' => [],
                ];
            }

            $groups[$module]['apis'][] = [
                'apiName' => $apiClass,
                'label' => $entry['label'],
                'sensitivity' => is_array($row) && isset($row['sensitivity'])
                    ? $row['sensitivity']
                    : ApiSensitivity::NORMAL,
                'verbs' => $verbs,
                'paths' => array_keys($entry['paths']),
            ];
        }

        $ordered = [];
        foreach (self::MODULE_ORDER as $module) {
            if (isset($groups[$module])) {
                $ordered[] = $groups[$module];
                unset($groups[$module]);
            }
        }
        // Anything a future release adds that this list has not been told about.
        ksort($groups);
        foreach ($groups as $group) {
            $ordered[] = $group;
        }

        return $ordered;
    }

    /**
     * How many tools each verb would contribute if every class were enabled,
     * against how many are enabled now. Lets the screen say "68 of 490" rather
     * than leaving an administrator to count checkboxes.
     *
     * @param array<string,array<string,mixed>> $permissionMap
     * @return array{tools:array<string,array{available:int,enabled:int}>,classes:int}
     */
    public function getCounts(array $permissionMap): array
    {
        $tools = [];
        foreach (ApiVerb::all() as $verb) {
            $tools[$verb] = ['available' => 0, 'enabled' => 0];
        }

        foreach ($this->getClasses() as $apiClass => $entry) {
            $row = $permissionMap[$apiClass] ?? null;
            foreach ($entry['verbs'] as $verb => $count) {
                $tools[$verb]['available'] += $count;
                if ($row !== null && !empty($row[$verb])) {
                    $tools[$verb]['enabled'] += $count;
                }
            }
        }

        return ['tools' => $tools, 'classes' => count($this->getClasses())];
    }

    /**
     * `OrangeHRM\Pim\Api\EmployeeSalaryComponentAPI` -> `Employee Salary Component`.
     *
     * The class name is the most accurate short description available: the
     * OpenAPI summary describes one operation, not the family, and the route
     * path is full of plurals and ids.
     */
    public static function toLabel(string $apiClass): string
    {
        $short = substr((string) strrchr('\\' . ltrim($apiClass, '\\'), '\\'), 1);
        $short = preg_replace('/API$/', '', $short);

        // Split on camel-case boundaries only, never after a digit, so
        // `I18NGroup` stays `I18N Group` and `EmployeeCSVImport` stays
        // `Employee CSV Import`.
        $words = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', (string) $short) ?: [];
        $words = array_values(array_filter(array_map('trim', $words)));
        if ($words === []) {
            return $apiClass;
        }

        // Original casing is kept, so `I18NGroup` stays `I18N Group` and an
        // acronym is not lower-cased into something unrecognisable.
        return implode(' ', $words);
    }
}
