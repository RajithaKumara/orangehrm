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
 * How sensitive the data behind an API class is, independent of the verb used to
 * reach it.
 *
 * Two axes, deliberately kept apart:
 *
 *  - **Verb risk** is about what a call *does*. Delete is worse than update,
 *    update worse than read. The admin screen derives that from the verb alone
 *    and needs nothing from this class.
 *  - **Data sensitivity**, below, is about what a call *touches*, and applies
 *    even to a read.
 *
 * Collapsing the two would get the important case backwards: reading an
 * employee's salary is more sensitive than updating a job title, yet one is a
 * GET and the other a PUT.
 *
 * These values are authored here and seeded into `ohrm_mcp_api_permission` by
 * the migration. The screen displays them; it does not edit them. A tenant who
 * could downgrade a warning would make every warning meaningless, and the
 * classification is a product judgement rather than a local preference.
 *
 * There is one flag, not a scale. An earlier version had a middle tier called
 * "personal", and it was wrong: in an HRIS almost everything about an employee
 * is personal data -- a job title and an attendance record included -- so the
 * label was true of most of the catalog and implied the rows it was missing
 * from were not personal. A flag that says nothing is noise; one that says
 * something false is worse.
 *
 * What is left flags the endpoints whose name does not announce what they
 * hold. "Employee US Tax Exemption" does not read as compensation data, and
 * verb risk cannot help an administrator here: reading a salary and reading a
 * job title are both GET.
 */
final class ApiSensitivity
{
    /** Everything the flag below does not cover. */
    public const NORMAL = 'normal';

    /** Compensation, legal status, evaluation, attachments, accounts. */
    public const SENSITIVE = 'sensitive';

    /**
     * The categories a leak of which is materially worse than a leak of someone's
     * job title, and several of which are special-category data under privacy law.
     */
    public const SENSITIVE_APIS = [
        // Compensation and tax
        'OrangeHRM\\Pim\\Api\\EmployeeSalaryComponentAPI',
        'OrangeHRM\\Pim\\Api\\EmpUsTaxExemptionAPI',
        'OrangeHRM\\Pim\\Api\\EmploymentContractAPI',

        // Immigration and legal status
        'OrangeHRM\\Pim\\Api\\EmployeeImmigrationRecordAPI',

        // Account control
        'OrangeHRM\\Admin\\Api\\UserAPI',

        // Performance evaluation: ratings and free-text judgements about a person.
        'OrangeHRM\\Performance\\Api\\PerformanceReviewAPI',
        'OrangeHRM\\Performance\\Api\\PerformanceReviewFinalEvaluationAPI',
        'OrangeHRM\\Performance\\Api\\EmployeeEvaluationAPI',
        'OrangeHRM\\Performance\\Api\\SupervisorEvaluationAPI',
        'OrangeHRM\\Performance\\Api\\MyReviewAPI',
        'OrangeHRM\\Performance\\Api\\ReviewListAPI',

        // Uploaded files. The contents are unknown to us and in practice hold
        // identity documents, medical notes and signed contracts, so the ceiling
        // is the highest thing anyone has ever attached.
        'OrangeHRM\\Pim\\Api\\EmployeeAttachmentAPI',
        'OrangeHRM\\Recruitment\\Api\\CandidateAttachmentAPI',
        'OrangeHRM\\Recruitment\\Api\\InterviewAttachmentAPI',
        'OrangeHRM\\Claim\\Api\\ClaimAttachmentAPI',

        // A PIM report returns whatever field groups its definition selects,
        // which includes salary. The endpoint cannot be classified by anything
        // milder than the most sensitive field it can carry.
        'OrangeHRM\\Pim\\Api\\PimReportDataAPI',
    ];

    public static function of(string $apiClass): string
    {
        $class = ltrim($apiClass, '\\');

        return in_array($class, self::SENSITIVE_APIS, true) ? self::SENSITIVE : self::NORMAL;
    }

    /**
     * @return string[] the two levels, least sensitive first
     */
    public static function levels(): array
    {
        return [self::NORMAL, self::SENSITIVE];
    }

    public static function isValid(string $level): bool
    {
        return in_array($level, self::levels(), true);
    }
}
