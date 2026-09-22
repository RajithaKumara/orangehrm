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
 * What each API class is called on the MCP Configuration screen.
 *
 * The name is derived from the class name, which is right for most of the
 * catalog -- `LocationAPI` is "Location" and nobody needs to be told -- and
 * wrong in two ways that a developer reads straight past and an HR
 * administrator cannot:
 *
 *  - **Abbreviations.** `EmpEmergencyContactAPI` became "Emp Emergency
 *    Contact", `PimReportAPI` became "Pim Report". The expansions below are
 *    applied per word, so a class added later gets them too.
 *  - **Names that are not English.** "Employee Allowed Skill" is what the route
 *    is called; what it *does* is list the skills you may pick from. No
 *    derivation can bridge that, so those are written out by hand.
 *
 * Anything not named here keeps its derived label, which is the case for about
 * two thirds of the catalog.
 *
 * **This class is the seed, not the source.** The migration writes these into
 * `ohrm_mcp_api_permission.label`, and the screen reads the column, so a
 * customer can rename anything with one statement. A NULL column falls back
 * here, which is also how a row is reset to the shipped wording.
 *
 * English, like `ohrm_menu_item.menu_title`: the product translates DB-held
 * text by source string through `ohrm_i18n_lang_string`, so a label living in
 * a table is no less translatable than one living here.
 */
final class ApiLabels
{
    /**
     * Applied per word during derivation, so future classes benefit.
     */
    private const ABBREVIATIONS = [
        'Emp' => 'Employee',
        'Pim' => 'PIM',
        'Kpi' => 'KPI',
        'Kpis' => 'KPIs',
        'Us' => 'US',
        'Csv' => 'CSV',
        'Ldap' => 'LDAP',
        'Oauth' => 'OAuth',
        'Mcp' => 'MCP',
        'Api' => 'API',
        'Url' => 'URL',
        'Id' => 'ID',
    ];

    /**
     * Hand-written where the class name does not describe the thing.
     *
     * Grouped by why the derived name failed, because the reason is the useful
     * part when the next one comes along.
     */
    private const OVERRIDES = [
        // "Allowed" endpoints feed a picker: they answer "what may I choose
        // here", which reads as a permission check until you know better.
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedLanguageAPI' => 'Employee Language Options',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedLicenseAPI' => 'Employee License Options',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedSkillAPI' => 'Employee Skill Options',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedReportToEmployeeAPI' => 'Employee Report-To Options',
        'OrangeHRM\\Admin\\Api\\PayGradeAllowedCurrencyAPI' => 'Pay Grade Currency Options',
        'OrangeHRM\\Recruitment\\Api\\CandidateAllowedActionAPI' => 'Candidate Available Actions',
        'OrangeHRM\\Performance\\Api\\PerformanceReviewAllowedActionsAPI' => 'Performance Review Available Actions',

        // Checks a screen runs while someone types.
        'OrangeHRM\\Attendance\\Api\\AttendancePunchInRecordOverlapAPI' => 'Punch In Overlap Check',
        'OrangeHRM\\Attendance\\Api\\AttendancePunchOutRecordOverlapAPI' => 'Punch Out Overlap Check',
        'OrangeHRM\\Attendance\\Api\\AttendanceEditPunchInRecordOverlapAPI' => 'Punch In Overlap Check (editing)',
        'OrangeHRM\\Attendance\\Api\\AttendanceEditPunchOutRecordOverlapAPI' => 'Punch Out Overlap Check (editing)',
        'OrangeHRM\\Leave\\Api\\LeaveOverlapAPI' => 'Leave Overlap Check',
        'OrangeHRM\\Leave\\Api\\ValidationLeaveEntitlementValueAPI' => 'Leave Entitlement Value Check',

        // "Report Data" is the rows a report returns, not a kind of report.
        'OrangeHRM\\Pim\\Api\\PimReportDataAPI' => 'PIM Report Results',
        'OrangeHRM\\Leave\\Api\\LeaveReportDataAPI' => 'Leave Report Results',
        'OrangeHRM\\Time\\Api\\TimeReportDataAPI' => 'Time Report Results',

        // I18N is not a word an administrator has met. The product calls this
        // area Localization and Language Packages.
        'OrangeHRM\\Admin\\Api\\I18NLanguageAPI' => 'Language',
        'OrangeHRM\\Admin\\Api\\I18NTranslationAPI' => 'Language Translation',
        'OrangeHRM\\Admin\\Api\\I18NTranslationBulkAPI' => 'Language Translation (bulk)',
        'OrangeHRM\\Admin\\Api\\I18NImportErrorAPI' => 'Language Import Error',

        // Bulk variants read as a different feature rather than the same one
        // applied to many rows.
        'OrangeHRM\\Leave\\Api\\BulkLeaveAPI' => 'Leave (bulk)',
        'OrangeHRM\\Leave\\Api\\EmployeeBulkLeaveRequestAPI' => 'Employee Leave Request (bulk)',

        // Custom fields: one endpoint defines them, the other holds an
        // employee's values, and the derived names do not say which.
        'OrangeHRM\\Pim\\Api\\CustomFieldAPI' => 'Custom Field Definition',
        'OrangeHRM\\Pim\\Api\\EmployeeCustomFieldAPI' => 'Employee Custom Field Value',

        // Timesheet "items" are entries on the timesheet.
        'OrangeHRM\\Time\\Api\\MyTimesheetItemAPI' => 'My Timesheet Entry',
        'OrangeHRM\\Time\\Api\\EmployeeTimesheetItemAPI' => 'Employee Timesheet Entry',

        // Scope, where the derived names are individually right and collectively
        // indistinguishable.
        'OrangeHRM\\Attendance\\Api\\EmployeeLatestAttendanceRecordAPI' => 'Latest Attendance Record',
        'OrangeHRM\\Leave\\Api\\LeaveBalanceAPI' => 'Leave Balance (by leave type)',
        'OrangeHRM\\Performance\\Api\\ReviewEmployeeSupervisorAPI' => 'Performance Review Supervisor',

        // Named after the class rather than the route it serves: one endpoint,
        // /recruitment/interviewers, feeding the interviewer picker.
        'OrangeHRM\\Recruitment\\Api\\EmployeeListAPI' => 'Interviewer',

        // Plain English for the rest.
        'OrangeHRM\\Attendance\\Api\\TimezonesAPI' => 'Time Zone',
        'OrangeHRM\\Time\\Api\\TimesheetActionLogAPI' => 'Timesheet Action History',
        'OrangeHRM\\Time\\Api\\CopyProjectActivityAPI' => 'Copy Project Activities',
    ];

    public static function of(string $apiClass): string
    {
        $class = ltrim($apiClass, '\\');
        if (isset(self::OVERRIDES[$class])) {
            return self::OVERRIDES[$class];
        }
        return self::derive($class);
    }

    /**
     * `OrangeHRM\Pim\Api\EmployeeSalaryComponentAPI` -> `Employee Salary Component`.
     *
     * The class name is the most accurate short description available: the
     * OpenAPI summary describes one operation rather than the family, and the
     * route path is full of plurals and ids.
     */
    private static function derive(string $apiClass): string
    {
        $short = substr((string) strrchr('\\' . $apiClass, '\\'), 1);
        $short = preg_replace('/API$/', '', $short);

        // Split on camel-case boundaries only, never after a digit, so
        // `I18NGroup` stays `I18N Group` and `EmployeeCSVImport` stays
        // `Employee CSV Import`.
        $words = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', (string) $short) ?: [];
        $words = array_values(array_filter(array_map('trim', $words)));
        if ($words === []) {
            return $apiClass;
        }

        foreach ($words as $i => $word) {
            if (isset(self::ABBREVIATIONS[$word])) {
                $words[$i] = self::ABBREVIATIONS[$word];
            }
        }

        return implode(' ', $words);
    }

    /**
     * @return array<string,string> the hand-written names, for tests
     */
    public static function overrides(): array
    {
        return self::OVERRIDES;
    }
}
