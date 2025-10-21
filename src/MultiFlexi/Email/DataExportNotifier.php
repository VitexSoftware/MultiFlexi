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

namespace MultiFlexi\Email;

/**
 * Email Notification System for GDPR Data Export.
 *
 * Sends notifications about data export requests and completions
 * to comply with GDPR transparency requirements
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DataExportNotifier extends \MultiFlexi\Engine
{
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        parent::__construct();

        // Configure email settings
        $this->fromEmail = \Ease\Shared::cfg('MAIL_FROM') ?? 'noreply@'.($_SERVER['SERVER_NAME'] ?? 'multiflexi.local');
        $this->fromName = \Ease\Shared::cfg('MAIL_FROM_NAME') ?? 'MultiFlexi GDPR System';
    }

    /**
     * Send data export request notification.
     *
     * @param int    $userId      User who requested export
     * @param string $format      Export format
     * @param string $downloadUrl Download URL
     * @param string $expiresAt   Expiration datetime
     *
     * @return bool Success status
     */
    public function sendExportReadyNotification(int $userId, string $format, string $downloadUrl, string $expiresAt): bool
    {
        $user = new \MultiFlexi\User($userId);
        $userEmail = $user->getEmail();

        if (empty($userEmail)) {
            $this->addStatusMessage('User has no email address for notification', 'warning');

            return false;
        }

        $userName = $user->getUserName();
        $subject = _('Your Personal Data Export is Ready');

        $body = self::generateExportReadyEmailBody($userName, $format, $downloadUrl, $expiresAt);

        return $this->sendEmail($userEmail, $userName, $subject, $body);
    }

    /**
     * Send data export completion notification (after download).
     *
     * @param int    $userId       User who downloaded export
     * @param string $format       Export format
     * @param string $downloadTime Download timestamp
     *
     * @return bool Success status
     */
    public function sendExportDownloadedNotification(int $userId, string $format, string $downloadTime): bool
    {
        $user = new \MultiFlexi\User($userId);
        $userEmail = $user->getEmail();

        if (empty($userEmail)) {
            return false; // No email available
        }

        $userName = $user->getUserName();
        $subject = _('Personal Data Export Downloaded - Confirmation');

        $body = self::generateDownloadConfirmationEmailBody($userName, $format, $downloadTime);

        return $this->sendEmail($userEmail, $userName, $subject, $body);
    }

    /**
     * Send security alert for suspicious export activity.
     *
     * @param int    $userId    User account involved
     * @param string $alertType Type of alert
     * @param array  $details   Alert details
     *
     * @return bool Success status
     */
    public function sendSecurityAlert(int $userId, string $alertType, array $details): bool
    {
        $user = new \MultiFlexi\User($userId);
        $userEmail = $user->getEmail();

        if (empty($userEmail)) {
            return false;
        }

        $userName = $user->getUserName();
        $subject = _('Security Alert: Unusual Data Export Activity');

        $body = self::generateSecurityAlertEmailBody($userName, $alertType, $details);

        return $this->sendEmail($userEmail, $userName, $subject, $body);
    }

    /**
     * Generate email body for export ready notification.
     */
    private static function generateExportReadyEmailBody(string $userName, string $format, string $downloadUrl, string $expiresAt): string
    {
        $siteName = \Ease\Shared::cfg('APP_NAME') ?? 'MultiFlexi';
        $baseUrl = 'https://'.($_SERVER['SERVER_NAME'] ?? 'multiflexi.local');

        return <<<EOD
Dear {$userName},

Your request for a personal data export has been processed and is now ready for download.

EXPORT DETAILS:
- Format: {$format}
- Generated: " . date('Y-m-d H:i:s') . "
- Expires: {$expiresAt}

DOWNLOAD YOUR DATA:
Click the following secure link to download your personal data:
{$baseUrl}{$downloadUrl}

IMPORTANT SECURITY INFORMATION:
- This download link is valid for 1 hour only
- The link can only be used once
- You must be logged into your {$siteName} account to access the download
- The link is tied to your current session for security

WHAT'S INCLUDED:
Your export contains all personal data we hold about you, including:
- Your user profile information
- Activity logs and audit trails
- Consent records and preferences
- Company associations and roles
- Credential metadata (not actual passwords)
- Communication preferences

GDPR COMPLIANCE:
This export fulfills your rights under GDPR Article 15 (Right of Access). The data is provided in a structured, machine-readable format as required by law.

If you did not request this export, please contact us immediately at the address below.

For questions about your personal data or GDPR rights, please contact our Data Protection Officer.

Best regards,
{$siteName} GDPR System

---
This is an automated message. Please do not reply to this email.
For support, visit: {$baseUrl}/privacy-policy.php
EOD;
    }

    /**
     * Generate email body for download confirmation.
     */
    private static function generateDownloadConfirmationEmailBody(string $userName, string $format, string $downloadTime): string
    {
        $siteName = \Ease\Shared::cfg('APP_NAME') ?? 'MultiFlexi';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        return <<<EOD
Dear {$userName},

This email confirms that your personal data export was successfully downloaded.

DOWNLOAD DETAILS:
- Format: {$format}
- Downloaded: {$downloadTime}
- IP Address: {$ipAddress}

SECURITY REMINDER:
- Your personal data export contains sensitive information
- Store the downloaded file securely
- Delete the file when no longer needed
- Do not share your personal data export with others

GDPR COMPLIANCE:
This download has been logged in our audit trail as required by GDPR Article 30 (Records of processing activities).

If you did not download this export, please contact us immediately as this may indicate unauthorized access to your account.

Best regards,
{$siteName} GDPR System

---
This is an automated message. Please do not reply to this email.
EOD;
    }

    /**
     * Generate security alert email body.
     */
    private static function generateSecurityAlertEmailBody(string $userName, string $alertType, array $details): string
    {
        $siteName = \Ease\Shared::cfg('APP_NAME') ?? 'MultiFlexi';
        $timestamp = date('Y-m-d H:i:s');

        return <<<EOD
Dear {$userName},

We detected unusual activity related to data export requests on your account.

ALERT DETAILS:
- Alert Type: {$alertType}
- Time: {$timestamp}
- Details: " . json_encode({$details}, JSON_PRETTY_PRINT) . "

RECOMMENDED ACTIONS:
- Review your recent account activity
- Change your password if you suspect unauthorized access
- Contact us if you did not initiate these requests

SECURITY MEASURES:
- We have automatically applied additional security measures to your account
- Future export requests may require additional verification
- All activity is being logged for security purposes

If you recognize this activity as legitimate, no action is required. The security measures will be automatically lifted after a review period.

For immediate assistance, please contact our security team.

Best regards,
{$siteName} Security Team

---
This is an automated security alert. Please do not reply to this email.
EOD;
    }

    /**
     * Send email using configured mailer.
     */
    private function sendEmail(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            // Use the existing MultiFlexi mailer if available
            if (class_exists('\\Ease\\Mailer')) {
                $mailer = new \Ease\Mailer($toEmail, $subject, $body);
                $mailer->setFromEmail($this->fromEmail);
                $mailer->setFromName($this->fromName);

                $success = $mailer->send();

                if ($success) {
                    $this->addStatusMessage("GDPR notification sent to {$toEmail}", 'success');
                } else {
                    $this->addStatusMessage("Failed to send GDPR notification to {$toEmail}", 'error');
                }

                return $success;
            }

            // Fallback to PHP mail() function
            $headers = [
                'From: '.$this->fromName.' <'.$this->fromEmail.'>',
                'Reply-To: '.$this->fromEmail,
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: MultiFlexi GDPR System',
            ];

            $success = mail($toEmail, $subject, $body, implode("\r\n", $headers));

            if ($success) {
                $this->addStatusMessage("GDPR notification sent to {$toEmail}", 'success');

                // Log the email for audit purposes
                self::logEmailSent($toEmail, $subject);
            } else {
                $this->addStatusMessage("Failed to send GDPR notification to {$toEmail}", 'error');
            }

            return $success;
        } catch (\Exception $e) {
            $this->addStatusMessage('Email error: '.$e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Log sent email for audit purposes.
     */
    private static function logEmailSent(string $toEmail, string $subject): void
    {
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';

        $logEngine->insertToSQL([
            'user_id' => null, // System email
            'severity' => 'info',
            'venue' => 'DataExportNotifier',
            'message' => "GDPR notification email sent to {$toEmail}: {$subject}",
            'created' => date('Y-m-d H:i:s'),
        ]);
    }
}
