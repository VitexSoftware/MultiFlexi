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

namespace MultiFlexi\Security;

/**
 * Security Manager for GDPR Data Export
 * 
 * Handles authentication, authorization, rate limiting, and audit logging
 * for data export requests in compliance with GDPR security requirements
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DataExportSecurityManager extends \MultiFlexi\Engine
{
    private const RATE_LIMIT_REQUESTS = 5; // 5 requests per hour
    private const RATE_LIMIT_WINDOW = 3600; // 1 hour in seconds
    private const TOKEN_EXPIRY = 3600; // 1 hour token validity

    /**
     * Check if user can request data export
     * 
     * @param int $userId
     * @param string $ipAddress
     * @return array Result with success status and message
     */
    public function canRequestExport(int $userId, string $ipAddress): array
    {
        // Check authentication
        if (!$this->isUserAuthenticated($userId)) {
            return [
                'allowed' => false,
                'reason' => 'authentication_required',
                'message' => 'User must be authenticated'
            ];
        }

        // Check rate limiting
        if (!$this->checkRateLimit($userId, $ipAddress)) {
            $this->logSecurityEvent($userId, 'data_export_rate_limit_exceeded', $ipAddress);
            return [
                'allowed' => false,
                'reason' => 'rate_limit_exceeded',
                'message' => 'Too many export requests. Please try again later.'
            ];
        }

        // Check for suspicious activity
        if ($this->detectSuspiciousActivity($userId, $ipAddress)) {
            $this->logSecurityEvent($userId, 'data_export_suspicious_activity', $ipAddress);
            return [
                'allowed' => false,
                'reason' => 'suspicious_activity',
                'message' => 'Request blocked due to suspicious activity'
            ];
        }

        return [
            'allowed' => true,
            'reason' => 'authorized',
            'message' => 'Request authorized'
        ];
    }

    /**
     * Create secure download token
     * 
     * @param int $userId
     * @param string $format
     * @param string $ipAddress
     * @param string $userAgent
     * @return array Token data
     */
    public function createSecureToken(int $userId, string $format, string $ipAddress, string $userAgent): array
    {
        // Generate cryptographically secure token
        $tokenData = [
            'user_id' => $userId,
            'format' => $format,
            'expires' => time() + self::TOKEN_EXPIRY,
            'nonce' => bin2hex(random_bytes(32)),
            'ip' => $ipAddress,
            'created' => time()
        ];
        
        $tokenString = json_encode($tokenData);
        $token = bin2hex(random_bytes(32)); // 64 character token
        $tokenHash = hash('sha256', $token . \Ease\Shared::cfg('DB_PASSWORD'));
        
        // Store token in database
        $tokenEngine = new \Ease\SQL\Engine();
        $tokenEngine->myTable = 'data_export_tokens';
        
        $tokenRecord = [
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'format' => $format,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'expires_at' => date('Y-m-d H:i:s', $tokenData['expires']),
            'DatCreate' => date('Y-m-d H:i:s')
        ];
        
        if ($tokenEngine->insertToSQL($tokenRecord)) {
            $this->logGDPRAudit($userId, 'data_export_token_created', 'Article 15', [
                'format' => $format,
                'expires_at' => $tokenRecord['expires_at']
            ]);
            
            return [
                'success' => true,
                'token' => $token,
                'expires_at' => $tokenRecord['expires_at']
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Failed to create secure token'
        ];
    }

    /**
     * Verify download token
     * 
     * @param string $token
     * @param int $userId
     * @param string $ipAddress
     * @return array|false Token data or false if invalid
     */
    public function verifyDownloadToken(string $token, int $userId, string $ipAddress)
    {
        $tokenHash = hash('sha256', $token . \Ease\Shared::cfg('DB_PASSWORD'));
        
        $tokenEngine = new \Ease\SQL\Engine();
        $tokenEngine->myTable = 'data_export_tokens';
        
        $tokenRecord = $tokenEngine->listingQuery()
            ->select('*')
            ->where(['token_hash' => $tokenHash, 'user_id' => $userId])
            ->fetch();
            
        if (!$tokenRecord) {
            $this->logSecurityEvent($userId, 'data_export_invalid_token', $ipAddress);
            return false;
        }
        
        // Check expiry
        if (strtotime($tokenRecord['expires_at']) < time()) {
            $this->logSecurityEvent($userId, 'data_export_expired_token', $ipAddress);
            return false;
        }
        
        // Check if already used
        if ($tokenRecord['used_at']) {
            $this->logSecurityEvent($userId, 'data_export_reused_token', $ipAddress);
            return false;
        }
        
        // Mark token as used
        $tokenEngine->listingQuery()
            ->update(['used_at' => date('Y-m-d H:i:s')])
            ->where(['id' => $tokenRecord['id']])
            ->execute();
            
        $this->logGDPRAudit($userId, 'data_export_token_used', 'Article 15', [
            'format' => $tokenRecord['format'],
            'token_created' => $tokenRecord['DatCreate']
        ]);
        
        return [
            'user_id' => (int) $tokenRecord['user_id'],
            'format' => $tokenRecord['format'],
            'expires' => strtotime($tokenRecord['expires_at'])
        ];
    }

    /**
     * Check rate limiting
     * 
     * @param int $userId
     * @param string $ipAddress
     * @return bool
     */
    private function checkRateLimit(int $userId, string $ipAddress): bool
    {
        $rateLimitEngine = new \Ease\SQL\Engine();
        $rateLimitEngine->myTable = 'data_export_rate_limits';
        
        $now = new \DateTime();
        $windowStart = new \DateTime();
        $windowStart->sub(new \DateInterval('PT' . self::RATE_LIMIT_WINDOW . 'S'));
        
        // Get current rate limit record
        $rateLimitRecord = $rateLimitEngine->listingQuery()
            ->select('*')
            ->where(['user_id' => $userId, 'ip_address' => $ipAddress])
            ->where('window_end > ?', [$now->format('Y-m-d H:i:s')])
            ->fetch();
            
        if ($rateLimitRecord) {
            // Check if within limits
            if ($rateLimitRecord['request_count'] >= self::RATE_LIMIT_REQUESTS) {
                return false;
            }
            
            // Increment counter
            $rateLimitEngine->listingQuery()
                ->update([
                    'request_count' => $rateLimitRecord['request_count'] + 1,
                    'DatSave' => $now->format('Y-m-d H:i:s')
                ])
                ->where(['id' => $rateLimitRecord['id']])
                ->execute();
                
        } else {
            // Create new rate limit window
            $windowEnd = new \DateTime();
            $windowEnd->add(new \DateInterval('PT' . self::RATE_LIMIT_WINDOW . 'S'));
            
            $rateLimitEngine->insertToSQL([
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'request_count' => 1,
                'window_start' => $now->format('Y-m-d H:i:s'),
                'window_end' => $windowEnd->format('Y-m-d H:i:s'),
                'DatCreate' => $now->format('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }

    /**
     * Check if user is authenticated
     * 
     * @param int $userId
     * @return bool
     */
    private function isUserAuthenticated(int $userId): bool
    {
        $currentUser = \Ease\Shared::user();
        return $currentUser && $currentUser->getUserID() && (int) $currentUser->getUserID() === $userId;
    }

    /**
     * Detect suspicious activity
     * 
     * @param int $userId
     * @param string $ipAddress
     * @return bool
     */
    private function detectSuspiciousActivity(int $userId, string $ipAddress): bool
    {
        // Check for rapid requests from different IPs
        $rateLimitEngine = new \Ease\SQL\Engine();
        $rateLimitEngine->myTable = 'data_export_rate_limits';
        
        $lastHour = new \DateTime();
        $lastHour->sub(new \DateInterval('PT1H'));
        
        $ipCount = $rateLimitEngine->listingQuery()
            ->select('COUNT(DISTINCT ip_address) as ip_count')
            ->where(['user_id' => $userId])
            ->where('DatCreate > ?', [$lastHour->format('Y-m-d H:i:s')])
            ->fetch();
            
        // Suspicious if more than 3 different IPs in last hour
        if ($ipCount && $ipCount['ip_count'] > 3) {
            return true;
        }
        
        // Check for unusual patterns (could be expanded)
        return false;
    }

    /**
     * Log security event
     * 
     * @param int $userId
     * @param string $event
     * @param string $ipAddress
     * @param array $details
     */
    private function logSecurityEvent(int $userId, string $event, string $ipAddress, array $details = []): void
    {
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';
        
        $logEngine->insertToSQL([
            'user_id' => $userId,
            'severity' => 'warning',
            'venue' => 'DataExportSecurity',
            'message' => "Security event: {$event} from IP {$ipAddress}" . (empty($details) ? '' : ' - ' . json_encode($details)),
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log GDPR audit event
     * 
     * @param int $userId
     * @param string $action
     * @param string $article
     * @param array $details
     */
    private function logGDPRAudit(int $userId, string $action, string $article, array $details = []): void
    {
        $auditEngine = new \Ease\SQL\Engine();
        $auditEngine->myTable = 'gdpr_audit_log';
        
        $user = new \MultiFlexi\User($userId);
        
        $auditEngine->insertToSQL([
            'user_id' => $userId,
            'action' => $action,
            'article' => $article,
            'data_subject' => $user->getUserName(),
            'legal_basis' => 'Article 6(1)(a) - Consent',
            'details' => empty($details) ? null : json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'result' => 'success',
            'DatCreate' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Clean up expired tokens and rate limit records
     * 
     * @return array Cleanup statistics
     */
    public function cleanupExpiredRecords(): array
    {
        $stats = ['tokens_cleaned' => 0, 'rate_limits_cleaned' => 0];
        
        // Clean expired tokens
        $tokenEngine = new \Ease\SQL\Engine();
        $tokenEngine->myTable = 'data_export_tokens';
        
        $expiredTokens = $tokenEngine->listingQuery()
            ->delete()
            ->where('expires_at < ?', [date('Y-m-d H:i:s')])
            ->execute();
            
        $stats['tokens_cleaned'] = $expiredTokens;
        
        // Clean old rate limit records (older than 24 hours)
        $rateLimitEngine = new \Ease\SQL\Engine();
        $rateLimitEngine->myTable = 'data_export_rate_limits';
        
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        
        $expiredRateLimits = $rateLimitEngine->listingQuery()
            ->delete()
            ->where('window_end < ?', [$yesterday->format('Y-m-d H:i:s')])
            ->execute();
            
        $stats['rate_limits_cleaned'] = $expiredRateLimits;
        
        return $stats;
    }
}