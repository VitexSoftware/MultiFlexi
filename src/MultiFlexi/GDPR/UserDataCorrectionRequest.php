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

namespace MultiFlexi\GDPR;

use MultiFlexi\Audit\UserDataAuditLogger;
use MultiFlexi\Notifications\DataCorrectionNotifier;

/**
 * User Data Correction Request - manages approval workflow for sensitive data changes.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class UserDataCorrectionRequest extends \MultiFlexi\DBEngine
{
    /**
     * Fields that require admin approval.
     */
    public const SENSITIVE_FIELDS = [
        'login' => 'Username',
        'email' => 'Email Address',
    ];

    /**
     * Fields that can be changed directly.
     */
    public const DIRECT_FIELDS = [
        'firstname' => 'First Name',
        'lastname' => 'Last Name',
    ];

    /**
     * Request status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Constructor.
     *
     * @param mixed $identifier Record identifier
     */
    public function __construct($identifier = null)
    {
        $this->myTable = 'user_data_correction_requests';
        $this->createColumn = 'created_at';
        $this->lastModifiedColumn = 'updated_at';
        parent::__construct($identifier);
    }

    /**
     * Create a new correction request.
     *
     * @param int    $userId         User ID requesting the change
     * @param string $fieldName      Field to be changed
     * @param mixed  $currentValue   Current field value
     * @param mixed  $requestedValue Requested new value
     * @param string $justification  User's justification for the change
     *
     * @return bool Success of request creation
     */
    public function createRequest(
        int $userId,
        string $fieldName,
        $currentValue,
        $requestedValue,
        string $justification = '',
    ): bool {
        $requestData = [
            'user_id' => $userId,
            'field_name' => $fieldName,
            'current_value' => \is_array($currentValue) || \is_object($currentValue) ?
                json_encode($currentValue) : (string) $currentValue,
            'requested_value' => \is_array($requestedValue) || \is_object($requestedValue) ?
                json_encode($requestedValue) : (string) $requestedValue,
            'justification' => $justification,
            'status' => self::STATUS_PENDING,
            'requested_by_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'requested_by_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->setData($requestData);

        if ($this->dbsync()) {
            // Send notifications about the new request
            $notifier = new DataCorrectionNotifier();
            $user = new \MultiFlexi\User($userId);
            $notifier->notifyNewRequest($this->getMyKey(), $user);

            return true;
        }

        return false;
    }

    /**
     * Approve a correction request and apply the change.
     *
     * @param int    $requestId      Request ID to approve
     * @param int    $reviewerUserId ID of admin who approved
     * @param string $reviewerNotes  Admin's notes for the approval
     *
     * @return bool Success of approval and data change
     */
    public function approveRequest(int $requestId, int $reviewerUserId, string $reviewerNotes = ''): bool
    {
        $this->loadFromSQL($requestId);

        if (!$this->getData() || $this->getDataValue('status') !== self::STATUS_PENDING) {
            $this->addStatusMessage(_('Request not found or already processed'), 'error');

            return false;
        }

        $userId = (int) $this->getDataValue('user_id');
        $fieldName = $this->getDataValue('field_name');
        $newValue = $this->getDataValue('requested_value');
        $oldValue = $this->getDataValue('current_value');

        // Load user and apply the change
        $user = new \MultiFlexi\User($userId);

        if (!$user->getMyKey()) {
            $this->addStatusMessage(_('User not found'), 'error');

            return false;
        }

        // Apply the change to user data
        $user->setDataValue($fieldName, $newValue);

        if (!$user->dbsync()) {
            $this->addStatusMessage(_('Failed to update user data'), 'error');

            return false;
        }

        // Update request status
        $this->setDataValues([
            'status' => self::STATUS_APPROVED,
            'reviewed_by_user_id' => $reviewerUserId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewer_notes' => $reviewerNotes,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$this->dbsync()) {
            $this->addStatusMessage(_('Failed to update request status'), 'error');

            return false;
        }

        // Log the approved change
        $auditLogger = new UserDataAuditLogger();
        $auditLogger->logDataChange(
            $userId,
            $fieldName,
            $oldValue,
            $newValue,
            'approved',
            $reviewerUserId,
            null,
            null,
            'Approved correction request #'.$requestId.': '.$reviewerNotes,
        );

        // Send notification to user about approval
        $notifier = new DataCorrectionNotifier();
        $reviewer = new \MultiFlexi\User($reviewerUserId);
        $notifier->notifyRequestApproved($requestId, $reviewer, $reviewerNotes);

        return true;
    }

    /**
     * Reject a correction request.
     *
     * @param int    $requestId       Request ID to reject
     * @param int    $reviewerUserId  ID of admin who rejected
     * @param string $rejectionReason Reason for rejection
     *
     * @return bool Success of rejection
     */
    public function rejectRequest(int $requestId, int $reviewerUserId, string $rejectionReason): bool
    {
        $this->loadFromSQL($requestId);

        if (!$this->getData() || $this->getDataValue('status') !== self::STATUS_PENDING) {
            $this->addStatusMessage(_('Request not found or already processed'), 'error');

            return false;
        }

        $this->setDataValues([
            'status' => self::STATUS_REJECTED,
            'reviewed_by_user_id' => $reviewerUserId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewer_notes' => $rejectionReason,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$this->dbsync()) {
            $this->addStatusMessage(_('Failed to update request status'), 'error');

            return false;
        }

        // Log the rejection
        $auditLogger = new UserDataAuditLogger();
        $auditLogger->logDataChange(
            (int) $this->getDataValue('user_id'),
            $this->getDataValue('field_name'),
            $this->getDataValue('current_value'),
            $this->getDataValue('requested_value'),
            'rejected',
            $reviewerUserId,
            null,
            null,
            'Rejected correction request #'.$requestId.': '.$rejectionReason,
        );

        // Send notification to user about rejection
        $notifier = new DataCorrectionNotifier();
        $reviewer = new \MultiFlexi\User($reviewerUserId);
        $notifier->notifyRequestRejected($requestId, $reviewer, $rejectionReason);

        return true;
    }

    /**
     * Cancel a pending request (by the user who created it).
     *
     * @param int $requestId Request ID to cancel
     * @param int $userId    User ID (must match request creator)
     *
     * @return bool Success of cancellation
     */
    public function cancelRequest(int $requestId, int $userId): bool
    {
        $this->loadFromSQL($requestId);

        if (!$this->getData()
            || (int) $this->getDataValue('user_id') !== $userId
            || $this->getDataValue('status') !== self::STATUS_PENDING) {
            $this->addStatusMessage(_('Request not found, not yours, or already processed'), 'error');

            return false;
        }

        $this->setDataValues([
            'status' => self::STATUS_CANCELLED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->dbsync();
    }

    /**
     * Get pending requests for admin review.
     *
     * @param int $limit  Maximum number of requests to return
     * @param int $offset Offset for pagination
     *
     * @return array Array of pending requests with user information
     */
    public function getPendingRequests(int $limit = 20, int $offset = 0): array
    {
        $query = $this->getFluentPDO()->from($this->myTable.' r')
            ->select('r.*')
            ->select('u.login')
            ->select('u.firstname')
            ->select('u.lastname')
            ->select('u.email')
            ->select('reviewer.login as reviewer_login')
            ->leftJoin('user u ON r.user_id = u.id')
            ->leftJoin('user reviewer ON r.reviewed_by_user_id = reviewer.id')
            ->where('r.status', self::STATUS_PENDING)
            ->orderBy('r.created_at ASC')
            ->limit($limit)
            ->offset($offset);

        $result = $query->fetchAll();

        return $result ?: [];
    }

    /**
     * Get user's own requests.
     *
     * @param int $userId User ID
     * @param int $limit  Maximum number of requests to return
     *
     * @return array Array of user's requests
     */
    public function getUserRequests(int $userId, int $limit = 10): array
    {
        $query = $this->getFluentPDO()->from($this->myTable.' r')
            ->select('r.*')
            ->select('reviewer.login as reviewer_login')
            ->select('reviewer.firstname as reviewer_firstname')
            ->select('reviewer.lastname as reviewer_lastname')
            ->leftJoin('user reviewer ON r.reviewed_by_user_id = reviewer.id')
            ->where('r.user_id', $userId)
            ->orderBy('r.created_at DESC')
            ->limit($limit);

        $result = $query->fetchAll();

        return $result ?: [];
    }

    /**
     * Check if field requires approval.
     *
     * @param string $fieldName Field name to check
     *
     * @return bool True if field requires approval
     */
    public static function requiresApproval(string $fieldName): bool
    {
        return \array_key_exists($fieldName, self::SENSITIVE_FIELDS);
    }

    /**
     * Get human-readable field name.
     *
     * @param string $fieldName Technical field name
     *
     * @return string Human-readable field name
     */
    public static function getFieldDisplayName(string $fieldName): string
    {
        return self::SENSITIVE_FIELDS[$fieldName] ?? self::DIRECT_FIELDS[$fieldName] ?? $fieldName;
    }
}
