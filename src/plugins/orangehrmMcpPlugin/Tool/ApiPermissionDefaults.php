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
 * The shipped contents of `ohrm_mcp_api_permission`: one row per API class, with
 * the four verb flags an instance starts life with.
 *
 * Why a hand-held list rather than a walk of the route collection at migration
 * time: the installer runs before the application kernel is in a state anyone
 * should rely on, and a seed that silently varies with what happens to be
 * registered is not a seed. Holding it here also means the diff for "5.10 opened
 * up a new endpoint family" is visible in review rather than implied.
 *
 * Drift is caught by ApiPermissionDefaultsTest, which asserts this list is
 * exactly the set of classes the live route map exposes. A class present in
 * routes but missing here is simply disabled -- absence of a row is refusal --
 * so drift degrades safely and the test exists to stop it going unnoticed.
 *
 * Writes ship off for every class without exception. Reads ship on for the
 * classes behind the previously curated default set: the same endpoints an
 * instance already offered, now expressed per class rather than per path.
 */
final class ApiPermissionDefaults
{
    /**
     * The API classes an instance ships a row for.
     *
     * Not everything the route collection exposes: NOT_SEEDED below lists the
     * routable classes we deliberately leave out, and between them the two cover
     * the routable surface exactly. Leaving a class out is not a block -- adding
     * a row makes it work -- it only decides what an administrator starts with.
     */
    public const ALL = [
        // pim (34)
        'OrangeHRM\\Pim\\Api\\CustomFieldAPI',
        'OrangeHRM\\Pim\\Api\\EmpEmergencyContactAPI',
        'OrangeHRM\\Pim\\Api\\EmpUsTaxExemptionAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedLanguageAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedLicenseAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedReportToEmployeeAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAllowedSkillAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAttachmentAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeContactDetailsAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeCountAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeCustomFieldAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeDependentAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeEducationAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeImmigrationRecordAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeJobDetailAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeLanguageAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeLicenseAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeMembershipAPI',
        'OrangeHRM\\Pim\\Api\\EmployeePersonalDetailAPI',
        'OrangeHRM\\Pim\\Api\\EmployeePictureAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeSalaryComponentAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeSkillAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeSubordinateAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeSupervisorAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeTerminationAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeWorkExperienceAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeWorkShiftAPI',
        'OrangeHRM\\Pim\\Api\\EmploymentContractAPI',
        'OrangeHRM\\Pim\\Api\\MyInfoAPI',
        'OrangeHRM\\Pim\\Api\\PimDefinedReportAPI',
        'OrangeHRM\\Pim\\Api\\PimReportAPI',
        'OrangeHRM\\Pim\\Api\\PimReportDataAPI',
        'OrangeHRM\\Pim\\Api\\TerminationReasonConfigurationAPI',

        // admin (26)
        'OrangeHRM\\Admin\\Api\\EducationAPI',
        'OrangeHRM\\Admin\\Api\\EmailSubscriberAPI',
        'OrangeHRM\\Admin\\Api\\EmailSubscriptionAPI',
        'OrangeHRM\\Admin\\Api\\EmploymentStatusAPI',
        'OrangeHRM\\Admin\\Api\\I18NImportErrorAPI',
        'OrangeHRM\\Admin\\Api\\I18NLanguageAPI',
        'OrangeHRM\\Admin\\Api\\I18NTranslationAPI',
        'OrangeHRM\\Admin\\Api\\I18NTranslationBulkAPI',
        'OrangeHRM\\Admin\\Api\\JobCategoryAPI',
        'OrangeHRM\\Admin\\Api\\JobSpecificationAPI',
        'OrangeHRM\\Admin\\Api\\JobTitleAPI',
        'OrangeHRM\\Admin\\Api\\LanguageAPI',
        'OrangeHRM\\Admin\\Api\\LicenseAPI',
        'OrangeHRM\\Admin\\Api\\LocalizationAPI',
        'OrangeHRM\\Admin\\Api\\LocationAPI',
        'OrangeHRM\\Admin\\Api\\MembershipAPI',
        'OrangeHRM\\Admin\\Api\\NationalityAPI',
        'OrangeHRM\\Admin\\Api\\PayGradeAPI',
        'OrangeHRM\\Admin\\Api\\PayGradeAllowedCurrencyAPI',
        'OrangeHRM\\Admin\\Api\\PayGradeCurrencyAPI',
        'OrangeHRM\\Admin\\Api\\SkillAPI',
        'OrangeHRM\\Admin\\Api\\SubunitAPI',
        'OrangeHRM\\Admin\\Api\\UserAPI',
        'OrangeHRM\\Admin\\Api\\WorkShiftAPI',
        'OrangeHRM\\Admin\\Api\\WorkShiftEmployeeAPI',
        'OrangeHRM\\CorporateBranding\\Api\\ThemeAPI',

        // leave (21)
        'OrangeHRM\\Leave\\Api\\BulkLeaveAPI',
        'OrangeHRM\\Leave\\Api\\EligibleLeaveTypeAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeBulkLeaveRequestAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveBalanceAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveEntitlementAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveRequestAPI',
        'OrangeHRM\\Leave\\Api\\HolidayAPI',
        'OrangeHRM\\Leave\\Api\\LeaveAPI',
        'OrangeHRM\\Leave\\Api\\LeaveBalanceAPI',
        'OrangeHRM\\Leave\\Api\\LeaveCommentAPI',
        'OrangeHRM\\Leave\\Api\\LeaveEntitlementAPI',
        'OrangeHRM\\Leave\\Api\\LeaveOverlapAPI',
        'OrangeHRM\\Leave\\Api\\LeavePeriodAPI',
        'OrangeHRM\\Leave\\Api\\LeaveReportAPI',
        'OrangeHRM\\Leave\\Api\\LeaveReportDataAPI',
        'OrangeHRM\\Leave\\Api\\LeaveRequestCommentAPI',
        'OrangeHRM\\Leave\\Api\\LeaveTypeAPI',
        'OrangeHRM\\Leave\\Api\\MyLeaveRequestAPI',
        'OrangeHRM\\Leave\\Api\\ValidationLeaveEntitlementValueAPI',
        'OrangeHRM\\Leave\\Api\\WorkWeekAPI',

        // time (15)
        'OrangeHRM\\Time\\Api\\CopyProjectActivityAPI',
        'OrangeHRM\\Time\\Api\\CustomerAPI',
        'OrangeHRM\\Time\\Api\\DefaultTimesheetAPI',
        'OrangeHRM\\Time\\Api\\EmployeeTimesheetAPI',
        'OrangeHRM\\Time\\Api\\EmployeeTimesheetItemAPI',
        'OrangeHRM\\Time\\Api\\EmployeeTimesheetListAPI',
        'OrangeHRM\\Time\\Api\\MyTimesheetAPI',
        'OrangeHRM\\Time\\Api\\MyTimesheetItemAPI',
        'OrangeHRM\\Time\\Api\\ProjectAPI',
        'OrangeHRM\\Time\\Api\\ProjectActivityAPI',
        'OrangeHRM\\Time\\Api\\ProjectAdminAPI',
        'OrangeHRM\\Time\\Api\\TimeReportAPI',
        'OrangeHRM\\Time\\Api\\TimeReportDataAPI',
        'OrangeHRM\\Time\\Api\\TimesheetActionLogAPI',
        'OrangeHRM\\Time\\Api\\TimesheetCommentAPI',

        // recruitment (18)
        'OrangeHRM\\Recruitment\\Api\\CandidateAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateAllowedActionAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateAttachmentAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateHiringAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateHistoryAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateInterviewSchedulingAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateJobOfferingAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateMarkingInterviewFailAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateMarkingInterviewPassAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateOfferDecliningAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateRejectionAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateShortlistingAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateStatusAPI',
        'OrangeHRM\\Recruitment\\Api\\EmployeeListAPI',
        'OrangeHRM\\Recruitment\\Api\\HiringManagerAPI',
        'OrangeHRM\\Recruitment\\Api\\InterviewAttachmentAPI',
        'OrangeHRM\\Recruitment\\Api\\VacancyAPI',
        'OrangeHRM\\Recruitment\\Api\\VacancyAttachmentAPI',

        // performance (15)
        'OrangeHRM\\Performance\\Api\\EmployeeEvaluationAPI',
        'OrangeHRM\\Performance\\Api\\EmployeeTrackerAPI',
        'OrangeHRM\\Performance\\Api\\KpiAPI',
        'OrangeHRM\\Performance\\Api\\MyReviewAPI',
        'OrangeHRM\\Performance\\Api\\MyTrackerAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceReviewAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceReviewAllowedActionsAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceReviewFinalEvaluationAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceTrackerAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceTrackerLogAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceTrackerReviewerAPI',
        'OrangeHRM\\Performance\\Api\\ReviewEmployeeSupervisorAPI',
        'OrangeHRM\\Performance\\Api\\ReviewKpiAPI',
        'OrangeHRM\\Performance\\Api\\ReviewListAPI',
        'OrangeHRM\\Performance\\Api\\SupervisorEvaluationAPI',

        // attendance (11)
        'OrangeHRM\\Attendance\\Api\\AttendanceConfigurationAPI',
        'OrangeHRM\\Attendance\\Api\\AttendanceEditPunchInRecordOverlapAPI',
        'OrangeHRM\\Attendance\\Api\\AttendanceEditPunchOutRecordOverlapAPI',
        'OrangeHRM\\Attendance\\Api\\AttendancePunchInRecordOverlapAPI',
        'OrangeHRM\\Attendance\\Api\\AttendancePunchOutRecordOverlapAPI',
        'OrangeHRM\\Attendance\\Api\\AttendanceRecordAPI',
        'OrangeHRM\\Attendance\\Api\\EmployeeAttendanceRecordAPI',
        'OrangeHRM\\Attendance\\Api\\EmployeeAttendanceSummaryAPI',
        'OrangeHRM\\Attendance\\Api\\EmployeeLatestAttendanceRecordAPI',
        'OrangeHRM\\Attendance\\Api\\MyAttendanceRecordAPI',
        'OrangeHRM\\Attendance\\Api\\TimezonesAPI',

        // claim (7)
        'OrangeHRM\\Claim\\Api\\ClaimAttachmentAPI',
        'OrangeHRM\\Claim\\Api\\ClaimEventAPI',
        'OrangeHRM\\Claim\\Api\\ClaimExpenseAPI',
        'OrangeHRM\\Claim\\Api\\ClaimExpenseTypeAPI',
        'OrangeHRM\\Claim\\Api\\ClaimRequestActionAPI',
        'OrangeHRM\\Claim\\Api\\EmployeeClaimRequestAPI',
        'OrangeHRM\\Claim\\Api\\MyClaimRequestAPI',

        // dashboard (5)
        'OrangeHRM\\Dashboard\\Api\\EmployeeActionSummaryAPI',
        'OrangeHRM\\Dashboard\\Api\\EmployeeDistributionByLocationAPI',
        'OrangeHRM\\Dashboard\\Api\\EmployeeDistributionBySubunitAPI',
        'OrangeHRM\\Dashboard\\Api\\EmployeeOnLeaveAPI',
        'OrangeHRM\\Dashboard\\Api\\EmployeeTimeAtWorkAPI',

        // directory (1)
        'OrangeHRM\\CorporateDirectory\\Api\\EmployeeDirectoryAPI',

    ];

    /**
     * Classes whose reads are switched on out of the box.
     *
     * Everything else is *available* and one checkbox away; this is only what an
     * instance starts with. The distinction matters both ways: a hard allowlist
     * would block a customer with a legitimate need, while shipping all 190
     * classes enabled would hand a model hundreds of tools and measurably
     * degrade which one it picks.
     *
     * Selection rules, applied to the readable surface to reach these 42:
     *
     *  - Drop the remaining form plumbing. The worst of it never reaches this
     *    list at all -- InternalApis excludes validation oracles and UI
     *    scaffolding from the catalog outright -- but `allowed`, `overlaps` and
     *    `count` endpoints still exist to support a screen as a user types.
     *  - Drop report builders. `reports` and `reports/data` drive the UI report
     *    designer; they take a report definition, not a question.
     *  - Drop modules nobody asks an assistant about: Buzz, dashboard widgets,
     *    localisation, theme, module lists.
     *
     * Deliberately left off despite being readable: salary components, tax
     * exemption, termination records, employment contracts, performance reviews
     * and expense claims. All are reachable by an administrator through the UI,
     * but putting compensation and exit data in an assistant's default reach is
     * a decision an organisation should make explicitly rather than inherit --
     * which is exactly what the screen is for.
     *
     * Per-class granularity is slightly coarser than the per-path list it
     * replaces: these 42 classes cover the paths that shipped enabled and
     * pull in 18 single-item variants (`.../{id}`) alongside the collections
     * they belong to. That is the trade -- an administrator reviews 43 rows
     * instead of 250 checkboxes, and gets the by-id read of a list they already
     * had.
     */
    public const READ_ENABLED = [
        'OrangeHRM\\Admin\\Api\\EducationAPI',
        'OrangeHRM\\Admin\\Api\\EmploymentStatusAPI',
        'OrangeHRM\\Admin\\Api\\JobTitleAPI',
        'OrangeHRM\\Admin\\Api\\LanguageAPI',
        'OrangeHRM\\Admin\\Api\\LocationAPI',
        'OrangeHRM\\Admin\\Api\\NationalityAPI',
        'OrangeHRM\\Admin\\Api\\PayGradeAPI',
        'OrangeHRM\\Admin\\Api\\SkillAPI',
        'OrangeHRM\\Admin\\Api\\SubunitAPI',
        'OrangeHRM\\Admin\\Api\\WorkShiftAPI',
        'OrangeHRM\\Attendance\\Api\\EmployeeAttendanceRecordAPI',
        'OrangeHRM\\Attendance\\Api\\EmployeeAttendanceSummaryAPI',
        'OrangeHRM\\Attendance\\Api\\EmployeeLatestAttendanceRecordAPI',
        'OrangeHRM\\Attendance\\Api\\MyAttendanceRecordAPI',
        'OrangeHRM\\CorporateDirectory\\Api\\EmployeeDirectoryAPI',
        'OrangeHRM\\Leave\\Api\\EligibleLeaveTypeAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveBalanceAPI',
        'OrangeHRM\\Leave\\Api\\EmployeeLeaveRequestAPI',
        'OrangeHRM\\Leave\\Api\\HolidayAPI',
        'OrangeHRM\\Leave\\Api\\LeaveBalanceAPI',
        'OrangeHRM\\Leave\\Api\\LeavePeriodAPI',
        'OrangeHRM\\Leave\\Api\\LeaveTypeAPI',
        'OrangeHRM\\Leave\\Api\\MyLeaveRequestAPI',
        'OrangeHRM\\Leave\\Api\\WorkWeekAPI',
        'OrangeHRM\\Pim\\Api\\EmpEmergencyContactAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeContactDetailsAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeDependentAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeJobDetailAPI',
        'OrangeHRM\\Pim\\Api\\EmployeePersonalDetailAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeSubordinateAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeSupervisorAPI',
        'OrangeHRM\\Pim\\Api\\EmployeeWorkShiftAPI',
        'OrangeHRM\\Pim\\Api\\MyInfoAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateStatusAPI',
        'OrangeHRM\\Recruitment\\Api\\EmployeeListAPI',
        'OrangeHRM\\Recruitment\\Api\\HiringManagerAPI',
        'OrangeHRM\\Recruitment\\Api\\VacancyAPI',
        'OrangeHRM\\Time\\Api\\EmployeeTimesheetAPI',
        'OrangeHRM\\Time\\Api\\EmployeeTimesheetListAPI',    ];

    /**
     * Routable API classes we deliberately ship **no row** for.
     *
     * Not a block. Nothing in code refuses these: if a customer genuinely wants
     * one, inserting a row in `ohrm_mcp_api_permission` makes it work, and it
     * then appears on the MCP Access screen like any other. The only thing this
     * list decides is what the product hands an administrator to start from.
     *
     * They are left out because each is a tool a model must read past to find
     * the one it wants, and tool-selection accuracy falls as the catalog grows.
     * A row nobody would ever switch on has a cost and no benefit.
     *
     * The shape of the things listed here, so the next one is an easy call:
     *
     *  - **Validation oracles.** Their whole job is to answer "is this value
     *    acceptable" while a person types. A model should submit the real
     *    request and read the 422 instead.
     *  - **UI scaffolding.** Menus, quick launch, data groups, theme previews,
     *    widget configuration. These describe how to draw OrangeHRM, not what
     *    is in it.
     *  - **Environment metadata.** Version and clock endpoints.
     *  - **File plumbing.** Bulk import that takes an uploaded file, which an
     *    assistant has no way to supply meaningfully.
     *
     * Kept as an explicit list rather than left implicit because
     * ApiPermissionDefaultsTest asserts ALL + NOT_SEEDED covers every routable
     * class exactly. Without it, an endpoint family added by a future release
     * would go unseeded and therefore unreachable, and nobody would notice.
     */
    public const NOT_SEEDED = [
        // Validation oracles
        'OrangeHRM\\Pim\\Api\\ValidationEmployeeEmailAPI',
        'OrangeHRM\\Pim\\Api\\ValidationEmployeeOtherEmailAPI',
        'OrangeHRM\\Time\\Api\\ValidateProjectNameAPI',
        'OrangeHRM\\Time\\Api\\ValidationCustomerNameAPI',
        'OrangeHRM\\Time\\Api\\ValidationProjectActivityNameAPI',
        'OrangeHRM\\Time\\Api\\TimesheetProjectActivityUniqueValidationAPI',
        'OrangeHRM\\Admin\\Api\\I18NTranslationValidationAPI',
        'OrangeHRM\\Core\\Api\\Rest\\ValidationUniqueAPI',
        'OrangeHRM\\Authentication\\PublicApi\\PasswordStrengthValidationAPI',

        // UI scaffolding
        'OrangeHRM\\Dashboard\\Api\\QuickLaunchAPI',
        'OrangeHRM\\Dashboard\\Api\\EmployeeOnLeaveTodayConfigAPI',
        'OrangeHRM\\Mobile\\Api\\MenuItemAPI',
        'OrangeHRM\\Core\\Api\\Rest\\DataGroupAPI',
        'OrangeHRM\\CorporateBranding\\Api\\PreviewThemeAPI',
        'OrangeHRM\\Admin\\Api\\I18NGroupAPI',
        'OrangeHRM\\Time\\Api\\TimesheetTimeFormatAPI',

        // Environment metadata
        'OrangeHRM\\Core\\PublicApi\\VersionAPI',
        'OrangeHRM\\Attendance\\Api\\CurrentDateTimeAPI',

        // File plumbing
        'OrangeHRM\\Pim\\Api\\EmployeeCSVImportAPI',

        // Configuration behind a single dropdown on one PIM screen
        'OrangeHRM\\Pim\\Api\\ReportingMethodConfigurationAPI',

        // The Buzz social feed. Nobody asks an HR assistant what was posted,
        // the likes and comments are pure catalog padding, and a post written
        // by an assistant under someone's name is broadcast org-wide with no
        // approval step and no way to un-see it. It is also the one place where
        // any employee can publish free text to everyone, which makes it the
        // obvious place to plant instructions aimed at an assistant -- a reason
        // that applies to the reads, not just the writes.
        'OrangeHRM\\Buzz\\Api\\BuzzFeedAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzPostAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzPostShareAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzShareAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzCommentAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzLikeOnShareAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzLikeOnCommentAPI',
        'OrangeHRM\\Buzz\\Api\\BuzzVideoURLValidationAPI',
        'OrangeHRM\\Buzz\\Api\\EmployeeAnniversaryAPI',

        // Core internals: an installation health probe and the "about this
        // organisation" box. Neither is HR data.
        'OrangeHRM\\Core\\Api\\Rest\\AboutOrganizationAPI',
        'OrangeHRM\\SystemCheck\\PublicApi\\SystemCheckAPI',

        // Administrative configuration. These set up the product rather than
        // describe the people in it, and an assistant editing them changes how
        // OrangeHRM behaves for everyone.
        'OrangeHRM\\Admin\\Api\\I18NTranslationImportAPI',
        'OrangeHRM\\Admin\\Api\\ModulesAPI',
        'OrangeHRM\\Admin\\Api\\OrganizationAPI',
        'OrangeHRM\\Admin\\Api\\ValidationUserNameAPI',
        'OrangeHRM\\Time\\Api\\TimeConfigPeriodAPI',
        'OrangeHRM\\Pim\\Api\\OptionalFieldAPI',
    ];

    /**
     * The rows to seed, keyed by API class.
     *
     * @return array<string,array{read:bool,create:bool,update:bool,delete:bool,sensitivity:string}>
     */
    public static function rows(): array
    {
        $readEnabled = array_flip(self::READ_ENABLED);

        $rows = [];
        foreach (self::ALL as $apiClass) {
            $rows[$apiClass] = [
                'read' => isset($readEnabled[$apiClass]),
                // No write ships enabled. Enabling one is an administrator's
                // deliberate act, taken against a screen that says what it does.
                'create' => false,
                'update' => false,
                'delete' => false,
                'sensitivity' => ApiSensitivity::of($apiClass),
            ];
        }
        return $rows;
    }

    public static function count(): int
    {
        return count(self::ALL);
    }
}
