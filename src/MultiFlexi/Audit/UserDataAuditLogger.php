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

namespace MultiFlexi\Audit;

/**
 * User Data Audit Logger for GDPR compliance.
 *
 * Logs all user personal data modifications for Article 16 compliance
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class UserDataAuditLogger extends \MultiFlexi\DBEngine
{
    /**
     * Constructor.
     *
     * @param mixed $identifier Record identifier
     */
    public function __construct($identifier = null)
    {
        $this->myTable = 'user_data_audit';
        $this->createColumn = 'created_at';
        parent::__construct($identifier);
    }

    /**
     * Log user data change.
     *
     * @param int         $userId          User ID whose data was changed
     * @param string      $field           Field name that was changed
     * @param mixed       $oldValue        Old value (will be JSON encoded if array/object)
     * @param mixed       $newValue        New value (will be JSON encoded if array/object)
     * @param string      $changeType      Type of change: 'direct', 'pending_approval', 'approved', 'rejected'
     * @param null|int    $changedByUserId ID of user who made the change (null for self-changes)
     * @param null|string $ipAddress       IP address from where change was made
     * @param null|string $userAgent       User agent string
     * @param null|string $reason          Reason for the change (for admin changes)
     *
     * @return bool Success of logging
     */
    public function logDataChange(
        int $userId,
        string $field,
        $oldValue,
        $newValue,
        string $changeType = 'direct',
        ?int $changedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $reason = null,
    ): bool {
        $logData = [
            'user_id' => $userId,
            'field_name' => $field,
            'old_value' => \is_array($oldValue) || \is_object($oldValue) ? json_encode($oldValue) : (string) $oldValue,
            'new_value' => \is_array($newValue) || \is_object($newValue) ? json_encode($newValue) : (string) $newValue,
            'change_type' => $changeType,
            'changed_by_user_id' => $changedByUserId,
            'ip_address' => $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? null),
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->setData($logData);

        return $this->dbsync();
    }

    /**
     * Get audit log for specific user.
     *
     * @param int $userId User ID
     * @param int $limit  Maximum number of records to return
     * @param int $offset Offset for pagination
     *
     * @return array Array of audit log entries
     */
    public function getUserAuditLog(int $userId, int $limit = 50, int $offset = 0): array
    {
        $result = $this->getFluentPDO()->from($this->myTable)
            ->where('user_id', $userId)
            ->orderBy('created_at DESC')
            ->limit($limit)
            ->offset($offset)
            ->fetchAll();
        
        return $result ?: [];
    }

    /**
     * Get recent data changes pending approval.
     *
     * @param int $limit Maximum number of records to return
     *
     * @return array Array of pending changes
     */
    public function getPendingApprovals(int $limit = 20): array
    {
        $result = $this->getFluentPDO()->from($this->myTable)
            ->where('change_type', 'pending_approval')
            ->orderBy('created_at DESC')
            ->limit($limit)
            ->fetchAll();
        
        return $result ?: [];
    }

    /**
     * Get audit statistics for reporting.
     *
     * @param string $fromDate Start date (Y-m-d format)
     * @param string $toDate   End date (Y-m-d format)
     *
     * @return array Statistics array
     */
    public function getAuditStatistics(string $fromDate, string $toDate): array
    {
        $stats = $this->getFluentPDO()->from($this->myTable)
            ->select('change_type')
            ->select('COUNT(*) as count')
            ->select('COUNT(DISTINCT user_id) as unique_users')
            ->where('created_at >= ?', $fromDate.' 00:00:00')
            ->where('created_at <= ?', $toDate.' 23:59:59')
            ->groupBy('change_type')
            ->fetchAll();

        $result = [
            'total_changes' => 0,
            'unique_users_affected' => 0,
            'by_type' => [],
        ];

        foreach ($stats as $stat) {
            $result['total_changes'] += $stat['count'];
            $result['by_type'][$stat['change_type']] = $stat['count'];
        }

        // Get unique users count across all change types
        $uniqueUsersResult = $this->getFluentPDO()->from($this->myTable)
            ->select('COUNT(DISTINCT user_id) as unique_users')
            ->where('created_at >= ?', $fromDate.' 00:00:00')
            ->where('created_at <= ?', $toDate.' 23:59:59')
            ->fetch();

        $result['unique_users_affected'] = $uniqueUsersResult['unique_users'] ?? 0;

        return $result;
    }
}
