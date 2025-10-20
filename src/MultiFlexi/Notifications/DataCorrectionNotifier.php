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

namespace MultiFlexi\Notifications;

use MultiFlexi\User;
use MultiFlexi\GDPR\UserDataCorrectionRequest;

/**
 * Notification system for GDPR data correction requests
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class DataCorrectionNotifier extends \Ease\Sand
{
    /**
     * Send notification when a new correction request is submitted
     *
     * @param int $requestId Request ID
     * @param User $user User who submitted the request
     *
     * @return bool Success of notification sending
     */
    public function notifyNewRequest(int $requestId, User $user): bool
    {
        $request = new UserDataCorrectionRequest();
        if (!$request->loadFromSQL($requestId)) {
            $this->addStatusMessage('Request not found', 'error');
            return false;
        }

        $success = true;

        // Notify user about successful submission
        $success = $this->sendUserSubmissionConfirmation($user, $request->getData()) && $success;

        // Notify administrators about pending request
        $success = $this->notifyAdministrators($request->getData()) && $success;

        return $success;
    }

    /**
     * Send notification when request is approved
     *
     * @param int $requestId Request ID
     * @param User $reviewer Admin who approved
     * @param string $reviewerNotes Admin's notes
     *
     * @return bool Success of notification sending
     */
    public function notifyRequestApproved(int $requestId, User $reviewer, string $reviewerNotes = ''): bool
    {
        $request = new UserDataCorrectionRequest();
        if (!$request->loadFromSQL($requestId)) {
            $this->addStatusMessage('Request not found', 'error');
            return false;
        }

        $user = new User((int) $request->getDataValue('user_id'));
        if (!$user->getMyKey()) {
            $this->addStatusMessage('User not found', 'error');
            return false;
        }

        return $this->sendApprovalNotification($user, $request->getData(), $reviewer, $reviewerNotes);
    }

    /**
     * Send notification when request is rejected
     *
     * @param int $requestId Request ID
     * @param User $reviewer Admin who rejected
     * @param string $rejectionReason Reason for rejection
     *
     * @return bool Success of notification sending
     */
    public function notifyRequestRejected(int $requestId, User $reviewer, string $rejectionReason): bool
    {
        $request = new UserDataCorrectionRequest();
        if (!$request->loadFromSQL($requestId)) {
            $this->addStatusMessage('Request not found', 'error');
            return false;
        }

        $user = new User((int) $request->getDataValue('user_id'));
        if (!$user->getMyKey()) {
            $this->addStatusMessage('User not found', 'error');
            return false;
        }

        return $this->sendRejectionNotification($user, $request->getData(), $reviewer, $rejectionReason);
    }

    /**
     * Send confirmation email to user after successful request submission
     *
     * @param User $user User who submitted the request
     * @param array $requestData Request data
     *
     * @return bool Success of email sending
     */
    private function sendUserSubmissionConfirmation(User $user, array $requestData): bool
    {
        $email = $user->getEmail();
        if (!$email) {
            return true; // No email to send to, but not an error
        }

        $fieldDisplayName = UserDataCorrectionRequest::getFieldDisplayName($requestData['field_name']);
        
        $subject = _('Data Correction Request Submitted');
        $body = sprintf(
            _('Dear %s,

Your request to change your %s has been successfully submitted for administrator review.

Request Details:
- Field: %s
- Current Value: %s
- Requested Value: %s
- Justification: %s
- Request ID: #%d
- Submitted: %s

You will receive another notification once your request has been reviewed by an administrator.

Thank you for keeping your personal data accurate.

Best regards,
The %s Team'),
            $user->getUserName(),
            $fieldDisplayName,
            $fieldDisplayName,
            $requestData['current_value'],
            $requestData['requested_value'],
            $requestData['justification'] ?: _('(no justification provided)'),
            $requestData['id'],
            date('F j, Y g:i A', strtotime($requestData['created_at'])),
            \Ease\Shared::appName()
        );

        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Notify administrators about a new pending request
     *
     * @param array $requestData Request data
     *
     * @return bool Success of notification sending
     */
    private function notifyAdministrators(array $requestData): bool
    {
        $admins = $this->getAdministrators();
        if (empty($admins)) {
            return true; // No admins to notify
        }

        $fieldDisplayName = UserDataCorrectionRequest::getFieldDisplayName($requestData['field_name']);
        $subject = _('New Data Correction Request Pending Review');
        
        $reviewUrl = $this->getBaseUrl() . '/admin-data-corrections.php';
        
        $body = sprintf(
            _('A new data correction request requires your review.

Request Details:
- Request ID: #%d
- User: %s (ID: %d)
- Field: %s
- Current Value: %s
- Requested Value: %s
- Justification: %s
- Submitted: %s
- IP Address: %s

Please review this request at: %s

This is an automated message from the GDPR compliance system.'),
            $requestData['id'],
            $this->getUserDisplayName((int) $requestData['user_id']),
            $requestData['user_id'],
            $fieldDisplayName,
            $requestData['current_value'],
            $requestData['requested_value'],
            $requestData['justification'] ?: _('(no justification provided)'),
            date('F j, Y g:i A', strtotime($requestData['created_at'])),
            $requestData['requested_by_ip'],
            $reviewUrl
        );

        $success = true;
        foreach ($admins as $admin) {
            if ($admin['email']) {
                $success = $this->sendEmail($admin['email'], $subject, $body) && $success;
            }
        }

        return $success;
    }

    /**
     * Send approval notification to user
     *
     * @param User $user User who submitted the request
     * @param array $requestData Request data
     * @param User $reviewer Admin who approved
     * @param string $reviewerNotes Admin's notes
     *
     * @return bool Success of email sending
     */
    private function sendApprovalNotification(User $user, array $requestData, User $reviewer, string $reviewerNotes): bool
    {
        $email = $user->getEmail();
        if (!$email) {
            return true; // No email to send to
        }

        $fieldDisplayName = UserDataCorrectionRequest::getFieldDisplayName($requestData['field_name']);
        
        $subject = _('Data Correction Request Approved');
        $body = sprintf(
            _('Dear %s,

Great news! Your data correction request has been approved and your personal information has been updated.

Request Details:
- Field: %s
- Previous Value: %s
- New Value: %s
- Request ID: #%d
- Approved by: %s
- Approved on: %s

Administrator Notes: %s

Your personal data has been updated in our system. You can view your updated profile at any time.

Thank you for helping us keep your information accurate.

Best regards,
The %s Team'),
            $user->getUserName(),
            $fieldDisplayName,
            $requestData['current_value'],
            $requestData['requested_value'],
            $requestData['id'],
            $reviewer->getUserName(),
            date('F j, Y g:i A'),
            $reviewerNotes ?: _('(no additional notes)'),
            \Ease\Shared::appName()
        );

        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Send rejection notification to user
     *
     * @param User $user User who submitted the request
     * @param array $requestData Request data
     * @param User $reviewer Admin who rejected
     * @param string $rejectionReason Reason for rejection
     *
     * @return bool Success of email sending
     */
    private function sendRejectionNotification(User $user, array $requestData, User $reviewer, string $rejectionReason): bool
    {
        $email = $user->getEmail();
        if (!$email) {
            return true; // No email to send to
        }

        $fieldDisplayName = UserDataCorrectionRequest::getFieldDisplayName($requestData['field_name']);
        
        $subject = _('Data Correction Request - Decision Required');
        $body = sprintf(
            _('Dear %s,

We have reviewed your data correction request and require additional information before we can proceed.

Request Details:
- Field: %s
- Current Value: %s
- Requested Value: %s
- Request ID: #%d
- Reviewed by: %s
- Reviewed on: %s

Administrator Feedback: %s

If you believe this decision is incorrect or if you have additional supporting information, please contact us directly or submit a new request with more details.

You can view and manage your data correction requests in your profile.

Best regards,
The %s Team'),
            $user->getUserName(),
            $fieldDisplayName,
            $requestData['current_value'],
            $requestData['requested_value'],
            $requestData['id'],
            $reviewer->getUserName(),
            date('F j, Y g:i A'),
            $rejectionReason,
            \Ease\Shared::appName()
        );

        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Send email using available email system
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     *
     * @return bool Success of sending
     */
    private function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            // Use MultiFlexi's existing email system if available
            // This is a basic implementation - you may want to integrate with your existing email system
            $headers = [
                'From' => 'no-reply@' . $_SERVER['HTTP_HOST'] ?? 'multiflexi.local',
                'Reply-To' => 'no-reply@' . $_SERVER['HTTP_HOST'] ?? 'multiflexi.local',
                'X-Mailer' => 'MultiFlexi GDPR System',
                'Content-Type' => 'text/plain; charset=UTF-8'
            ];

            $headerString = '';
            foreach ($headers as $key => $value) {
                $headerString .= "$key: $value\r\n";
            }

            $result = mail($to, $subject, $body, $headerString);
            
            if ($result) {
                $this->addStatusMessage(sprintf(_('Email sent to %s'), $to), 'success');
            } else {
                $this->addStatusMessage(sprintf(_('Failed to send email to %s'), $to), 'error');
            }

            return $result;
        } catch (\Exception $e) {
            $this->addStatusMessage(sprintf(_('Email error: %s'), $e->getMessage()), 'error');
            return false;
        }
    }

    /**
     * Get list of administrators who should receive notifications
     *
     * @return array Array of admin user data
     */
    private function getAdministrators(): array
    {
        $user = new User();
        
        // This query depends on your role/permission system
        // Adjust according to how you identify administrators
        return $user->listingQuery()
            ->select(['id', 'login', 'firstname', 'lastname', 'email'])
            ->where('JSON_EXTRACT(settings, "$.admin") = true OR role = %s', 'admin')
            ->fetchAll();
    }

    /**
     * Get user display name by ID
     *
     * @param int $userId User ID
     *
     * @return string User display name
     */
    private function getUserDisplayName(int $userId): string
    {
        $user = new User($userId);
        return $user->getMyKey() ? $user->getUserName() : "User #$userId";
    }

    /**
     * Get base URL for links in emails
     *
     * @return string Base URL
     */
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        return "$protocol://$host$path";
    }

    /**
     * Get daily digest of pending requests for administrators
     *
     * @return string Digest content
     */
    public function getDailyDigest(): string
    {
        $request = new UserDataCorrectionRequest();
        $pendingRequests = $request->getPendingRequests(50);
        
        if (empty($pendingRequests)) {
            return _('No pending data correction requests.');
        }

        $digest = sprintf(_("Daily GDPR Data Correction Digest - %s\n\n"), date('F j, Y'));
        $digest .= sprintf(_("There are %d pending data correction requests:\n\n"), count($pendingRequests));

        foreach ($pendingRequests as $req) {
            $fieldDisplayName = UserDataCorrectionRequest::getFieldDisplayName($req['field_name']);
            $digest .= sprintf(
                _("- Request #%d: %s (%s) wants to change %s\n"),
                $req['id'],
                $req['login'],
                $req['email'],
                $fieldDisplayName
            );
        }

        $digest .= "\n" . _('Please review these requests in the admin panel.');
        
        return $digest;
    }

    /**
     * Send daily digest to administrators
     *
     * @return bool Success of sending digest
     */
    public function sendDailyDigest(): bool
    {
        $request = new UserDataCorrectionRequest();
        $pendingCount = count($request->getPendingRequests(1));
        
        if ($pendingCount === 0) {
            return true; // No digest to send
        }

        $admins = $this->getAdministrators();
        if (empty($admins)) {
            return true; // No admins to notify
        }

        $subject = sprintf(_('Daily GDPR Digest - %d pending requests'), $pendingCount);
        $body = $this->getDailyDigest();
        
        $success = true;
        foreach ($admins as $admin) {
            if ($admin['email']) {
                $success = $this->sendEmail($admin['email'], $subject, $body) && $success;
            }
        }

        return $success;
    }
}