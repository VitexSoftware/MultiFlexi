<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\DataRetention;

use Ease\SQL\Orm;
use MultiFlexi\User;

/**
 * Retention Policy Manager.
 *
 * Manages data retention policies configuration for GDPR compliance.
 * Provides interface for defining retention periods for different data types.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class RetentionPolicyManager extends \Ease\Sand
{
    /**
     * @var Orm Database handle for retention policies
     */
    private Orm $policies;

    /**
     * @var null|User Current user
     */
    private ?User $currentUser = null;

    /**
     * @var array Valid deletion actions
     */
    private array $validActions = ['hard_delete', 'soft_delete', 'anonymize', 'archive'];

    /**
     * @var array Supported data types
     */
    private array $supportedDataTypes = [
        'user_personal_data' => 'User Personal Data',
        'session_data' => 'Session Data',
        'audit_data' => 'Audit Data',
        'job_execution_data' => 'Job Execution Data',
        'application_logs' => 'Application Logs',
        'company_business_data' => 'Company Business Data',
        'security_data' => 'Security Data',
    ];

    /**
     * Constructor.
     *
     * @param null|User $user Current user
     */
    public function __construct(?User $user = null)
    {
        parent::__construct();
        $this->policies = new Orm();
        $this->policies->setMyTable('data_retention_policies');
        $this->currentUser = $user;
    }

    /**
     * Create a new retention policy.
     *
     * @param array $policyData Policy configuration
     *
     * @throws \Exception
     *
     * @return int Policy ID
     */
    public function createPolicy(array $policyData): int
    {
        // Validate required fields
        $requiredFields = ['policy_name', 'data_type', 'table_name', 'retention_period_days', 'deletion_action'];

        foreach ($requiredFields as $field) {
            if (empty($policyData[$field])) {
                throw new \Exception(sprintf(_('Required field missing: %s'), $field));
            }
        }

        // Validate policy name uniqueness
        if ($this->policyExists($policyData['policy_name'])) {
            throw new \Exception(sprintf(_('Policy name "%s" already exists'), $policyData['policy_name']));
        }

        // Validate deletion action
        if (!\in_array($policyData['deletion_action'], $this->validActions, true)) {
            throw new \Exception(sprintf(_('Invalid deletion action: %s'), $policyData['deletion_action']));
        }

        // Validate retention period
        if ($policyData['retention_period_days'] < 0) {
            throw new \Exception(_('Retention period must be non-negative'));
        }

        // Get current user ID
        $userId = $this->currentUser ? $this->currentUser->getId() : self::getCurrentUserId();

        if (!$userId) {
            throw new \Exception(_('No user available for policy creation'));
        }

        // Prepare policy data
        $policyRecord = [
            'policy_name' => trim($policyData['policy_name']),
            'data_type' => $policyData['data_type'],
            'table_name' => $policyData['table_name'],
            'retention_period_days' => (int) $policyData['retention_period_days'],
            'grace_period_days' => (int) ($policyData['grace_period_days'] ?? 30),
            'deletion_action' => $policyData['deletion_action'],
            'legal_basis' => $policyData['legal_basis'] ?? '',
            'description' => $policyData['description'] ?? '',
            'enabled' => isset($policyData['enabled']) ? (bool) $policyData['enabled'] : true,
            'created_by' => $userId,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];

        if ($this->policies->insertToSQL($policyRecord)) {
            $policyId = $this->policies->getLastInsertID();

            $this->addStatusMessage(
                sprintf(_('Created retention policy "%s" (ID: %d)'), $policyRecord['policy_name'], $policyId),
                'success',
            );

            return $policyId;
        }

        throw new \Exception(_('Failed to create retention policy'));
    }

    /**
     * Update an existing retention policy.
     *
     * @param int   $policyId   Policy ID
     * @param array $policyData Updated policy data
     *
     * @throws \Exception
     *
     * @return bool Success status
     */
    public function updatePolicy(int $policyId, array $policyData): bool
    {
        // Load existing policy
        $this->policies->loadFromSQL($policyId);
        $existingPolicy = $this->policies->getData();

        if (empty($existingPolicy)) {
            throw new \Exception(_('Policy not found'));
        }

        // Validate policy name uniqueness (if changed)
        if (isset($policyData['policy_name'])
            && $policyData['policy_name'] !== $existingPolicy['policy_name']
            && $this->policyExists($policyData['policy_name'])) {
            throw new \Exception(sprintf(_('Policy name "%s" already exists'), $policyData['policy_name']));
        }

        // Validate deletion action (if provided)
        if (isset($policyData['deletion_action'])
            && !\in_array($policyData['deletion_action'], $this->validActions, true)) {
            throw new \Exception(sprintf(_('Invalid deletion action: %s'), $policyData['deletion_action']));
        }

        // Validate retention period (if provided)
        if (isset($policyData['retention_period_days'])
            && $policyData['retention_period_days'] < 0) {
            throw new \Exception(_('Retention period must be non-negative'));
        }

        // Prepare update data
        $updateData = ['updated_at' => new \DateTime()];

        $updateableFields = [
            'policy_name', 'data_type', 'table_name', 'retention_period_days',
            'grace_period_days', 'deletion_action', 'legal_basis', 'description', 'enabled',
        ];

        foreach ($updateableFields as $field) {
            if (\array_key_exists($field, $policyData)) {
                $updateData[$field] = $policyData[$field];
            }
        }

        if ($this->policies->updateToSQL($updateData, ['id' => $policyId])) {
            $this->addStatusMessage(
                sprintf(_('Updated retention policy ID %d'), $policyId),
                'success',
            );

            return true;
        }

        throw new \Exception(_('Failed to update retention policy'));
    }

    /**
     * Delete a retention policy.
     *
     * @param int $policyId Policy ID
     *
     * @throws \Exception
     *
     * @return bool Success status
     */
    public function deletePolicy(int $policyId): bool
    {
        // Check if policy exists
        $this->policies->loadFromSQL($policyId);
        $policy = $this->policies->getData();

        if (empty($policy)) {
            throw new \Exception(_('Policy not found'));
        }

        // Check if policy has associated cleanup jobs
        $jobsTable = new Orm();
        $jobsTable->setMyTable('retention_cleanup_jobs');
        $associatedJobs = $jobsTable->listingQuery()
            ->where('policy_id', $policyId)
            ->count();

        if ($associatedJobs > 0) {
            throw new \Exception(sprintf(
                _('Cannot delete policy "%s": %d cleanup jobs are associated with it'),
                $policy['policy_name'],
                $associatedJobs,
            ));
        }

        if ($this->policies->deleteFromSQL(['id' => $policyId])) {
            $this->addStatusMessage(
                sprintf(_('Deleted retention policy "%s"'), $policy['policy_name']),
                'success',
            );

            return true;
        }

        throw new \Exception(_('Failed to delete retention policy'));
    }

    /**
     * Enable/disable a retention policy.
     *
     * @param int  $policyId Policy ID
     * @param bool $enabled  Enable status
     *
     * @throws \Exception
     *
     * @return bool Success status
     */
    public function togglePolicy(int $policyId, bool $enabled): bool
    {
        $updateData = [
            'enabled' => $enabled,
            'updated_at' => new \DateTime(),
        ];

        if ($this->policies->updateToSQL($updateData, ['id' => $policyId])) {
            $this->addStatusMessage(
                sprintf(_('Policy ID %d %s'), $policyId, $enabled ? _('enabled') : _('disabled')),
                'success',
            );

            return true;
        }

        throw new \Exception(sprintf(_('Failed to %s policy'), $enabled ? _('enable') : _('disable')));
    }

    /**
     * Get all retention policies.
     *
     * @param bool $enabledOnly Return only enabled policies
     *
     * @return array Policies
     */
    public function getPolicies(bool $enabledOnly = false): array
    {
        $query = $this->policies->listingQuery()->orderBy('policy_name');

        if ($enabledOnly) {
            $query->where('enabled', true);
        }

        return $query->fetchAll();
    }

    /**
     * Get policy by ID.
     *
     * @param int $policyId Policy ID
     *
     * @return null|array Policy data
     */
    public function getPolicy(int $policyId): ?array
    {
        $this->policies->loadFromSQL($policyId);
        $policy = $this->policies->getData();

        return !empty($policy) ? $policy : null;
    }

    /**
     * Get policy by name.
     *
     * @param string $policyName Policy name
     *
     * @return null|array Policy data
     */
    public function getPolicyByName(string $policyName): ?array
    {
        $policy = $this->policies->listingQuery()
            ->where('policy_name', $policyName)
            ->fetch();

        return $policy ?: null;
    }

    /**
     * Get policies by data type.
     *
     * @param string $dataType Data type
     *
     * @return array Policies
     */
    public function getPoliciesByDataType(string $dataType): array
    {
        return $this->policies->listingQuery()
            ->where('data_type', $dataType)
            ->orderBy('policy_name')
            ->fetchAll();
    }

    /**
     * Get policies by table.
     *
     * @param string $tableName Table name
     *
     * @return array Policies
     */
    public function getPoliciesByTable(string $tableName): array
    {
        return $this->policies->listingQuery()
            ->where('table_name', $tableName)
            ->orderBy('policy_name')
            ->fetchAll();
    }

    /**
     * Check if policy exists by name.
     *
     * @param string $policyName Policy name
     *
     * @return bool Policy exists
     */
    public function policyExists(string $policyName): bool
    {
        $count = $this->policies->listingQuery()
            ->where('policy_name', $policyName)
            ->count();

        return $count > 0;
    }

    /**
     * Create default policies for common data types.
     *
     * @throws \Exception
     *
     * @return array Created policy IDs
     */
    public function createDefaultPolicies(): array
    {
        $defaultPolicies = [
            [
                'policy_name' => 'user_accounts_inactive',
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => 1095, // 3 years
                'grace_period_days' => 30,
                'deletion_action' => 'anonymize',
                'legal_basis' => 'GDPR Art. 5(1)(e) - Data minimization principle',
                'description' => 'Inactive user accounts are anonymized after 3 years of inactivity',
            ],
            [
                'policy_name' => 'session_data',
                'data_type' => 'session_data',
                'table_name' => 'user_sessions',
                'retention_period_days' => 30,
                'grace_period_days' => 7,
                'deletion_action' => 'hard_delete',
                'legal_basis' => 'GDPR Art. 5(1)(e) - Data minimization principle',
                'description' => 'Session data is deleted after 30 days',
            ],
            [
                'policy_name' => 'audit_logs',
                'data_type' => 'audit_data',
                'table_name' => 'security_audit_log',
                'retention_period_days' => 2555, // 7 years
                'grace_period_days' => 90,
                'deletion_action' => 'archive',
                'legal_basis' => 'Legal and regulatory requirements for audit trail retention',
                'description' => 'Security audit logs are archived after 7 years',
            ],
        ];

        $createdPolicyIds = [];

        foreach ($defaultPolicies as $policyData) {
            try {
                if (!$this->policyExists($policyData['policy_name'])) {
                    $policyId = $this->createPolicy($policyData);
                    $createdPolicyIds[] = $policyId;
                } else {
                    $this->addStatusMessage(
                        sprintf(_('Policy "%s" already exists, skipping'), $policyData['policy_name']),
                        'info',
                    );
                }
            } catch (\Exception $e) {
                $this->addStatusMessage(
                    sprintf(_('Failed to create policy "%s": %s'), $policyData['policy_name'], $e->getMessage()),
                    'error',
                );
            }
        }

        return $createdPolicyIds;
    }

    /**
     * Validate policy configuration.
     *
     * @param array $policyData Policy data to validate
     *
     * @return array Validation results
     */
    public function validatePolicy(array $policyData): array
    {
        $errors = [];
        $warnings = [];

        // Required fields validation
        $requiredFields = ['policy_name', 'data_type', 'table_name', 'retention_period_days', 'deletion_action'];

        foreach ($requiredFields as $field) {
            if (empty($policyData[$field])) {
                $errors[] = sprintf(_('Required field missing: %s'), $field);
            }
        }

        // Policy name validation
        if (isset($policyData['policy_name'])) {
            if (\strlen($policyData['policy_name']) > 100) {
                $errors[] = _('Policy name too long (max 100 characters)');
            }

            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $policyData['policy_name'])) {
                $errors[] = _('Policy name can only contain letters, numbers, underscores, and hyphens');
            }
        }

        // Deletion action validation
        if (isset($policyData['deletion_action'])
            && !\in_array($policyData['deletion_action'], $this->validActions, true)) {
            $errors[] = sprintf(_('Invalid deletion action: %s'), $policyData['deletion_action']);
        }

        // Retention period validation
        if (isset($policyData['retention_period_days'])) {
            $retentionDays = (int) $policyData['retention_period_days'];

            if ($retentionDays < 0) {
                $errors[] = _('Retention period must be non-negative');
            }

            if ($retentionDays > 3650 && !empty($policyData['legal_basis'])) { // More than 10 years
                $warnings[] = _('Very long retention period - ensure legal basis is strong');
            }

            if ($retentionDays < 30 && $policyData['data_type'] === 'audit_data') {
                $warnings[] = _('Audit data retention period is very short - may not meet regulatory requirements');
            }
        }

        // Grace period validation
        if (isset($policyData['grace_period_days'])) {
            $gracePeriod = (int) $policyData['grace_period_days'];

            if ($gracePeriod < 0) {
                $errors[] = _('Grace period must be non-negative');
            }

            if ($gracePeriod > 90) {
                $warnings[] = _('Very long grace period may delay compliance with deletion requests');
            }
        }

        // Data type specific validations
        if (isset($policyData['data_type'])) {
            switch ($policyData['data_type']) {
                case 'user_personal_data':
                    if (isset($policyData['deletion_action']) && $policyData['deletion_action'] === 'hard_delete') {
                        $warnings[] = _('Hard deletion of personal data should be carefully considered - anonymization may be more appropriate');
                    }

                    break;
                case 'audit_data':
                    if (isset($policyData['retention_period_days']) && $policyData['retention_period_days'] < 2555) { // 7 years
                        $warnings[] = _('Audit data retention period is shorter than typical regulatory requirements (7 years)');
                    }

                    break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get policy statistics.
     *
     * @return array Statistics
     */
    public function getPolicyStatistics(): array
    {
        $allPolicies = $this->getPolicies();
        $enabledPolicies = $this->getPolicies(true);

        $stats = [
            'total_policies' => \count($allPolicies),
            'enabled_policies' => \count($enabledPolicies),
            'disabled_policies' => \count($allPolicies) - \count($enabledPolicies),
            'by_data_type' => [],
            'by_deletion_action' => [],
            'average_retention_days' => 0,
        ];

        if (!empty($allPolicies)) {
            $totalRetentionDays = 0;

            foreach ($allPolicies as $policy) {
                // Count by data type
                $dataType = $policy['data_type'];
                $stats['by_data_type'][$dataType] = ($stats['by_data_type'][$dataType] ?? 0) + 1;

                // Count by deletion action
                $action = $policy['deletion_action'];
                $stats['by_deletion_action'][$action] = ($stats['by_deletion_action'][$action] ?? 0) + 1;

                // Sum retention days
                $totalRetentionDays += $policy['retention_period_days'];
            }

            $stats['average_retention_days'] = (int) ($totalRetentionDays / \count($allPolicies));
        }

        return $stats;
    }

    /**
     * Export policies configuration.
     *
     * @param string      $format   Export format (json, csv)
     * @param null|string $filePath Optional output file path
     *
     * @throws \Exception
     *
     * @return string File path
     */
    public function exportPolicies(string $format = 'json', ?string $filePath = null): string
    {
        $policies = $this->getPolicies();

        if (empty($policies)) {
            throw new \Exception(_('No policies to export'));
        }

        if (!$filePath) {
            $timestamp = (new \DateTime())->format('Y-m-d_H-i-s');
            $filePath = sys_get_temp_dir()."/retention_policies_export_{$timestamp}.{$format}";
        }

        switch ($format) {
            case 'json':
                file_put_contents($filePath, json_encode($policies, \JSON_PRETTY_PRINT));

                break;
            case 'csv':
                self::exportPoliciesToCsv($policies, $filePath);

                break;

            default:
                throw new \Exception(sprintf(_('Unsupported export format: %s'), $format));
        }

        return $filePath;
    }

    /**
     * Import policies from configuration file.
     *
     * @param string $filePath          File path
     * @param bool   $overwriteExisting Overwrite existing policies
     *
     * @throws \Exception
     *
     * @return array Import results
     */
    public function importPolicies(string $filePath, bool $overwriteExisting = false): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception(_('Import file not found'));
        }

        $extension = strtolower(pathinfo($filePath, \PATHINFO_EXTENSION));

        switch ($extension) {
            case 'json':
                $policies = json_decode(file_get_contents($filePath), true);

                if (json_last_error() !== \JSON_ERROR_NONE) {
                    throw new \Exception(_('Invalid JSON file'));
                }

                break;

            default:
                throw new \Exception(sprintf(_('Unsupported import format: %s'), $extension));
        }

        $results = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($policies as $policyData) {
            try {
                if ($this->policyExists($policyData['policy_name'])) {
                    if ($overwriteExisting) {
                        $existingPolicy = $this->getPolicyByName($policyData['policy_name']);
                        $this->updatePolicy($existingPolicy['id'], $policyData);
                        ++$results['updated'];
                    } else {
                        ++$results['skipped'];
                    }
                } else {
                    $this->createPolicy($policyData);
                    ++$results['imported'];
                }
            } catch (\Exception $e) {
                $results['errors'][] = sprintf(
                    _('Failed to import policy "%s": %s'),
                    $policyData['policy_name'] ?? 'unknown',
                    $e->getMessage(),
                );
            }
        }

        return $results;
    }

    /**
     * Get supported data types.
     *
     * @return array Data types
     */
    public function getSupportedDataTypes(): array
    {
        return $this->supportedDataTypes;
    }

    /**
     * Get valid deletion actions.
     *
     * @return array Deletion actions
     */
    public function getValidDeletionActions(): array
    {
        return $this->validActions;
    }

    /**
     * Export policies to CSV format.
     *
     * @param array  $policies Policies to export
     * @param string $filePath Output file path
     */
    private static function exportPoliciesToCsv(array $policies, string $filePath): void
    {
        $fp = fopen($filePath, 'wb');

        // CSV header
        fputcsv($fp, [
            'Policy Name',
            'Data Type',
            'Table Name',
            'Retention Period (days)',
            'Grace Period (days)',
            'Deletion Action',
            'Legal Basis',
            'Description',
            'Enabled',
            'Created At',
        ]);

        // Data rows
        foreach ($policies as $policy) {
            fputcsv($fp, [
                $policy['policy_name'],
                $policy['data_type'],
                $policy['table_name'],
                $policy['retention_period_days'],
                $policy['grace_period_days'],
                $policy['deletion_action'],
                $policy['legal_basis'],
                $policy['description'],
                $policy['enabled'] ? 'Yes' : 'No',
                $policy['created_at'],
            ]);
        }

        fclose($fp);
    }

    /**
     * Get current user ID.
     *
     * @return null|int User ID
     */
    private static function getCurrentUserId(): ?int
    {
        if (isset($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        if (class_exists('\\Ease\\User') && method_exists('\\Ease\\User', 'singleton')) {
            $user = \Ease\User::singleton();

            if ($user && method_exists($user, 'getId')) {
                return $user->getId();
            }
        }

        // Fallback to first admin user
        $userTable = new Orm();
        $userTable->setMyTable('user');
        $adminUser = $userTable->listingQuery()
            ->where('enabled', true)
            ->orderBy('id')
            ->fetch();

        return $adminUser['id'] ?? null;
    }
}
