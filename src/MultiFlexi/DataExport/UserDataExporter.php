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

namespace MultiFlexi\DataExport;

use MultiFlexi\Consent\ConsentManager;

/**
 * User Data Exporter for GDPR Article 15 - Right of Access.
 *
 * Collects all personal data associated with a user from various tables
 * and provides it in structured formats (JSON/PDF)
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class UserDataExporter extends \MultiFlexi\Engine
{
    /**
     * Export all user personal data.
     *
     * @param int $userId User ID to export data for
     *
     * @return array Complete user data export
     */
    public function exportUserData(int $userId): array
    {
        return [
            'export_metadata' => self::getExportMetadata($userId),
            'user_profile' => self::getUserProfileData($userId),
            'company_associations' => self::getCompanyAssociations($userId),
            'credentials' => $this->getCredentialMetadata($userId),
            'activity_logs' => $this->getActivityLogs($userId),
            'job_history' => $this->getJobHistory($userId),
            'consent_records' => $this->getConsentRecords($userId),
            'session_history' => self::getSessionHistory($userId),
            'audit_trails' => $this->getAuditTrails($userId),
        ];
    }

    /**
     * Generate PDF export.
     *
     * @param array $data Export data
     *
     * @return string PDF content
     */
    public function generatePDF(array $data): string
    {
        // Simple PDF generation - in production, use proper PDF library
        $content = "MultiFlexi - Personal Data Export\n";
        $content .= 'Generated: '.$data['export_metadata']['export_date']."\n\n";

        foreach ($data as $section => $sectionData) {
            if ($section === 'export_metadata') {
                continue;
            }

            $content .= strtoupper(str_replace('_', ' ', $section))."\n";
            $content .= str_repeat('-', \strlen($section))."\n";
            $content .= self::formatSectionForPDF($sectionData)."\n\n";
        }

        return $content;
    }

    /**
     * Get export metadata.
     */
    private static function getExportMetadata(int $userId): array
    {
        return [
            'export_date' => date('c'),
            'user_id' => $userId,
            'export_version' => '1.0',
            'gdpr_article' => 'Article 15 - Right of Access',
            'data_controller' => 'MultiFlexi',
            'retention_info' => 'Data is retained according to our privacy policy',
            'contact_info' => 'For questions about this export, contact your data protection officer',
        ];
    }

    /**
     * Get user profile data from user table.
     */
    private static function getUserProfileData(int $userId): array
    {
        $userEngine = new \Ease\SQL\Engine();
        $userEngine->myTable = 'user';

        $userData = $userEngine->listingQuery()
            ->select('id, login, email, firstname, lastname, enabled, DatCreate, DatSave, last_modifier_id')
            ->where(['id' => $userId])
            ->fetch();

        if (!$userData) {
            return [];
        }

        // Remove internal IDs and add human-readable information
        $profile = [
            'user_id' => (int) $userData['id'],
            'username' => $userData['login'],
            'email' => $userData['email'],
            'first_name' => $userData['firstname'],
            'last_name' => $userData['lastname'],
            'account_enabled' => (bool) $userData['enabled'],
            'account_created' => $userData['DatCreate'],
            'last_updated' => $userData['DatSave'],
            'last_modified_by' => $userData['last_modifier_id'] ? 'User ID: '.$userData['last_modifier_id'] : null,
        ];

        // Get user settings (if any)
        $userObj = new \MultiFlexi\User($userId);
        $settings = $userObj->getData()['settings'] ?? null;

        if ($settings) {
            $profile['user_settings'] = json_decode($settings, true);
        }

        return $profile;
    }

    /**
     * Get company associations.
     */
    private static function getCompanyAssociations(int $userId): array
    {
        // This would need to be implemented based on your company-user association logic
        // For now, returning companies where user has activities

        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';

        $companyIds = $logEngine->listingQuery()
            ->select('DISTINCT company_id')
            ->where(['user_id' => $userId])
            ->where('company_id IS NOT NULL')
            ->fetchAll();

        $companies = [];

        foreach ($companyIds as $companyId) {
            $companyEngine = new \Ease\SQL\Engine();
            $companyEngine->myTable = 'company';

            $company = $companyEngine->listingQuery()
                ->select('id, company, nazev, ic, enabled, DatCreate')
                ->where(['id' => $companyId['company_id']])
                ->fetch();

            if ($company) {
                $companies[] = [
                    'company_id' => (int) $company['id'],
                    'company_code' => $company['company'],
                    'company_name' => $company['nazev'],
                    'company_identifier' => $company['ic'],
                    'enabled' => (bool) $company['enabled'],
                    'associated_since' => $company['DatCreate'],
                    'relationship_type' => 'user_activity', // Based on activity logs
                ];
            }
        }

        return $companies;
    }

    /**
     * Get credential metadata (NOT actual passwords/secrets).
     */
    private function getCredentialMetadata(int $userId): array
    {
        // Get credentials associated with companies the user has access to
        $companyAssociations = self::getCompanyAssociations($userId);
        $companyIds = array_column($companyAssociations, 'company_id');

        if (empty($companyIds)) {
            return [];
        }

        $credEngine = new \Ease\SQL\Engine();
        $credEngine->myTable = 'credentials';

        $credentials = $credEngine->listingQuery()
            ->select('id, name, company_id, formType')
            ->where(['company_id' => $companyIds])
            ->fetchAll();

        return array_map(static function ($cred) {
            return [
                'credential_id' => (int) $cred['id'],
                'credential_name' => $cred['name'],
                'company_id' => (int) $cred['company_id'],
                'credential_type' => $cred['formType'],
                'note' => 'Actual credential values are not included for security reasons',
            ];
        }, $credentials);
    }

    /**
     * Get activity logs for user.
     */
    private function getActivityLogs(int $userId): array
    {
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';

        $logs = $logEngine->listingQuery()
            ->select('id, company_id, apps_id, severity, venue, message, created')
            ->where(['user_id' => $userId])
            ->orderBy('created DESC')
            ->limit(1000) // Limit to prevent huge exports
            ->fetchAll();

        return array_map(static function ($log) {
            return [
                'log_id' => (int) $log['id'],
                'company_id' => $log['company_id'] ? (int) $log['company_id'] : null,
                'application_id' => $log['apps_id'] ? (int) $log['apps_id'] : null,
                'severity' => $log['severity'],
                'source' => $log['venue'],
                'message' => $log['message'],
                'timestamp' => $log['created'],
            ];
        }, $logs);
    }

    /**
     * Get job execution history for user.
     */
    private function getJobHistory(int $userId): array
    {
        // Get jobs associated with companies the user has access to
        $companyAssociations = self::getCompanyAssociations($userId);
        $companyIds = array_column($companyAssociations, 'company_id');

        if (empty($companyIds)) {
            return [];
        }

        $jobEngine = new \Ease\SQL\Engine();
        $jobEngine->myTable = 'job';

        $jobs = $jobEngine->listingQuery()
            ->select('id, app_id, begin, end, company_id, exitcode')
            ->where(['company_id' => $companyIds])
            ->orderBy('begin DESC')
            ->limit(500) // Limit to prevent huge exports
            ->fetchAll();

        return array_map(static function ($job) {
            return [
                'job_id' => (int) $job['id'],
                'application_id' => (int) $job['app_id'],
                'company_id' => (int) $job['company_id'],
                'started_at' => $job['begin'],
                'completed_at' => $job['end'],
                'exit_code' => $job['exitcode'],
                'status' => $job['exitcode'] === null ? 'running' : ($job['exitcode'] === 0 ? 'success' : 'failed'),
            ];
        }, $jobs);
    }

    /**
     * Get consent records for user.
     */
    private function getConsentRecords(int $userId): array
    {
        $consentManager = new ConsentManager();

        // Get all consent history, not just current
        $consentEngine = new \Ease\SQL\Engine();
        $consentEngine->myTable = 'consent';

        $consents = $consentEngine->listingQuery()
            ->select('id, consent_type, consent_status, consent_details, consent_version, expires_at, withdrawn_at, DatCreate, ip_address')
            ->where(['user_id' => $userId])
            ->orderBy('DatCreate DESC')
            ->fetchAll();

        $consentData = array_map(static function ($consent) {
            return [
                'consent_id' => (int) $consent['id'],
                'consent_type' => $consent['consent_type'],
                'status' => $consent['consent_status'] ? 'granted' : 'denied',
                'details' => $consent['consent_details'] ? json_decode($consent['consent_details'], true) : null,
                'policy_version' => $consent['consent_version'],
                'granted_at' => $consent['DatCreate'],
                'expires_at' => $consent['expires_at'],
                'withdrawn_at' => $consent['withdrawn_at'],
                'ip_address' => $consent['ip_address'],
            ];
        }, $consents);

        // Get consent audit log
        $auditEngine = new \Ease\SQL\Engine();
        $auditEngine->myTable = 'consent_log';

        $auditLogs = $auditEngine->listingQuery()
            ->select('action, consent_type, old_value, new_value, DatCreate, ip_address')
            ->where(['user_id' => $userId])
            ->orderBy('DatCreate DESC')
            ->fetchAll();

        $auditData = array_map(static function ($audit) {
            return [
                'action' => $audit['action'],
                'consent_type' => $audit['consent_type'],
                'old_value' => $audit['old_value'] ? json_decode($audit['old_value'], true) : null,
                'new_value' => $audit['new_value'] ? json_decode($audit['new_value'], true) : null,
                'timestamp' => $audit['DatCreate'],
                'ip_address' => $audit['ip_address'],
            ];
        }, $auditLogs);

        return [
            'consent_records' => $consentData,
            'consent_audit_trail' => $auditData,
        ];
    }

    /**
     * Get session history (if available).
     */
    private static function getSessionHistory(int $userId): array
    {
        // Note: PHP sessions are typically not stored in database
        // This would need to be implemented if session data is stored in DB
        // For now, return empty array with explanation

        return [
            'note' => 'Session history is not permanently stored in the database',
            'current_session_info' => [
                'session_id' => session_id(),
                'started_at' => $_SESSION['login_time'] ?? 'unknown',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ],
        ];
    }

    /**
     * Get additional audit trails.
     */
    private function getAuditTrails(int $userId): array
    {
        // Additional audit data beyond consent logs
        $trails = [];

        // Data export requests
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';

        $exportLogs = $logEngine->listingQuery()
            ->select('message, created')
            ->where(['user_id' => $userId, 'venue' => 'DataExportApi'])
            ->orderBy('created DESC')
            ->fetchAll();

        $trails['data_export_requests'] = array_map(static function ($log) {
            return [
                'message' => $log['message'],
                'timestamp' => $log['created'],
            ];
        }, $exportLogs);

        return $trails;
    }

    /**
     * Format section data for PDF.
     *
     * @param mixed $data
     */
    private static function formatSectionForPDF($data): string
    {
        if (\is_array($data)) {
            return json_encode($data, \JSON_PRETTY_PRINT);
        }

        return (string) $data;
    }
}
