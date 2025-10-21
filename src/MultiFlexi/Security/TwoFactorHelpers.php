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
 * Helper functions for Two-Factor Authentication integration.
 */
class TwoFactorHelpers
{
    /**
     * Check if 2FA is available and enabled.
     *
     * @return bool True if 2FA is available
     */
    public static function isAvailable(): bool
    {
        return isset($GLOBALS['twoFactorAuth'])
            && \Ease\Shared::cfg('TWO_FACTOR_AUTH_ENABLED', true);
    }

    /**
     * Check if 2FA is enabled for a user.
     *
     * @param int $userId User ID
     *
     * @return bool Whether 2FA is enabled for the user
     */
    public static function isEnabledForUser(int $userId): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        return $GLOBALS['twoFactorAuth']->isEnabled($userId);
    }

    /**
     * Check if 2FA is enabled for current user.
     *
     * @return bool Whether 2FA is enabled for current user
     */
    public static function isEnabledForCurrentUser(): bool
    {
        $userId = self::getCurrentUserId();

        return $userId ? self::isEnabledForUser($userId) : false;
    }

    /**
     * Setup 2FA for a user.
     *
     * @param int $userId User ID
     *
     * @return null|array Setup information or null on failure
     */
    public static function setupForUser(int $userId): ?array
    {
        if (!self::isAvailable()) {
            return null;
        }

        try {
            return $GLOBALS['twoFactorAuth']->setupTwoFactor($userId);
        } catch (\Exception $e) {
            error_log('2FA setup failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Verify 2FA code for a user.
     *
     * @param int    $userId User ID
     * @param string $code   TOTP code or backup code
     *
     * @return bool Verification result
     */
    public static function verifyCode(int $userId, string $code): bool
    {
        if (!self::isAvailable()) {
            return true; // Allow if 2FA is not available
        }

        return $GLOBALS['twoFactorAuth']->verifyCode($userId, $code);
    }

    /**
     * Enable 2FA for a user after verification.
     *
     * @param int    $userId           User ID
     * @param string $verificationCode TOTP code for verification
     *
     * @return bool Success status
     */
    public static function enableForUser(int $userId, string $verificationCode): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        return $GLOBALS['twoFactorAuth']->enableTwoFactor($userId, $verificationCode);
    }

    /**
     * Disable 2FA for a user.
     *
     * @param int    $userId           User ID
     * @param string $verificationCode TOTP code or backup code for verification
     *
     * @return bool Success status
     */
    public static function disableForUser(int $userId, string $verificationCode): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        return $GLOBALS['twoFactorAuth']->disableTwoFactor($userId, $verificationCode);
    }

    /**
     * Generate new backup codes for a user.
     *
     * @param int $userId User ID
     *
     * @return null|array New backup codes or null on failure
     */
    public static function regenerateBackupCodes(int $userId): ?array
    {
        if (!self::isAvailable()) {
            return null;
        }

        try {
            return $GLOBALS['twoFactorAuth']->regenerateBackupCodes($userId);
        } catch (\Exception $e) {
            error_log('2FA backup code regeneration failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get remaining backup codes for a user.
     *
     * @param int $userId User ID
     *
     * @return array Remaining backup codes
     */
    public static function getRemainingBackupCodes(int $userId): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        return $GLOBALS['twoFactorAuth']->getRemainingBackupCodes($userId);
    }

    /**
     * Check if 2FA verification is required for current request.
     *
     * @param int $userId User ID
     *
     * @return bool Whether 2FA verification is required
     */
    public static function isVerificationRequired(int $userId): bool
    {
        // Check if 2FA is enabled for user
        if (!self::isEnabledForUser($userId)) {
            return false;
        }

        // Check if already verified in current session
        if (isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'] === $userId) {
            return false;
        }

        return true;
    }

    /**
     * Mark 2FA as verified in current session.
     *
     * @param int $userId User ID
     */
    public static function markVerifiedInSession(int $userId): void
    {
        $_SESSION['2fa_verified'] = $userId;
        $_SESSION['2fa_verified_at'] = time();
    }

    /**
     * Clear 2FA verification from session.
     */
    public static function clearSessionVerification(): void
    {
        unset($_SESSION['2fa_verified'], $_SESSION['2fa_verified_at']);
    }

    /**
     * Check if current session needs 2FA verification.
     *
     * @return bool Whether verification is needed
     */
    public static function currentSessionNeedsVerification(): bool
    {
        $userId = self::getCurrentUserId();

        return $userId ? self::isVerificationRequired($userId) : false;
    }

    /**
     * Enforce 2FA verification - redirect to 2FA page if required.
     *
     * @param null|int    $userId      User ID (uses current user if null)
     * @param null|string $redirectUrl URL to redirect to if 2FA is needed
     */
    public static function enforceVerification(?int $userId = null, ?string $redirectUrl = null): void
    {
        if ($userId === null) {
            $userId = self::getCurrentUserId();
        }

        if (!$userId) {
            return;
        }

        if (self::isVerificationRequired($userId)) {
            if ($redirectUrl) {
                header("Location: {$redirectUrl}");

                exit;
            }

            // Default 2FA verification page
            header('Location: /2fa/verify');

            exit;
        }
    }

    /**
     * Get 2FA statistics.
     *
     * @return array Statistics data
     */
    public static function getStatistics(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        return $GLOBALS['twoFactorAuth']->getStatistics();
    }

    /**
     * Generate QR code HTML for 2FA setup.
     *
     * @param string $qrCodeUrl QR code URL
     * @param string $secret    Manual entry secret
     *
     * @return string HTML for QR code display
     */
    public static function generateQrCodeHtml(string $qrCodeUrl, string $secret): string
    {
        $issuer = htmlspecialchars(\Ease\Shared::cfg('APP_NAME', 'MultiFlexi'));
        $secretFormatted = htmlspecialchars($secret);

        return <<<EOD

            <div class='text-center mb-4'>
                <h5>Scan QR Code</h5>
                <p class='text-muted'>Use an authenticator app like Google Authenticator, Authy, or Microsoft Authenticator</p>
                <img src='{$qrCodeUrl}' alt='2FA QR Code' class='img-fluid mb-3' style='max-width: 200px;'>
                <hr>
                <h6>Manual Entry</h6>
                <p class='text-muted'>If you can't scan the QR code, enter this secret manually:</p>
                <code class='bg-light p-2 d-block'>{$secretFormatted}</code>
                <small class='text-muted'>Issuer: {$issuer}</small>
            </div>

EOD;
    }

    /**
     * Generate backup codes HTML display.
     *
     * @param array $backupCodes Array of backup codes
     *
     * @return string HTML for backup codes display
     */
    public static function generateBackupCodesHtml(array $backupCodes): string
    {
        if (empty($backupCodes)) {
            return '<p class="text-warning">No backup codes available. Generate new codes if needed.</p>';
        }

        $html = '<div class="backup-codes-container">';
        $html .= '<h5>Backup Recovery Codes</h5>';
        $html .= '<p class="text-warning"><strong>Important:</strong> Save these codes safely. Each can only be used once.</p>';
        $html .= '<div class="row">';

        foreach ($backupCodes as $index => $code) {
            $html .= '<div class="col-md-6 mb-2">';
            $html .= '<code class="d-block p-2 bg-light">'.htmlspecialchars($code).'</code>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '<p class="text-muted mt-3"><small>Remaining codes: '.\count($backupCodes).'</small></p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Create 2FA verification form HTML.
     *
     * @param string $action           Form action URL
     * @param string $csrfToken        CSRF protection token
     * @param bool   $showBackupOption Whether to show backup code option
     *
     * @return string HTML for verification form
     */
    public static function generateVerificationForm(string $action, string $csrfToken = '', bool $showBackupOption = true): string
    {
        $csrfInput = $csrfToken ? "<input type='hidden' name='csrf_token' value='{$csrfToken}'>" : '';

        return <<<EOD

            <form method='post' action='{$action}' class='needs-validation' novalidate>
                {$csrfInput}
                <div class='mb-3'>
                    <label for='verification_code' class='form-label'>Enter 6-digit code from your authenticator app</label>
                    <input type='text' class='form-control form-control-lg text-center'
                           id='verification_code' name='verification_code'
                           pattern='[0-9]{6}' maxlength='6' minlength='6'
                           placeholder='123456' required autocomplete='one-time-code'>
                    <div class='invalid-feedback'>Please enter a valid 6-digit code.</div>
                </div>
                <button type='submit' class='btn btn-primary btn-lg w-100 mb-3'>Verify</button>

EOD.($showBackupOption ? <<<'EOD'

                <div class='text-center'>
                    <button type='button' class='btn btn-link' onclick='showBackupCodeForm()'>
                        Use backup recovery code instead
                    </button>
                </div>

EOD : '').<<<'EOD'

            </form>


EOD.($showBackupOption ? <<<EOD

            <form method='post' action='{$action}' class='backup-form' style='display:none;'>
                {$csrfInput}
                <div class='mb-3'>
                    <label for='backup_code' class='form-label'>Enter 8-character backup code</label>
                    <input type='text' class='form-control form-control-lg text-center'
                           id='backup_code' name='verification_code'
                           pattern='[A-Z0-9]{8}' maxlength='8' minlength='8'
                           placeholder='ABC12345' style='text-transform: uppercase;'>
                </div>
                <button type='submit' class='btn btn-warning btn-lg w-100 mb-3'>Use Backup Code</button>
                <div class='text-center'>
                    <button type='button' class='btn btn-link' onclick='showNormalForm()'>
                        Use authenticator app instead
                    </button>
                </div>
            </form>

            <script>
            function showBackupCodeForm() {
                document.querySelector('form:first-of-type').style.display = 'none';
                document.querySelector('.backup-form').style.display = 'block';
            }
            function showNormalForm() {
                document.querySelector('form:first-of-type').style.display = 'block';
                document.querySelector('.backup-form').style.display = 'none';
            }
            </script>

EOD : '');
    }

    /**
     * Get current user ID from session or framework.
     *
     * @return null|int Current user ID or null
     */
    private static function getCurrentUserId(): ?int
    {
        // Try various methods to get current user ID
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        if (isset($_SESSION['USER_ID']) && is_numeric($_SESSION['USER_ID'])) {
            return (int) $_SESSION['USER_ID'];
        }

        // Check if using Ease framework user system
        if (class_exists('\\Ease\\User') && method_exists('\\Ease\\User', 'singleton')) {
            $user = \Ease\User::singleton();

            if (method_exists($user, 'getUserID') && $user->getUserID()) {
                return (int) $user->getUserID();
            }
        }

        return null;
    }
}
