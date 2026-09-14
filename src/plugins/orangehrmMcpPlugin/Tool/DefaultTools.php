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

/**
 * The tools an instance ships with switched on.
 *
 * Every non-restricted endpoint is *available* to an administrator; this list is
 * only what is enabled out of the box. The distinction matters: a hard allowlist
 * would block a customer with a legitimate need, while shipping everything on
 * would hand a model hundreds of tools and measurably degrade which one it picks.
 *
 * Selection rules, applied to roughly 240 readable endpoints to reach these:
 *
 *  - Drop form plumbing. Anything under `validation`, `allowed`, `overlaps` or
 *    `count` exists to support the web UI as a user types. A model calling an
 *    overlap checker is confused, not productive.
 *  - Drop single-item variants where the collection already answers. The list
 *    endpoint plus a filter covers nearly every question, and the paired
 *    variants nearly doubled the count for no gain.
 *  - Drop report builders. `reports` and `reports/data` drive the UI report
 *    designer; they take a report definition, not a question.
 *  - Drop modules nobody asks an assistant about: Buzz, dashboard widgets,
 *    mobile, internal core, localisation, theme, module lists.
 *
 * Deliberately left off despite being readable: salary components, tax
 * exemption, termination records, performance reviews and expense claims. All
 * are reachable by an administrator through the UI, but putting compensation and
 * exit data in an assistant's default reach is a decision an organisation should
 * make explicitly rather than inherit.
 *
 * Writes are never defaulted on. A write tool is enabled deliberately or not at
 * all, and read-only mode still overrides the list entirely.
 *
 * The seeded value is a starting point, not a floor or a ceiling. Once an
 * administrator saves the screen, their stored list is the only thing that
 * counts and nothing here overrides it.
 */
final class DefaultTools
{
    /**
     * Curated tools. These are hand-written and few, so all of them ship on.
     */
    public const CURATED = [
        'mcp_get_current_employee',
        'mcp_list_employees',
        'mcp_list_job_titles',
        'mcp_list_leave_types',
    ];

    /**
     * Generic tools, by name. Names encode method and path, so a route rename
     * silently orphans an entry -- `DefaultToolsTest` asserts every name here
     * still resolves to a live route, which turns that into a failing test
     * rather than a tool that quietly stopped being offered.
     */
    public const GENERIC = [
        'rest_get_pim_myself',
        'rest_get_pim_employees',
        'rest_get_pim_employees_by_empNumber',
        'rest_get_pim_employees_by_empNumber_personal_details',
        'rest_get_pim_employees_by_empNumber_job_details',
        'rest_get_pim_employee_by_empNumber_contact_details',
        'rest_get_pim_employees_by_empNumber_emergency_contacts',
        'rest_get_pim_employees_by_empNumber_dependents',
        'rest_get_pim_employees_by_empNumber_subordinates',
        'rest_get_pim_employees_by_empNumber_supervisors',
        'rest_get_pim_employees_by_empNumber_work_shift',
        'rest_get_directory_employees',
        'rest_get_directory_employees_by_empNumber',
        'rest_get_leave_leave_types',
        'rest_get_leave_leave_types_eligible',
        'rest_get_leave_employees_leave_balances',
        'rest_get_leave_leave_balance_leave_type_by_leaveTypeId',
        'rest_get_leave_leave_requests',
        'rest_get_leave_leave_requests_by_leaveRequestId',
        'rest_get_leave_employees_leave_requests',
        'rest_get_leave_employees_leaves',
        'rest_get_leave_holidays',
        'rest_get_leave_leave_period',
        'rest_get_leave_workweek',
        'rest_get_attendance_records',
        'rest_get_attendance_records_latest',
        'rest_get_attendance_employees_summary',
        'rest_get_attendance_employees_by_empNumber_records',
        'rest_get_time_employees_by_empNumber_timesheets',
        'rest_get_time_employees_timesheets_list',
        'rest_get_admin_job_titles',
        'rest_get_admin_locations',
        'rest_get_admin_subunits',
        'rest_get_admin_employment_statuses',
        'rest_get_admin_pay_grades',
        'rest_get_admin_work_shifts',
        'rest_get_admin_nationalities',
        'rest_get_admin_skills',
        'rest_get_admin_languages',
        'rest_get_admin_educations',
        'rest_get_admin_organization',
        'rest_get_recruitment_vacancies',
        'rest_get_recruitment_vacancies_by_id',
        'rest_get_recruitment_candidates',
        'rest_get_recruitment_candidates_by_id',
        'rest_get_recruitment_candidates_statuses',
        'rest_get_recruitment_hiring_managers',
        'rest_get_recruitment_interviewers',
    ];

    /**
     * @return string[] every tool name enabled by default
     */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(self::CURATED, self::GENERIC)));
    }

    public static function count(): int
    {
        return count(self::all());
    }
}
