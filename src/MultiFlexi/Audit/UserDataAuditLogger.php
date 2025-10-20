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

use Ease\SQL\Orm;

/**
 * User Data Audit Logger for GDPR compliance
 * 
 * Logs all user personal data modifications for Article 16 compliance
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class UserDataAuditLogger extends \Ease\Sand
{
    use Orm;

    /**
     * Database table name
     */
    public string $myTable = 'user_data_audit';

    /**
     * Primary key column
     */
    public string $keyColumn = 'id';

    /**
     * Creation timestamp column
     */
    public string $createColumn = 'created_at';

    /**
     * Log user data change
     *
     * @param int $userId User ID whose data was changed
     * @param string $field Field name that was changed
     * @param mixed $oldValue Old value (will be JSON encoded if array/object)
     * @param mixed $newValue New value (will be JSON encoded if array/object)
     * @param string $changeType Type of change: 'direct', 'pending_approval', 'approved', 'rejected'
     * @param int|null $changedByUserId ID of user who made the change (null for self-changes)
     * @param string|null $ipAddress IP address from where change was made
     * @param string|null $userAgent User agent string
     * @param string|null $reason Reason for the change (for admin changes)
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
        ?string $reason = null
    ): bool {
        $logData = [
            'user_id' => $userId,
            'field_name' => $field,
            'old_value' => is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue) : (string) $oldValue,
            'new_value' => is_array($newValue) || is_object($newValue) ? json_encode($newValue) : (string) $newValue,
            'change_type' => $changeType,
            'changed_by_user_id' => $changedByUserId,
            'ip_address' => $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? null),
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->setData($logData);
        return $this->dbsync();
    }

    /**
     * Get audit log for specific user
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of records to return
     * @param int $offset Offset for pagination
     *
     * @return array Array of audit log entries
     */
    public function getUserAuditLog(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->listingQuery()
            ->select(['*'])
            ->where('user_id = %i', $userId)
            ->orderBy('created_at DESC')
            ->limit('%i OFFSET %i', $limit, $offset)
            ->fetchAll();
    }

    /**
     * Get recent data changes pending approval
     *
     * @param int $limit Maximum number of records to return
     *
     * @return array Array of pending changes
     */
    public function getPendingApprovals(int $limit = 20): array
    {
        return $this->listingQuery()
            ->select(['*'])
            ->where('change_type = %s', 'pending_approval')
            ->orderBy('created_at DESC')
            ->limit('%i', $limit)
            ->fetchAll();
    }

    /**
     * Get audit statistics for reporting
     *
     * @param string $fromDate Start date (Y-m-d format)
     * @param string $toDate End date (Y-m-d format)
     *
     * @return array Statistics array
     */
    public function getAuditStatistics(string $fromDate, string $toDate): array
    {
        $stats = $this->listingQuery()
            ->select([
                'change_type',
                'COUNT(*) as count',
                'COUNT(DISTINCT user_id) as unique_users'
            ])
            ->where('created_at BETWEEN %s AND %s', $fromDate . ' 00:00:00', $toDate . ' 23:59:59')
            ->groupBy('change_type')
            ->fetchAll();

        $result = [
            'total_changes' => 0,
            'unique_users_affected' => 0,
            'by_type' => []
        ];

        foreach ($stats as $stat) {
            $result['total_changes'] += $stat['count'];
            $result['by_type'][$stat['change_type']] = $stat['count'];
        }

        // Get unique users count across all change types
        $uniqueUsersResult = $this->listingQuery()
            ->select(['COUNT(DISTINCT user_id) as unique_users'])
            ->where('created_at BETWEEN %s AND %s', $fromDate . ' 00:00:00', $toDate . ' 23:59:59')
            ->fetch();

        $result['unique_users_affected'] = $uniqueUsersResult['unique_users'] ?? 0;

        return $result;
    }

    /**
     * Create audit log database table
     *
     * @return bool Success of table creation
     */
    public function createTable(): bool
    {
        $createSQL = "
            CREATE TABLE IF NOT EXISTS `{$this->myTable}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `field_name` varchar(100) NOT NULL,
                `old_value` text,
                `new_value` text,
                `change_type` enum('direct','pending_approval','approved','rejected') NOT NULL DEFAULT 'direct',
                `changed_by_user_id` int(11) NULL,
                `ip_address` varchar(45) NULL,
                `user_agent` text NULL,
                `reason` text NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_change_type` (`change_type`),
                KEY `idx_created_at` (`created_at`),
                FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`changed_by_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $this->pdo->exec($createSQL);
            return true;
        } catch (\PDOException $e) {
            $this->addStatusMessage('Failed to create audit table: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}