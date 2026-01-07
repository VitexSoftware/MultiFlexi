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
 * Data Archiver.
 *
 * Handles archival of data before deletion for GDPR compliance and audit purposes.
 * Provides secure storage and retrieval of archived data with retention controls.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DataArchiver extends \Ease\Sand
{
    /**
     * @var Orm Database handle for archive table
     */
    private Orm $archive;

    /**
     * @var User Current user performing archival
     */
    private ?User $currentUser = null;

    /**
     * Constructor.
     *
     * @param null|User $user User performing archival operations
     */
    public function __construct(?User $user = null)
    {
        parent::__construct();
        $this->archive = new Orm();
        $this->archive->setMyTable('data_archive');
        $this->currentUser = $user;
    }

    /**
     * Archive a record before deletion.
     *
     * @param string         $sourceTable    Original table name
     * @param array          $recordData     Record data to archive
     * @param string         $archiveType    Type of archive (pre_deletion, anonymization_backup, legal_hold)
     * @param string         $reason         Reason for archiving
     * @param null|int       $retentionJobId Associated retention job ID
     * @param null|\DateTime $legalHoldUntil Legal hold expiration date
     *
     * @throws \Exception
     *
     * @return int Archive record ID
     */
    public function archiveRecord(
        string $sourceTable,
        array $recordData,
        string $archiveType = 'pre_deletion',
        string $reason = '',
        ?int $retentionJobId = null,
        ?\DateTime $legalHoldUntil = null,
    ): int {
        // Validate archive type
        $validTypes = ['pre_deletion', 'anonymization_backup', 'legal_hold'];

        if (!\in_array($archiveType, $validTypes, true)) {
            throw new \Exception(sprintf(_('Invalid archive type: %s'), $archiveType));
        }

        // Get the current user ID
        $userId = $this->currentUser ? $this->currentUser->getId() : self::getCurrentUserId();

        if (!$userId) {
            throw new \Exception(_('No user available for archiving operation'));
        }

        // Prepare archive data
        $archiveData = [
            'archive_type' => $archiveType,
            'source_table' => $sourceTable,
            'source_record_id' => $recordData['id'] ?? null,
            'archived_data' => json_encode($recordData, \JSON_PRETTY_PRINT),
            'retention_job_id' => $retentionJobId,
            'archived_reason' => $reason,
            'legal_hold_until' => $legalHoldUntil,
            'archived_by' => $userId,
            'archived_at' => new \DateTime(),
        ];

        // Insert archive record
        if ($this->archive->insertToSQL($archiveData)) {
            $archiveId = $this->archive->getLastInsertID();

            $this->addStatusMessage(
                sprintf(
                    _('Archived record %s from table %s (Archive ID: %d)'),
                    $recordData['id'] ?? 'unknown',
                    $sourceTable,
                    $archiveId,
                ),
                'info',
            );

            return $archiveId;
        }

        throw new \Exception(sprintf(_('Failed to archive record from table %s'), $sourceTable));
    }

    /**
     * Archive multiple records in batch.
     *
     * @param string   $sourceTable    Original table name
     * @param array    $records        Array of record data to archive
     * @param string   $archiveType    Type of archive
     * @param string   $reason         Reason for archiving
     * @param null|int $retentionJobId Associated retention job ID
     *
     * @return array Archive results
     */
    public function archiveRecordsBatch(
        string $sourceTable,
        array $records,
        string $archiveType = 'pre_deletion',
        string $reason = '',
        ?int $retentionJobId = null,
    ): array {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($records as $record) {
            try {
                $this->archiveRecord($sourceTable, $record, $archiveType, $reason, $retentionJobId);
                ++$results['success'];
            } catch (\Exception $e) {
                ++$results['failed'];
                $results['errors'][] = sprintf(
                    _('Failed to archive record %s: %s'),
                    $record['id'] ?? 'unknown',
                    $e->getMessage(),
                );
            }
        }

        return $results;
    }

    /**
     * Retrieve archived record.
     *
     * @param int $archiveId Archive record ID
     *
     * @return null|array Archived data
     */
    public function retrieveArchivedRecord(int $archiveId): ?array
    {
        $this->archive->loadFromSQL($archiveId);
        $archiveData = $this->archive->getData();

        if (empty($archiveData)) {
            return null;
        }

        // Decode archived data
        $originalData = json_decode($archiveData['archived_data'], true);

        return [
            'archive_info' => $archiveData,
            'original_data' => $originalData,
        ];
    }

    /**
     * Search archived records by criteria.
     *
     * @param array $criteria Search criteria
     *
     * @return array Matching archived records
     */
    public function searchArchivedRecords(array $criteria): array
    {
        $query = $this->archive->listingQuery();

        // Apply search filters
        if (isset($criteria['source_table'])) {
            $query->where('source_table', $criteria['source_table']);
        }

        if (isset($criteria['archive_type'])) {
            $query->where('archive_type', $criteria['archive_type']);
        }

        if (isset($criteria['source_record_id'])) {
            $query->where('source_record_id', $criteria['source_record_id']);
        }

        if (isset($criteria['archived_by'])) {
            $query->where('archived_by', $criteria['archived_by']);
        }

        if (isset($criteria['archived_from'])) {
            $query->where('archived_at', '>=', $criteria['archived_from']);
        }

        if (isset($criteria['archived_until'])) {
            $query->where('archived_at', '<=', $criteria['archived_until']);
        }

        if (isset($criteria['retention_job_id'])) {
            $query->where('retention_job_id', $criteria['retention_job_id']);
        }

        return $query->orderBy('archived_at', 'DESC')->fetchAll();
    }

    /**
     * Get archive statistics.
     *
     * @param int $days Number of days to look back
     *
     * @return array Archive statistics
     */
    public function getArchiveStatistics(int $days = 30): array
    {
        $startDate = new \DateTime();
        $startDate->sub(new \DateInterval('P'.$days.'D'));

        $archives = $this->archive->listingQuery()
            ->where('archived_at', '>=', $startDate)
            ->fetchAll();

        $stats = [
            'total_archived' => \count($archives),
            'by_type' => [],
            'by_table' => [],
            'total_size_bytes' => 0,
        ];

        foreach ($archives as $archive) {
            // Count by type
            $type = $archive['archive_type'];
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;

            // Count by table
            $table = $archive['source_table'];
            $stats['by_table'][$table] = ($stats['by_table'][$table] ?? 0) + 1;

            // Calculate size
            $stats['total_size_bytes'] += \strlen($archive['archived_data']);
        }

        return $stats;
    }

    /**
     * Clean up expired archive records.
     *
     * @param int  $retentionDays Default retention period for archives in days
     * @param bool $dryRun        If true, only simulate cleanup
     *
     * @return array Cleanup results
     */
    public function cleanupExpiredArchives(int $retentionDays = 2555, bool $dryRun = false): array // 7 years default
    {
        $results = ['deleted' => 0, 'errors' => []];

        $expirationDate = new \DateTime();
        $expirationDate->sub(new \DateInterval('P'.$retentionDays.'D'));

        // Find expired archives (excluding those with active legal holds)
        $expiredArchives = $this->archive->listingQuery()
            ->where('archived_at', '<=', $expirationDate)
            ->where(static function ($query): void {
                $query->whereNull('legal_hold_until')
                    ->whereOr('legal_hold_until', '<=', new \DateTime());
            })
            ->fetchAll();

        foreach ($expiredArchives as $archive) {
            try {
                if (!$dryRun) {
                    $this->archive->deleteFromSQL(['id' => $archive['id']]);
                }

                ++$results['deleted'];

                $this->addStatusMessage(
                    sprintf(_('Deleted expired archive %d from table %s'), $archive['id'], $archive['source_table']),
                    'info',
                );
            } catch (\Exception $e) {
                $error = sprintf(
                    _('Failed to delete archive %d: %s'),
                    $archive['id'],
                    $e->getMessage(),
                );
                $results['errors'][] = $error;
                $this->addStatusMessage($error, 'error');
            }
        }

        return $results;
    }

    /**
     * Export archived data to file.
     *
     * @param array  $criteria Search criteria for export
     * @param string $format   Export format (json, csv)
     * @param string $filePath Output file path
     *
     * @throws \Exception
     *
     * @return bool Export success
     */
    public function exportArchivedData(array $criteria, string $format = 'json', string $filePath = ''): bool
    {
        $records = $this->searchArchivedRecords($criteria);

        if (empty($records)) {
            throw new \Exception(_('No records found matching criteria'));
        }

        // Generate file path if not provided
        if (empty($filePath)) {
            $timestamp = date('Y-m-d_H-i-s');
            $filePath = sys_get_temp_dir()."/archive_export_{$timestamp}.{$format}";
        }

        switch ($format) {
            case 'json':
                return $this->exportToJson($records, $filePath);
            case 'csv':
                return $this->exportToCsv($records, $filePath);

            default:
                throw new \Exception(sprintf(_('Unsupported export format: %s'), $format));
        }
    }

    /**
     * Set legal hold on archived records.
     *
     * @param array     $archiveIds Array of archive IDs
     * @param \DateTime $holdUntil  Legal hold expiration date
     * @param string    $reason     Reason for legal hold
     *
     * @return array Results
     */
    public function setLegalHold(array $archiveIds, \DateTime $holdUntil, string $reason = ''): array
    {
        $results = ['updated' => 0, 'errors' => []];

        foreach ($archiveIds as $archiveId) {
            try {
                $updated = $this->archive->updateToSQL([
                    'legal_hold_until' => $holdUntil,
                    'archived_reason' => $reason ?: 'Legal hold applied',
                ], ['id' => $archiveId]);

                if ($updated) {
                    ++$results['updated'];
                } else {
                    $results['errors'][] = sprintf(_('Failed to update archive %d'), $archiveId);
                }
            } catch (\Exception $e) {
                $results['errors'][] = sprintf(
                    _('Error setting legal hold on archive %d: %s'),
                    $archiveId,
                    $e->getMessage(),
                );
            }
        }

        return $results;
    }

    /**
     * Verify archive integrity.
     *
     * @param int $archiveId Archive record ID
     *
     * @return array Verification results
     */
    public function verifyArchiveIntegrity(int $archiveId): array
    {
        $archive = $this->retrieveArchivedRecord($archiveId);

        if (!$archive) {
            return ['valid' => false, 'errors' => [_('Archive record not found')]];
        }

        $errors = [];

        // Check if archived data is valid JSON
        if (json_last_error() !== \JSON_ERROR_NONE) {
            $errors[] = _('Invalid JSON in archived data');
        }

        // Check if required fields are present
        $requiredFields = ['archive_type', 'source_table', 'archived_at', 'archived_by'];

        foreach ($requiredFields as $field) {
            if (empty($archive['archive_info'][$field])) {
                $errors[] = sprintf(_('Missing required field: %s'), $field);
            }
        }

        // Check if original data contains expected structure
        if (empty($archive['original_data']) || !\is_array($archive['original_data'])) {
            $errors[] = _('Original data is empty or invalid');
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'size_bytes' => \strlen($archive['archive_info']['archived_data'] ?? ''),
            'record_count' => \is_array($archive['original_data']) ? 1 : 0,
        ];
    }

    /**
     * Export records to JSON format.
     *
     * @param array  $records  Records to export
     * @param string $filePath Output file path
     *
     * @return bool Export success
     */
    private function exportToJson(array $records, string $filePath): bool
    {
        $exportData = [];

        foreach ($records as $record) {
            $exportData[] = [
                'archive_info' => [
                    'id' => $record['id'],
                    'archive_type' => $record['archive_type'],
                    'source_table' => $record['source_table'],
                    'source_record_id' => $record['source_record_id'],
                    'archived_reason' => $record['archived_reason'],
                    'archived_at' => $record['archived_at'],
                    'archived_by' => $record['archived_by'],
                ],
                'original_data' => json_decode($record['archived_data'], true),
            ];
        }

        $result = file_put_contents($filePath, json_encode($exportData, \JSON_PRETTY_PRINT));

        if ($result !== false) {
            $this->addStatusMessage(
                sprintf(_('Exported %d records to %s'), \count($records), $filePath),
                'success',
            );

            return true;
        }

        return false;
    }

    /**
     * Export records to CSV format.
     *
     * @param array  $records  Records to export
     * @param string $filePath Output file path
     *
     * @return bool Export success
     */
    private function exportToCsv(array $records, string $filePath): bool
    {
        $fp = fopen($filePath, 'wb');

        if (!$fp) {
            return false;
        }

        // Write CSV header
        fputcsv($fp, [
            'Archive ID',
            'Archive Type',
            'Source Table',
            'Source Record ID',
            'Archived Reason',
            'Archived At',
            'Archived By',
            'Original Data',
        ]);

        // Write data rows
        foreach ($records as $record) {
            fputcsv($fp, [
                $record['id'],
                $record['archive_type'],
                $record['source_table'],
                $record['source_record_id'],
                $record['archived_reason'],
                $record['archived_at'],
                $record['archived_by'],
                $record['archived_data'],
            ]);
        }

        fclose($fp);

        $this->addStatusMessage(
            sprintf(_('Exported %d records to %s'), \count($records), $filePath),
            'success',
        );

        return true;
    }

    /**
     * Get current user ID from session or other context.
     *
     * @return null|int Current user ID
     */
    private static function getCurrentUserId(): ?int
    {
        // Try to get user from session or global context
        if (isset($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        // Try to get from EasePHP session if available
        if (class_exists('\\Ease\\User') && method_exists('\\Ease\\User', 'singleton')) {
            $user = \Ease\User::singleton();

            if ($user && method_exists($user, 'getId')) {
                return $user->getId();
            }
        }

        // Last resort: try to find a system user
        $systemUser = new Orm();
        $systemUser->setMyTable('user');
        $adminUser = $systemUser->listingQuery()
            ->where('login', 'admin')
            ->whereOr('login', 'system')
            ->orderBy('id')
            ->fetch();

        return $adminUser['id'] ?? null;
    }
}
