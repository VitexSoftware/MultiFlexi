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
 * Deletion Audit Logger.
 *
 * Logs all deletion operations for GDPR compliance
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DeletionAuditLogger extends \Ease\Sand
{
    /**
     * @var \Ease\SQL\Orm Audit table ORM
     */
    private \Ease\SQL\Orm $auditTable;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->auditTable = new \Ease\SQL\Orm();
        $this->auditTable->setMyTable('user_deletion_audit');
    }

    /**
     * Log a deletion operation.
     *
     * @param int         $deletionRequestId Deletion request ID
     * @param string      $tableName         Table name affected
     * @param null|int    $recordId          Record ID (null for bulk operations)
     * @param string      $action            Action performed: 'deleted', 'anonymized', 'retained'
     * @param string      $reason            Reason for the action
     * @param null|string $dataBefore        JSON of data before operation
     * @param null|string $dataAfter         JSON of data after operation
     *
     * @return bool Success status
     */
    public function logDeletion(
        int $deletionRequestId,
        string $tableName,
        ?int $recordId,
        string $action,
        string $reason,
        ?string $dataBefore = null,
        ?string $dataAfter = null,
    ): bool {
        $logData = [
            'deletion_request_id' => $deletionRequestId,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'action' => $action,
            'reason' => $reason,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'performed_by_user_id' => User::singleton()->getId(),
            'performed_date' => new \DateTime(),
        ];

        $result = $this->auditTable->insertToSQL($logData);

        if ($result) {
            $this->addStatusMessage(
                sprintf(
                    _('Audit log created: %s on %s (ID: %s)'),
                    $action,
                    $tableName,
                    $recordId ?: 'bulk',
                ),
                'debug',
            );
        } else {
            $this->addStatusMessage(
                sprintf(_('Failed to create audit log for %s on %s'), $action, $tableName),
                'error',
            );
        }

        return $result;
    }

    /**
     * Get audit trail for a deletion request.
     *
     * @param int $deletionRequestId Deletion request ID
     *
     * @return array Audit trail entries
     */
    public function getAuditTrail(int $deletionRequestId): array
    {
        return $this->auditTable->listingQuery()
            ->where('deletion_request_id', $deletionRequestId)
            ->orderBy('performed_date')
            ->fetchAll();
    }

    /**
     * Get audit statistics for a time period.
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate   End date
     *
     * @return array Statistics by action type
     */
    public function getAuditStatistics(\DateTime $startDate, \DateTime $endDate): array
    {
        $query = $this->auditTable->listingQuery()
            ->select(['action', 'COUNT(*) as count'])
            ->where('performed_date >=', $startDate)
            ->where('performed_date <=', $endDate)
            ->groupBy('action');

        $results = $query->fetchAll();
        $statistics = [
            'deleted' => 0,
            'anonymized' => 0,
            'retained' => 0,
        ];

        foreach ($results as $result) {
            $statistics[$result['action']] = (int) $result['count'];
        }

        return $statistics;
    }

    /**
     * Export audit trail as CSV.
     *
     * @param int $deletionRequestId Deletion request ID
     *
     * @return string CSV content
     */
    public function exportAuditTrailAsCsv(int $deletionRequestId): string
    {
        $auditTrail = $this->getAuditTrail($deletionRequestId);

        $csv = "Date,Table,Record ID,Action,Reason,Performed By\n";

        foreach ($auditTrail as $entry) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $entry['performed_date'],
                $entry['table_name'],
                $entry['record_id'] ?: 'N/A',
                $entry['action'],
                '"'.str_replace('"', '""', $entry['reason']).'"',
                $entry['performed_by_user_id'],
            );
        }

        return $csv;
    }

    /**
     * Cleanup old audit logs based on retention policy.
     *
     * @param int $retentionDays Number of days to retain audit logs
     *
     * @return int Number of deleted records
     */
    public function cleanupOldAuditLogs(int $retentionDays = 2555): int // Default 7 years
    {
        $cutoffDate = new \DateTime();
        $cutoffDate->sub(new \DateInterval("P{$retentionDays}D"));

        $deletedCount = $this->auditTable->deleteFromSQL([
            'performed_date <' => $cutoffDate,
        ]);

        $this->addStatusMessage(
            sprintf(_('Cleaned up %d old audit log entries'), $deletedCount),
            'info',
        );

        return $deletedCount;
    }

    /**
     * Verify audit trail integrity.
     *
     * @param int $deletionRequestId Deletion request ID
     *
     * @return array Verification results
     */
    public function verifyAuditTrailIntegrity(int $deletionRequestId): array
    {
        $auditTrail = $this->getAuditTrail($deletionRequestId);
        $verification = [
            'complete' => true,
            'issues' => [],
            'entry_count' => \count($auditTrail),
        ];

        // Check for required entries
        $expectedActions = ['deleted', 'anonymized', 'retained'];
        $foundActions = array_column($auditTrail, 'action');

        foreach ($expectedActions as $action) {
            if (!\in_array($action, $foundActions, true)) {
                $verification['issues'][] = sprintf(_('Missing %s action entries'), $action);
            }
        }

        // Check for proper sequencing
        $lastDate = null;

        foreach ($auditTrail as $entry) {
            $currentDate = new \DateTime($entry['performed_date']);

            if ($lastDate && $currentDate < $lastDate) {
                $verification['issues'][] = _('Entries are not in chronological order');

                break;
            }

            $lastDate = $currentDate;
        }

        $verification['complete'] = empty($verification['issues']);

        return $verification;
    }
}
