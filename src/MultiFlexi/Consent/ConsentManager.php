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

namespace MultiFlexi\Consent;

/**
 * GDPR Consent Management System.
 *
 * Handles user consent for cookies, analytics, marketing, and other data processing activities.
 * Provides comprehensive consent tracking with audit trail for GDPR compliance.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */
class ConsentManager extends \MultiFlexi\Engine
{
    use \Ease\SQL\Orm;

    // Consent types
    public const CONSENT_ESSENTIAL = 'essential';
    public const CONSENT_ANALYTICS = 'analytics';
    public const CONSENT_MARKETING = 'marketing';
    public const CONSENT_FUNCTIONAL = 'functional';
    public const CONSENT_PERSONALIZATION = 'personalization';

    // Consent statuses
    public const STATUS_GRANTED = true;
    public const STATUS_DENIED = false;

    // Consent actions for audit log
    public const ACTION_GRANTED = 'granted';
    public const ACTION_DENIED = 'denied';
    public const ACTION_WITHDRAWN = 'withdrawn';
    public const ACTION_UPDATED = 'updated';

    /**
     * Creation timestamp column name.
     */
    public ?string $createColumn = null;

    /**
     * Last modified timestamp column name.
     */
    public ?string $lastModifiedColumn = null;

    /**
     * Default consent expiration in days.
     */
    private int $defaultExpirationDays = 365;

    public function __construct($identifier = null)
    {
        $this->myTable = 'consent';
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatSave';
        parent::__construct($identifier);
    }

    /**
     * Get current user consent status for a specific type.
     *
     * @param string      $consentType Type of consent to check
     * @param null|int    $userId      User ID (null for current user)
     * @param null|string $sessionId   Session ID for anonymous users
     *
     * @return null|bool True if granted, false if denied, null if not set
     */
    public function getConsentStatus(string $consentType, ?int $userId = null, ?string $sessionId = null): ?bool
    {
        $userId ??= self::getCurrentUserId();
        $sessionId ??= session_id();

        $conditions = [
            'consent_type' => $consentType,
            'withdrawn_at' => null, // Not withdrawn
        ];

        // Add user or session condition
        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
        }

        // Find most recent consent
        $results = $this->listingQuery()
            ->where($conditions)
            ->orderBy('DatCreate DESC')
            ->fetchAll();

        // Filter out expired consents and get the most recent one
        $consent = null;

        foreach ($results as $result) {
            if ($result['withdrawn_at'] === null
                && ($result['expires_at'] === null || strtotime($result['expires_at']) > time())) {
                $consent = $result;

                break;
            }
        }

        if ($consent) {
            return (bool) $consent['consent_status'];
        }

        return null;
    }

    /**
     * Get all consent statuses for a user.
     *
     * @param null|int    $userId    User ID (null for current user)
     * @param null|string $sessionId Session ID for anonymous users
     *
     * @return array Array of consent types and their statuses
     */
    public function getAllConsentStatuses(?int $userId = null, ?string $sessionId = null): array
    {
        $userId ??= self::getCurrentUserId();
        $sessionId ??= session_id();

        $conditions = ['withdrawn_at' => null];

        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
        }

        $consents = $this->listingQuery()
            ->select('consent_type, consent_status, consent_details, DatCreate, expires_at')
            ->where($conditions)
            ->orderBy('consent_type, DatCreate DESC')
            ->fetchAll();

        $result = [];

        foreach ($consents as $consent) {
            // Only keep the most recent consent for each type if it's not expired
            if (!isset($result[$consent['consent_type']])
                && ($consent['expires_at'] === null || strtotime($consent['expires_at']) > time())) {
                $result[$consent['consent_type']] = [
                    'status' => (bool) $consent['consent_status'],
                    'details' => $consent['consent_details'] ? json_decode($consent['consent_details'], true) : null,
                    'granted_at' => $consent['DatCreate'],
                ];
            }
        }

        return $result;
    }

    /**
     * Record user consent.
     *
     * @param string      $consentType    Type of consent
     * @param bool        $status         Consent granted (true) or denied (false)
     * @param null|array  $details        Additional consent details
     * @param null|int    $userId         User ID (null for current user)
     * @param null|string $sessionId      Session ID for anonymous users
     * @param string      $version        Version of consent policy
     * @param null|int    $expirationDays Days until consent expires
     *
     * @return bool Success status
     */
    public function recordConsent(
        string $consentType,
        bool $status,
        ?array $details = null,
        ?int $userId = null,
        ?string $sessionId = null,
        string $version = '1.0',
        ?int $expirationDays = null,
    ): bool {
        $userId ??= self::getCurrentUserId();
        $sessionId ??= session_id();
        $expirationDays ??= $this->defaultExpirationDays;

        // Get old consent for audit
        $oldConsent = $this->getConsentStatus($consentType, $userId, $sessionId);

        $consentData = [
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'ip_address' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'consent_type' => $consentType,
            'consent_status' => $status,
            'consent_details' => $details ? json_encode($details) : null,
            'consent_version' => $version,
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$expirationDays} days")),
            'DatCreate' => date('Y-m-d H:i:s'),
        ];

        // Insert consent record
        $this->dataReset();
        $this->setData($consentData);

        if ($this->insertToSQL()) {
            $consentId = $this->getMyKey();

            // Log the consent action
            $action = $oldConsent === null ?
                ($status ? self::ACTION_GRANTED : self::ACTION_DENIED) :
                self::ACTION_UPDATED;

            self::logConsentAction($consentId, $action, $oldConsent, $status, $consentType, $userId, $sessionId);

            return true;
        }

        return false;
    }

    /**
     * Withdraw consent for a specific type.
     *
     * @param string      $consentType Type of consent to withdraw
     * @param null|int    $userId      User ID (null for current user)
     * @param null|string $sessionId   Session ID for anonymous users
     *
     * @return bool Success status
     */
    public function withdrawConsent(string $consentType, ?int $userId = null, ?string $sessionId = null): bool
    {
        $userId ??= self::getCurrentUserId();
        $sessionId ??= session_id();

        $conditions = [
            'consent_type' => $consentType,
            'withdrawn_at' => null,
        ];

        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
        }

        $result = $this->listingQuery()
            ->update([
                'withdrawn_at' => date('Y-m-d H:i:s'),
                'DatSave' => date('Y-m-d H:i:s'),
            ])
            ->where($conditions)
            ->execute();

        if ($result) {
            // Log withdrawal
            self::logConsentAction(null, self::ACTION_WITHDRAWN, true, false, $consentType, $userId, $sessionId);
        }

        return (bool) $result;
    }

    /**
     * Check if user has given consent for essential cookies/processing
     * Essential cookies are always allowed, this method exists for consistency.
     *
     * @param null|int    $userId    User ID
     * @param null|string $sessionId Session ID
     *
     * @return bool Always returns true for essential consent
     */
    public function hasEssentialConsent(?int $userId = null, ?string $sessionId = null): bool
    {
        // Essential cookies are always allowed under GDPR
        return true;
    }

    /**
     * Check if user has given consent for analytics.
     *
     * @param null|int    $userId    User ID
     * @param null|string $sessionId Session ID
     *
     * @return bool True if analytics consent is granted
     */
    public function hasAnalyticsConsent(?int $userId = null, ?string $sessionId = null): bool
    {
        return $this->getConsentStatus(self::CONSENT_ANALYTICS, $userId, $sessionId) === true;
    }

    /**
     * Check if user has given consent for marketing.
     *
     * @param null|int    $userId    User ID
     * @param null|string $sessionId Session ID
     *
     * @return bool True if marketing consent is granted
     */
    public function hasMarketingConsent(?int $userId = null, ?string $sessionId = null): bool
    {
        return $this->getConsentStatus(self::CONSENT_MARKETING, $userId, $sessionId) === true;
    }

    /**
     * Clean up expired consents.
     *
     * @return int Number of cleaned up records
     */
    public function cleanupExpiredConsents(): int
    {
        return $this->listingQuery()
            ->update(['withdrawn_at' => date('Y-m-d H:i:s')])
            ->where('expires_at IS NOT NULL AND expires_at < NOW()')
            ->where('withdrawn_at IS NULL')
            ->execute();
    }

    /**
     * Get available consent types.
     *
     * @return array List of consent type constants
     */
    public static function getConsentTypes(): array
    {
        return [
            self::CONSENT_ESSENTIAL,
            self::CONSENT_FUNCTIONAL,
            self::CONSENT_ANALYTICS,
            self::CONSENT_MARKETING,
            self::CONSENT_PERSONALIZATION,
        ];
    }

    /**
     * Get consent statistics for admin dashboard.
     *
     * @return array Statistics about consent
     */
    public function getConsentStatistics(): array
    {
        $stats = [];

        // Get consent counts by type
        $consentCounts = $this->listingQuery()
            ->select('consent_type, consent_status, COUNT(*) as count, expires_at')
            ->where('withdrawn_at IS NULL')
            ->groupBy('consent_type, consent_status')
            ->fetchAll();

        // Filter expired consents
        $validCounts = [];

        foreach ($consentCounts as $count) {
            if ($count['expires_at'] === null || strtotime($count['expires_at']) > time()) {
                $validCounts[] = $count;
            }
        }

        $consentCounts = $validCounts;

        foreach ($consentCounts as $count) {
            $type = $count['consent_type'];
            $status = $count['consent_status'] ? 'granted' : 'denied';
            $stats[$type][$status] = (int) $count['count'];
        }

        // Get total users with consent
        $allUsers = $this->listingQuery()
            ->select('user_id, session_id, expires_at')
            ->where('withdrawn_at IS NULL')
            ->fetchAll();

        // Count unique users/sessions that have non-expired consent
        $uniqueUsers = [];

        foreach ($allUsers as $user) {
            if ($user['expires_at'] === null || strtotime($user['expires_at']) > time()) {
                $identifier = $user['user_id'] ?? $user['session_id'];

                if ($identifier) {
                    $uniqueUsers[$identifier] = true;
                }
            }
        }

        $totalUsers = ['count' => \count($uniqueUsers)];

        $stats['total_users'] = (int) $totalUsers['count'];

        return $stats;
    }

    /**
     * Log consent action to audit trail.
     *
     * @param null|int    $consentId   Consent record ID
     * @param string      $action      Action performed
     * @param mixed       $oldValue    Previous value
     * @param mixed       $newValue    New value
     * @param string      $consentType Consent type
     * @param null|int    $userId      User ID
     * @param null|string $sessionId   Session ID
     */
    private static function logConsentAction(
        ?int $consentId,
        string $action,
        $oldValue,
        $newValue,
        string $consentType,
        ?int $userId = null,
        ?string $sessionId = null,
    ): void {
        $logData = [
            'consent_id' => $consentId,
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'ip_address' => self::getClientIp(),
            'action' => $action,
            'consent_type' => $consentType,
            'old_value' => $oldValue !== null ? json_encode($oldValue) : null,
            'new_value' => $newValue !== null ? json_encode($newValue) : null,
            'DatCreate' => date('Y-m-d H:i:s'),
        ];

        // Use a new engine instance for consent_log table
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'consent_log';
        $logEngine->insertToSQL($logData);
    }

    /**
     * Get current user ID from session.
     *
     * @return null|int Current user ID or null if not logged in
     */
    private static function getCurrentUserId(): ?int
    {
        $user = \Ease\Shared::user();

        return $user && $user->getUserID() ? (int) $user->getUserID() : null;
    }

    /**
     * Get client IP address.
     *
     * @return string Client IP address
     */
    private static function getClientIp(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);

                if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
