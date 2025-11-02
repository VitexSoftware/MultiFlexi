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

namespace MultiFlexi\DataErasure;

use MultiFlexi\User;

/**
 * GDPR Article 17 - Right of Erasure Implementation.
 *
 * Handles user account deletion requests with proper handling of:
 * - Cascading deletion for related data
 * - Legal retention requirements
 * - Data anonymization
 * - Audit trail logging
 * - Shared company data protection
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class UserDataEraser extends \Ease\Sand
{
    /**
     * @var User The user to be deleted
     */
    private User $targetUser;

    /**
     * @var User The user making the request
     */
    private User $requestingUser;
    private DeletionAuditLogger $auditLogger;

    /**
     * @var array Legal retention periods in days by data type
     */
    private array $retentionPeriods = [
        'audit_logs' => 2555, // 7 years
        'financial_records' => 3650, // 10 years
        'job_logs' => 365, // 1 year
        'login_logs' => 90, // 3 months
        'personal_data' => 0, // Can be deleted immediately
    ];

    /**
     * @var array Tables that contain user data with deletion strategies
     */
    private array $userDataTables = [
        'user' => [
            'strategy' => 'anonymize_or_delete',
            'foreign_key' => 'id',
            'personal_fields' => ['firstname', 'lastname', 'email', 'login'],
            'retention_type' => 'personal_data',
        ],
        'company' => [
            'strategy' => 'check_shared_ownership',
            'foreign_key' => 'owner',
            'personal_fields' => [],
            'retention_type' => 'business_data',
        ],
        'job' => [
            'strategy' => 'anonymize',
            'foreign_key' => 'user',
            'personal_fields' => [],
            'retention_type' => 'job_logs',
        ],
        'run_template' => [
            'strategy' => 'check_shared_usage',
            'foreign_key' => 'user',
            'personal_fields' => [],
            'retention_type' => 'business_data',
        ],
        'logger' => [
            'strategy' => 'retain',
            'foreign_key' => 'user',
            'personal_fields' => [],
            'retention_type' => 'audit_logs',
        ],
    ];

    /**
     * Constructor.
     *
     * @param User $targetUser     User to be deleted
     * @param User $requestingUser User making the request
     */
    public function __construct(User $targetUser, User $requestingUser)
    {
        parent::__construct();
        $this->targetUser = $targetUser;
        $this->requestingUser = $requestingUser;
        $this->auditLogger = new DeletionAuditLogger();
    }

    /**
     * Create a new deletion request.
     *
     * @param string $deletionType Type of deletion: 'soft', 'hard', 'anonymize'
     * @param string $reason       Reason for deletion
     * @param string $legalBasis   Legal basis for the request
     *
     * @throws \Exception
     *
     * @return int Deletion request ID
     */
    public function createDeletionRequest(
        string $deletionType = 'soft',
        string $reason = '',
        string $legalBasis = 'Art. 17 GDPR - Right of Erasure',
    ): int {
        $deletionRequest = new \Ease\SQL\Orm();
        $deletionRequest->setMyTable('user_deletion_requests');

        $requestData = [
            'user_id' => $this->targetUser->getId(),
            'requested_by_user_id' => $this->requestingUser->getId(),
            'request_date' => new \DateTime(),
            'reason' => $reason,
            'deletion_type' => $deletionType,
            'legal_basis' => $legalBasis,
            'DatCreate' => new \DateTime(),
        ];

        if ($deletionRequest->insertToSQL($requestData)) {
            $requestId = $deletionRequest->getLastInsertID();
            $this->addStatusMessage(
                sprintf(_('Deletion request %d created for user %s'), $requestId, $this->targetUser->getUserName()),
                'success',
            );

            return $requestId;
        }

        throw new \Exception(_('Failed to create deletion request'));
    }

    /**
     * Process a deletion request.
     *
     * @param int  $requestId     Deletion request ID
     * @param bool $adminApproval Whether admin approval is required
     *
     * @throws \Exception
     *
     * @return bool Success status
     */
    public function processDeletionRequest(int $requestId, bool $adminApproval = true): bool
    {
        $request = self::getDeletionRequest($requestId);

        if (!$request) {
            throw new \Exception(_('Deletion request not found'));
        }

        if ($adminApproval && $request['status'] !== 'approved') {
            $this->addStatusMessage(_('Deletion request requires admin approval'), 'warning');

            return false;
        }

        try {
            switch ($request['deletion_type']) {
                case 'soft':
                    $result = $this->performSoftDeletion($requestId);

                    break;
                case 'hard':
                    $result = $this->performHardDeletion($requestId);

                    break;
                case 'anonymize':
                    $result = $this->performAnonymization($requestId);

                    break;

                default:
                    throw new \Exception(_('Invalid deletion type'));
            }

            if ($result) {
                self::completeDeletionRequest($requestId);
            }

            return $result;
        } catch (\Exception $e) {
            $this->addStatusMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Approve a deletion request (admin function).
     *
     * @param int    $requestId Deletion request ID
     * @param User   $reviewer  Admin user approving the request
     * @param string $notes     Review notes
     *
     * @return bool Success status
     */
    public function approveDeletionRequest(int $requestId, User $reviewer, string $notes = ''): bool
    {
        $request = new \Ease\SQL\Orm();
        $request->setMyTable('user_deletion_requests');

        return $request->updateToSQL([
            'status' => 'approved',
            'reviewed_by_user_id' => $reviewer->getId(),
            'review_date' => new \DateTime(),
            'review_notes' => $notes,
            'DatSave' => new \DateTime(),
        ], ['id' => $requestId]);
    }

    /**
     * Reject a deletion request (admin function).
     *
     * @param int    $requestId Deletion request ID
     * @param User   $reviewer  Admin user rejecting the request
     * @param string $reason    Rejection reason
     *
     * @return bool Success status
     */
    public function rejectDeletionRequest(int $requestId, User $reviewer, string $reason = ''): bool
    {
        $request = new \Ease\SQL\Orm();
        $request->setMyTable('user_deletion_requests');

        return $request->updateToSQL([
            'status' => 'rejected',
            'reviewed_by_user_id' => $reviewer->getId(),
            'review_date' => new \DateTime(),
            'review_notes' => $reason,
            'DatSave' => new \DateTime(),
        ], ['id' => $requestId]);
    }

    /**
     * Get all pending deletion requests (admin function).
     *
     * @return array Pending deletion requests
     */
    public static function getPendingDeletionRequests(): array
    {
        $requests = new \Ease\SQL\Orm();
        $requests->setMyTable('user_deletion_requests');

        return $requests->listingQuery()
            ->where('status', 'pending')
            ->orderBy('request_date')
            ->fetchAll();
    }

    /**
     * Check if user can request deletion.
     *
     * @param User $user User to check
     *
     * @return array Result with 'allowed' boolean and 'reason' string
     */
    public static function canRequestDeletion(User $user): array
    {
        // Check if user already has a pending request
        $existingRequest = new \Ease\SQL\Orm();
        $existingRequest->setMyTable('user_deletion_requests');
        $pending = $existingRequest->listingQuery()
            ->where('user_id', $user->getId())
            ->where('status', ['pending', 'approved'])
            ->count();

        if ($pending > 0) {
            return [
                'allowed' => false,
                'reason' => _('User already has a pending deletion request'),
            ];
        }

        // Check if user is the last admin
        // This would need to be implemented based on your role system

        return ['allowed' => true, 'reason' => ''];
    }

    /**
     * Perform soft deletion (mark as deleted, preserve data).
     *
     * @param int $requestId Deletion request ID
     *
     * @return bool Success status
     */
    private function performSoftDeletion(int $requestId): bool
    {
        $user = $this->targetUser;

        // Mark user as deleted
        $result = $user->dbsync([
            'deleted_at' => new \DateTime(),
            'deletion_reason' => 'GDPR Article 17 - Right of Erasure',
            'enabled' => false,
        ]);

        if ($result) {
            $this->auditLogger->logDeletion(
                $requestId,
                'user',
                $user->getId(),
                'deleted',
                'Soft deletion - user marked as deleted',
            );
            $this->addStatusMessage(_('User marked as deleted'), 'success');
        }

        return $result;
    }

    /**
     * Perform hard deletion (permanently remove data).
     *
     * @param int $requestId Deletion request ID
     *
     * @throws \Exception
     *
     * @return bool Success status
     */
    private function performHardDeletion(int $requestId): bool
    {
        // Check for dependencies and shared data
        $dependencies = $this->checkDataDependencies();

        if (!empty($dependencies['blocking'])) {
            throw new \Exception(
                sprintf(
                    _('Cannot perform hard deletion due to dependencies: %s'),
                    implode(', ', $dependencies['blocking']),
                ),
            );
        }

        $deletedTables = [];

        foreach ($this->userDataTables as $tableName => $config) {
            switch ($config['strategy']) {
                case 'anonymize_or_delete':
                    if ($tableName === 'user') {
                        // For user table, delete if no dependencies, otherwise anonymize
                        if (empty($dependencies['soft'])) {
                            $this->deleteFromTable($tableName, $config, $requestId);
                            $deletedTables[] = $tableName;
                        } else {
                            $this->anonymizeTable($tableName, $config, $requestId);
                        }
                    }

                    break;
                case 'check_shared_ownership':
                    // Don't delete if other users depend on this data
                    if (!self::hasSharedOwnership($tableName, $config)) {
                        $this->deleteFromTable($tableName, $config, $requestId);
                        $deletedTables[] = $tableName;
                    } else {
                        $this->anonymizeUserReferencesInTable($tableName, $config, $requestId);
                    }

                    break;
                case 'check_shared_usage':
                    // Don't delete if used by other users
                    if (!$this->hasSharedUsage($tableName, $config)) {
                        $this->deleteFromTable($tableName, $config, $requestId);
                        $deletedTables[] = $tableName;
                    }

                    break;
                case 'anonymize':
                    $this->anonymizeTable($tableName, $config, $requestId);

                    break;
                case 'retain':
                    // Keep for legal compliance
                    $this->auditLogger->logDeletion(
                        $requestId,
                        $tableName,
                        null,
                        'retained',
                        'Retained for legal compliance',
                    );

                    break;
            }
        }

        $this->addStatusMessage(
            sprintf(_('Hard deletion completed. Tables affected: %s'), implode(', ', $deletedTables)),
            'success',
        );

        return true;
    }

    /**
     * Perform data anonymization.
     *
     * @param int $requestId Deletion request ID
     *
     * @return bool Success status
     */
    private function performAnonymization(int $requestId): bool
    {
        foreach ($this->userDataTables as $tableName => $config) {
            if (!empty($config['personal_fields']) || $config['strategy'] === 'anonymize') {
                $this->anonymizeTable($tableName, $config, $requestId);
            }
        }

        // Mark user as anonymized
        $this->targetUser->dbsync([
            'anonymized_at' => new \DateTime(),
            'enabled' => false,
        ]);

        $this->addStatusMessage(_('Data anonymization completed'), 'success');

        return true;
    }

    /**
     * Check data dependencies before deletion.
     *
     * @return array Array with 'blocking' and 'soft' dependencies
     */
    private function checkDataDependencies(): array
    {
        $dependencies = ['blocking' => [], 'soft' => []];
        $userId = $this->targetUser->getId();

        // Check if user owns companies with other users
        $companyCheck = new \Ease\SQL\Orm();
        $companyCheck->setMyTable('company');
        $ownedCompanies = $companyCheck->listingQuery()
            ->where('owner', $userId)
            ->fetchAll();

        foreach ($ownedCompanies as $company) {
            // Check if other users are associated with this company
            $userCount = $companyCheck->listingQuery()
                ->from('app_to_company atc')
                ->join('customer c', 'c.company = atc.company_id')
                ->where('atc.company_id', $company['id'])
                ->where('c.user !=', $userId)
                ->count();

            if ($userCount > 0) {
                $dependencies['blocking'][] = sprintf(
                    _('Company %s has other users'),
                    $company['name'] ?? $company['id'],
                );
            }
        }

        // Check for shared run templates
        $templateCheck = new \Ease\SQL\Orm();
        $templateCheck->setMyTable('run_template');
        $sharedTemplates = $templateCheck->listingQuery()
            ->where('user', $userId)
            ->where('public', true)
            ->count();

        if ($sharedTemplates > 0) {
            $dependencies['soft'][] = _('User has public run templates');
        }

        return $dependencies;
    }

    /**
     * Delete records from a table.
     *
     * @param string $tableName Table name
     * @param array  $config    Table configuration
     * @param int    $requestId Deletion request ID
     */
    private function deleteFromTable(string $tableName, array $config, int $requestId): void
    {
        $table = new \Ease\SQL\Orm();
        $table->setMyTable($tableName);

        $records = $table->listingQuery()
            ->where($config['foreign_key'], $this->targetUser->getId())
            ->fetchAll();

        foreach ($records as $record) {
            $this->auditLogger->logDeletion(
                $requestId,
                $tableName,
                $record['id'] ?? null,
                'deleted',
                'Hard deletion',
                json_encode($record),
            );

            $table->deleteFromSQL(['id' => $record['id']]);
        }
    }

    /**
     * Anonymize personal data in a table.
     *
     * @param string $tableName Table name
     * @param array  $config    Table configuration
     * @param int    $requestId Deletion request ID
     */
    private function anonymizeTable(string $tableName, array $config, int $requestId): void
    {
        $table = new \Ease\SQL\Orm();
        $table->setMyTable($tableName);

        $records = $table->listingQuery()
            ->where($config['foreign_key'], $this->targetUser->getId())
            ->fetchAll();

        foreach ($records as $record) {
            $originalData = json_encode($record);
            $anonymizedData = [];

            foreach ($config['personal_fields'] as $field) {
                if (isset($record[$field])) {
                    $anonymizedData[$field] = self::anonymizeField($field, $record[$field]);
                }
            }

            if (!empty($anonymizedData)) {
                $table->updateToSQL($anonymizedData, ['id' => $record['id']]);

                $this->auditLogger->logDeletion(
                    $requestId,
                    $tableName,
                    $record['id'] ?? null,
                    'anonymized',
                    'Personal data anonymized',
                    $originalData,
                    json_encode(array_merge($record, $anonymizedData)),
                );
            }
        }
    }

    /**
     * Anonymize user references in shared data.
     *
     * @param string $tableName Table name
     * @param array  $config    Table configuration
     * @param int    $requestId Deletion request ID
     */
    private function anonymizeUserReferencesInTable(string $tableName, array $config, int $requestId): void
    {
        $table = new \Ease\SQL\Orm();
        $table->setMyTable($tableName);

        // Set foreign key to null or anonymous user ID
        $table->updateToSQL(
            [$config['foreign_key'] => null],
            [$config['foreign_key'] => $this->targetUser->getId()],
        );

        $this->auditLogger->logDeletion(
            $requestId,
            $tableName,
            null,
            'anonymized',
            'User references anonymized',
        );
    }

    /**
     * Check if user has shared ownership of data.
     *
     * @param string $tableName Table name
     * @param array  $config    Table configuration
     *
     * @return bool True if shared ownership exists
     */
    private static function hasSharedOwnership(string $tableName, array $config): bool
    {
        // Implementation depends on specific business logic
        // This is a simplified version
        return false;
    }

    /**
     * Check if data has shared usage.
     *
     * @param string $tableName Table name
     * @param array  $config    Table configuration
     *
     * @return bool True if shared usage exists
     */
    private function hasSharedUsage(string $tableName, array $config): bool
    {
        $table = new \Ease\SQL\Orm();
        $table->setMyTable($tableName);

        // Check if any records are marked as public or shared
        $sharedRecords = $table->listingQuery()
            ->where($config['foreign_key'], $this->targetUser->getId())
            ->where('public', true)
            ->count();

        return $sharedRecords > 0;
    }

    /**
     * Anonymize a field value.
     *
     * @param string $fieldName Field name
     * @param mixed  $value     Original value
     *
     * @return string Anonymized value
     */
    private static function anonymizeField(string $fieldName, $value): string
    {
        switch ($fieldName) {
            case 'email':
                return 'anonymized@deleted.user';
            case 'firstname':
            case 'lastname':
                return 'Anonymized';
            case 'login':
                return 'deleted_user_'.uniqid();

            default:
                return '[ANONYMIZED]';
        }
    }

    /**
     * Get deletion request details.
     *
     * @param int $requestId Deletion request ID
     *
     * @return null|array Request details
     */
    private static function getDeletionRequest(int $requestId): ?array
    {
        $request = new \Ease\SQL\Orm();
        $request->setMyTable('user_deletion_requests');
        $request->loadFromSQL($requestId);

        return $request->getData() ?: null;
    }

    /**
     * Mark deletion request as completed.
     *
     * @param int $requestId Deletion request ID
     */
    private static function completeDeletionRequest(int $requestId): void
    {
        $request = new \Ease\SQL\Orm();
        $request->setMyTable('user_deletion_requests');
        $request->updateToSQL([
            'status' => 'completed',
            'completed_date' => new \DateTime(),
            'DatSave' => new \DateTime(),
        ], ['id' => $requestId]);
    }
}
