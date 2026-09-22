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
 * The join of two sources, and it needs both. The route collection says what
 * this build can serve and under which verbs -- the table cannot know that, and
 * a stale row would otherwise offer a checkbox for an endpoint that no longer
 * exists. The permission table says which of those the product put in front of
 * this administrator. A class missing from either side is not shown.
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
     * alphabetically. Ordering is presentation, but with a dozen groups it decides
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
     * Only classes with a row are shown. The table is the catalog: shipping no
     * row for an endpoint keeps it out of an administrator's way, and inserting
     * one -- which a customer with a specific need can do -- brings it back
     * here automatically, with no code change.
     *
     * A class with a row but no route is skipped in the other direction: the
     * build no longer serves it, so there is nothing to enable.
     *
     * @param array<string,array<string,mixed>> $permissionMap from McpApiPermissionService
     * @return array<int,array<string,mixed>>
     */
    public function getGroupedByModule(array $permissionMap): array
    {
        $groups = [];

        foreach ($this->getClasses() as $apiClass => $entry) {
            if (!isset($permissionMap[$apiClass])) {
                continue;
            }
            $row = $permissionMap[$apiClass];

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
                // The row's label wins. It is seeded by the migration from
                // api-label.yaml and editable afterwards, so a customer's
                // wording survives an upgrade; a null column falls back to the
                // derived name.
                'label' => is_string($row['label'] ?? null) && $row['label'] !== ''
                    ? $row['label']
                    : $entry['label'],
                'sensitivity' => is_array($row) ? (int) ($row['sensitivity'] ?? ApiSensitivity::NORMAL)
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
     * against how many are enabled now. Lets the screen say "65 of 430" rather
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

        $classes = 0;
        foreach ($this->getClasses() as $apiClass => $entry) {
            $row = $permissionMap[$apiClass] ?? null;
            if ($row === null) {
                // Counted nowhere: "66 of 463" has to describe the catalog an
                // administrator can actually see, or the numbers argue with the
                // rows underneath them.
                continue;
            }
            $classes++;
            foreach ($entry['verbs'] as $verb => $count) {
                $tools[$verb]['available'] += $count;
                if (!empty($row[$verb])) {
                    $tools[$verb]['enabled'] += $count;
                }
            }
        }

        return ['tools' => $tools, 'classes' => $classes];
    }

    /**
     * Fallback name for an API whose row carries no label.
     *
     * The shipped wording lives in `ohrm_mcp_api_permission.label`, seeded once
     * from `installer/Migration/V5_10_0/api-label.yaml`. This only runs for a
     * class with no row or a label somebody cleared, and it says what the class
     * name says: `EmployeeSalaryComponentAPI` becomes "Employee Salary
     * Component". It makes no attempt to be the shipped wording, because
     * keeping a second copy of 153 names in step with the seed file is a
     * promise nobody can keep.
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

        return $words === [] ? $apiClass : implode(' ', $words);
    }
}
