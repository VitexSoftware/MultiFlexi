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
 * GDPR Data Retention Service.
 *
 * Handles automated data retention and cleanup according to defined policies.
 * Implements GDPR Article 5(1)(e) - Data minimization principle.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class RetentionService extends \Ease\Sand
{
    /**
     * @var Orm Database handle for retention policies
     */
    private Orm $retentionPolicies;

    /**
     * @var Orm Database handle for cleanup jobs
     */
    private Orm $cleanupJobs;

    /**
     * @var DataArchiver Data archiver instance
     */
    private DataArchiver $archiver;

    /**
     * @var array Supported table configurations for cleanup
     */
    private array $tableConfigurations = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->retentionPolicies = new Orm();
        $this->retentionPolicies->setMyTable('data_retention_policies');

        $this->cleanupJobs = new Orm();
        $this->cleanupJobs->setMyTable('retention_cleanup_jobs');

        $this->archiver = new DataArchiver();
        $this->initializeTableConfigurations();
    }

    /**
     * Calculate retention expiration dates for all records.
     *
     * @return array Summary of updates
     */
    public function calculateRetentionDates(): array
    {
        $summary = ['updated_tables' => 0, 'updated_records' => 0, 'errors' => []];

        $policies = $this->getActivePolicies();

        foreach ($policies as $policy) {
            try {
                $updated = $this->calculateRetentionForPolicy($policy);
                ++$summary['updated_tables'];
                $summary['updated_records'] += $updated;

                $this->addStatusMessage(
                    sprintf(_('Updated %d records for policy %s'), $updated, $policy['policy_name']),
                    'info',
                );
            } catch (\Exception $e) {
                $error = sprintf(_('Error processing policy %s: %s'), $policy['policy_name'], $e->getMessage());
                $summary['errors'][] = $error;
                $this->addStatusMessage($error, 'error');
            }
        }

        return $summary;
    }

    /**
     * Process scheduled cleanup jobs.
     *
     * @param bool $dryRun If true, only simulate the cleanup without actual changes
     *
     * @return array Cleanup summary
     */
    public function processScheduledCleanup(bool $dryRun = false): array
    {
        $summary = [
            'jobs_processed' => 0,
            'records_deleted' => 0,
            'records_anonymized' => 0,
            'records_archived' => 0,
            'errors' => [],
        ];

        $expiredRecords = $this->findExpiredRecords();

        foreach ($expiredRecords as $tableName => $records) {
            if (empty($records)) {
                continue;
            }

            try {
                $policy = $this->getPolicyForTable($tableName);

                if (!$policy) {
                    continue;
                }

                $jobId = $this->createCleanupJob($policy['id'], 'scheduled_cleanup');
                $this->updateJobStatus($jobId, 'running');

                $result = $this->processRecordsForCleanup($policy, $records, $jobId, $dryRun);

                ++$summary['jobs_processed'];
                $summary['records_deleted'] += $result['deleted'];
                $summary['records_anonymized'] += $result['anonymized'];
                $summary['records_archived'] += $result['archived'];

                $this->completeCleanupJob($jobId, $result);
            } catch (\Exception $e) {
                $error = sprintf(_('Error processing cleanup for table %s: %s'), $tableName, $e->getMessage());
                $summary['errors'][] = $error;
                $this->addStatusMessage($error, 'error');

                if (isset($jobId)) {
                    $this->updateJobStatus($jobId, 'failed', $e->getMessage());
                }
            }
        }

        return $summary;
    }

    /**
     * Find records that have expired according to retention policies.
     *
     * @return array Records grouped by table name
     */
    public function findExpiredRecords(): array
    {
        $expiredRecords = [];
        $policies = $this->getActivePolicies();

        foreach ($policies as $policy) {
            $tableName = $policy['table_name'];

            if (!isset($this->tableConfigurations[$tableName])) {
                continue;
            }

            $config = $this->tableConfigurations[$tableName];
            $table = new Orm();
            $table->setMyTable($tableName);

            // Find records past their retention period
            $records = $table->listingQuery()
                ->where('retention_until', '<=', new \DateTime())
                ->where('marked_for_deletion', false)
                ->orderBy($config['date_field'])
                ->fetchAll();

            if (!empty($records)) {
                $expiredRecords[$tableName] = $records;
            }
        }

        return $expiredRecords;
    }

    /**
     * Process grace period cleanup (final deletion after grace period).
     *
     * @param bool $dryRun If true, only simulate the cleanup
     *
     * @return array Cleanup summary
     */
    public function processGracePeriodCleanup(bool $dryRun = false): array
    {
        $summary = [
            'records_processed' => 0,
            'records_deleted' => 0,
            'errors' => [],
        ];

        $policies = $this->getActivePolicies();

        foreach ($policies as $policy) {
            $tableName = $policy['table_name'];

            if (!isset($this->tableConfigurations[$tableName])) {
                continue;
            }

            $table = new Orm();
            $table->setMyTable($tableName);

            // Find records marked for deletion past grace period
            $gracePeriodEnd = new \DateTime();
            $gracePeriodEnd->sub(new \DateInterval('P'.$policy['grace_period_days'].'D'));

            $records = $table->listingQuery()
                ->where('marked_for_deletion', true)
                ->where('retention_until', '<=', $gracePeriodEnd)
                ->fetchAll();

            foreach ($records as $record) {
                try {
                    if (!$dryRun) {
                        // Archive before final deletion
                        $this->archiver->archiveRecord(
                            $tableName,
                            $record,
                            'pre_deletion',
                            'Grace period final deletion',
                        );

                        // Perform final deletion
                        $table->deleteFromSQL([$this->tableConfigurations[$tableName]['id_field'] => $record['id']]);
                    }

                    ++$summary['records_deleted'];
                } catch (\Exception $e) {
                    $error = sprintf(
                        _('Error deleting record %d from %s: %s'),
                        $record['id'],
                        $tableName,
                        $e->getMessage(),
                    );
                    $summary['errors'][] = $error;
                    $this->addStatusMessage($error, 'error');
                }

                ++$summary['records_processed'];
            }
        }

        return $summary;
    }

    /**
     * Get all active retention policies.
     *
     * @return array Active policies
     */
    public function getActivePolicies(): array
    {
        return $this->retentionPolicies->listingQuery()
            ->where('enabled', true)
            ->orderBy('table_name')
            ->fetchAll();
    }

    /**
     * Get retention policy for a specific table.
     *
     * @param string $tableName Table name
     *
     * @return null|array Policy configuration
     */
    public function getPolicyForTable(string $tableName): ?array
    {
        $policy = $this->retentionPolicies->listingQuery()
            ->where('table_name', $tableName)
            ->where('enabled', true)
            ->fetch();

        return $policy ?: null;
    }

    /**
     * Create a new cleanup job.
     *
     * @param int       $policyId Policy ID
     * @param string    $jobType  Job type
     * @param null|User $user     User starting the job
     *
     * @return int Job ID
     */
    public function createCleanupJob(int $policyId, string $jobType, ?User $user = null): int
    {
        $jobData = [
            'policy_id' => $policyId,
            'job_type' => $jobType,
            'started_by' => $user ? $user->getId() : null,
            'created_at' => new \DateTime(),
        ];

        $this->cleanupJobs->insertToSQL($jobData);

        return $this->cleanupJobs->getLastInsertID();
    }

    /**
     * Update cleanup job status.
     *
     * @param int         $jobId  Job ID
     * @param string      $status New status
     * @param null|string $error  Error message if applicable
     */
    public function updateJobStatus(int $jobId, string $status, ?string $error = null): void
    {
        $updateData = ['status' => $status];

        if ($status === 'running' && !$error) {
            $updateData['started_at'] = new \DateTime();
        } elseif (\in_array($status, ['completed', 'failed', 'cancelled'], true)) {
            $updateData['completed_at'] = new \DateTime();

            if ($error) {
                $updateData['errors'] = json_encode(['error' => $error]);
            }
        }

        $this->cleanupJobs->updateToSQL($updateData, ['id' => $jobId]);
    }

    /**
     * Complete a cleanup job with results.
     *
     * @param int   $jobId  Job ID
     * @param array $result Job results
     */
    public function completeCleanupJob(int $jobId, array $result): void
    {
        $updateData = [
            'status' => 'completed',
            'completed_at' => new \DateTime(),
            'records_processed' => $result['deleted'] + $result['anonymized'] + $result['archived'],
            'records_deleted' => $result['deleted'],
            'records_anonymized' => $result['anonymized'],
            'records_archived' => $result['archived'],
            'summary' => sprintf(
                _('Processed: %d, Deleted: %d, Anonymized: %d, Archived: %d'),
                $result['deleted'] + $result['anonymized'] + $result['archived'],
                $result['deleted'],
                $result['anonymized'],
                $result['archived'],
            ),
        ];

        if (!empty($result['errors'])) {
            $updateData['errors'] = json_encode($result['errors']);
        }

        $this->cleanupJobs->updateToSQL($updateData, ['id' => $jobId]);
    }

    /**
     * Get cleanup job statistics.
     *
     * @param int $days Number of days to look back
     *
     * @return array Statistics
     */
    public function getCleanupStatistics(int $days = 30): array
    {
        $startDate = new \DateTime();
        $startDate->sub(new \DateInterval('P'.$days.'D'));

        $jobs = $this->cleanupJobs->listingQuery()
            ->where('created_at', '>=', $startDate)
            ->fetchAll();

        $stats = [
            'total_jobs' => \count($jobs),
            'completed_jobs' => 0,
            'failed_jobs' => 0,
            'total_records_processed' => 0,
            'total_records_deleted' => 0,
            'total_records_anonymized' => 0,
            'total_records_archived' => 0,
        ];

        foreach ($jobs as $job) {
            if ($job['status'] === 'completed') {
                ++$stats['completed_jobs'];
            } elseif ($job['status'] === 'failed') {
                ++$stats['failed_jobs'];
            }

            $stats['total_records_processed'] += $job['records_processed'] ?? 0;
            $stats['total_records_deleted'] += $job['records_deleted'] ?? 0;
            $stats['total_records_anonymized'] += $job['records_anonymized'] ?? 0;
            $stats['total_records_archived'] += $job['records_archived'] ?? 0;
        }

        return $stats;
    }

    /**
     * Update user activity timestamp.
     *
     * @param int $userId User ID
     */
    public static function updateUserActivity(int $userId): void
    {
        $user = new Orm();
        $user->setMyTable('user');
        $user->updateToSQL(
            ['last_activity_at' => new \DateTime()],
            ['id' => $userId],
        );
    }

    /**
     * Mark inactive users.
     *
     * @param int $inactivityDays Days of inactivity threshold
     *
     * @return int Number of users marked as inactive
     */
    public function markInactiveUsers(int $inactivityDays = 90): int
    {
        $inactivityDate = new \DateTime();
        $inactivityDate->sub(new \DateInterval('P'.$inactivityDays.'D'));

        $user = new Orm();
        $user->setMyTable('user');

        $result = $user->updateToSQL(
            ['inactive_since' => new \DateTime()],
            [
                'last_activity_at' => ['<=', $inactivityDate],
                'inactive_since' => null,
                'enabled' => true,
            ],
        );

        return $result !== false ? $result : 0;
    }

    /**
     * Initialize table configurations for cleanup operations.
     */
    private function initializeTableConfigurations(): void
    {
        $this->tableConfigurations = [
            'user' => [
                'date_field' => 'last_activity_at',
                'id_field' => 'id',
                'personal_fields' => ['firstname', 'lastname', 'email', 'login'],
                'anonymization_strategy' => 'user_data',
            ],
            'user_sessions' => [
                'date_field' => 'last_activity',
                'id_field' => 'id',
                'personal_fields' => ['ip_address', 'user_agent'],
                'anonymization_strategy' => 'session_data',
            ],
            'security_audit_log' => [
                'date_field' => 'created_at',
                'id_field' => 'id',
                'personal_fields' => ['ip_address', 'user_agent', 'additional_data'],
                'anonymization_strategy' => 'audit_data',
            ],
            'job' => [
                'date_field' => 'begin',
                'id_field' => 'id',
                'personal_fields' => [],
                'anonymization_strategy' => 'job_data',
            ],
            'log' => [
                'date_field' => 'created',
                'id_field' => 'id',
                'personal_fields' => ['message'],
                'anonymization_strategy' => 'log_data',
            ],
            'company' => [
                'date_field' => 'DatSave',
                'id_field' => 'id',
                'personal_fields' => ['name', 'contact', 'email'],
                'anonymization_strategy' => 'company_data',
            ],
            'login_attempts' => [
                'date_field' => 'attempt_time',
                'id_field' => 'id',
                'personal_fields' => ['ip_address', 'username', 'user_agent'],
                'anonymization_strategy' => 'security_data',
            ],
        ];
    }

    /**
     * Calculate retention dates for a specific policy.
     *
     * @param array $policy Policy configuration
     *
     * @throws \Exception
     *
     * @return int Number of updated records
     */
    private function calculateRetentionForPolicy(array $policy): int
    {
        $tableName = $policy['table_name'];

        if (!isset($this->tableConfigurations[$tableName])) {
            throw new \Exception(sprintf(_('Table %s not configured for retention'), $tableName));
        }

        $config = $this->tableConfigurations[$tableName];
        $table = new Orm();
        $table->setMyTable($tableName);

        // Calculate retention until date
        $retentionDate = new \DateTime();
        $retentionDate->sub(new \DateInterval('P'.$policy['retention_period_days'].'D'));

        // Update records that don't have retention_until set
        $sql = sprintf(
            <<<'EOD'
UPDATE %s SET retention_until = DATE_ADD(%s, INTERVAL %d DAY)
             WHERE retention_until IS NULL AND %s IS NOT NULL
EOD,
            $tableName,
            $config['date_field'],
            $policy['retention_period_days'],
            $config['date_field'],
        );

        $result = $table->getDBLink()->exec($sql);

        return $result !== false ? $result : 0;
    }

    /**
     * Process records for cleanup according to policy.
     *
     * @param array $policy  Policy configuration
     * @param array $records Records to process
     * @param int   $jobId   Cleanup job ID
     * @param bool  $dryRun  If true, only simulate
     *
     * @return array Processing results
     */
    private function processRecordsForCleanup(array $policy, array $records, int $jobId, bool $dryRun = false): array
    {
        $result = ['deleted' => 0, 'anonymized' => 0, 'archived' => 0, 'errors' => []];
        $tableName = $policy['table_name'];
        $config = $this->tableConfigurations[$tableName];

        $table = new Orm();
        $table->setMyTable($tableName);

        foreach ($records as $record) {
            try {
                switch ($policy['deletion_action']) {
                    case 'hard_delete':
                        if (!$dryRun) {
                            // Archive before deletion
                            $this->archiver->archiveRecord($tableName, $record, 'pre_deletion', 'Hard deletion cleanup', $jobId);

                            // Delete record
                            $table->deleteFromSQL([$config['id_field'] => $record[$config['id_field']]]);
                        }

                        ++$result['deleted'];

                        break;
                    case 'soft_delete':
                        if (!$dryRun) {
                            // Mark for deletion (grace period)
                            $table->updateToSQL(
                                ['marked_for_deletion' => true],
                                [$config['id_field'] => $record[$config['id_field']]],
                            );
                        }

                        ++$result['deleted'];

                        break;
                    case 'anonymize':
                        if (!$dryRun) {
                            // Archive original data
                            $this->archiver->archiveRecord($tableName, $record, 'anonymization_backup', 'Data anonymization', $jobId);

                            // Anonymize personal fields
                            $anonymizedData = self::anonymizeRecord($record, $config);
                            $table->updateToSQL($anonymizedData, [$config['id_field'] => $record[$config['id_field']]]);
                        }

                        ++$result['anonymized'];

                        break;
                    case 'archive':
                        if (!$dryRun) {
                            // Archive and mark for deletion
                            $this->archiver->archiveRecord($tableName, $record, 'legal_hold', 'Legal retention archive', $jobId);
                            $table->updateToSQL(
                                ['marked_for_deletion' => true],
                                [$config['id_field'] => $record[$config['id_field']]],
                            );
                        }

                        ++$result['archived'];

                        break;
                }
            } catch (\Exception $e) {
                $result['errors'][] = sprintf(
                    _('Error processing record %d: %s'),
                    $record[$config['id_field']] ?? 'unknown',
                    $e->getMessage(),
                );
            }
        }

        return $result;
    }

    /**
     * Anonymize a record according to its configuration.
     *
     * @param array $record Original record
     * @param array $config Table configuration
     *
     * @return array Anonymized data
     */
    private static function anonymizeRecord(array $record, array $config): array
    {
        $anonymizedData = [];

        foreach ($config['personal_fields'] as $field) {
            if (!isset($record[$field]) || $record[$field] === null) {
                continue;
            }

            switch ($field) {
                case 'email':
                    $anonymizedData[$field] = 'anonymized@deleted.user';

                    break;
                case 'firstname':
                case 'lastname':
                case 'name':
                    $anonymizedData[$field] = '[ANONYMIZED]';

                    break;
                case 'login':
                case 'username':
                    $anonymizedData[$field] = 'deleted_user_'.uniqid();

                    break;
                case 'ip_address':
                    $anonymizedData[$field] = '0.0.0.0';

                    break;
                case 'user_agent':
                    $anonymizedData[$field] = '[ANONYMIZED]';

                    break;
                case 'message':
                    $anonymizedData[$field] = '[LOG MESSAGE ANONYMIZED]';

                    break;
                case 'contact':
                    $anonymizedData[$field] = '[CONTACT ANONYMIZED]';

                    break;
                case 'additional_data':
                    $anonymizedData[$field] = json_encode(['anonymized' => true]);

                    break;

                default:
                    $anonymizedData[$field] = '[ANONYMIZED]';
            }
        }

        // Add anonymization timestamp
        if (isset($record['anonymized_at'])) {
            $anonymizedData['anonymized_at'] = new \DateTime();
        }

        return $anonymizedData;
    }
}
