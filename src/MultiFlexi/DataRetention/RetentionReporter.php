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

namespace MultiFlexi\DataRetention;

use Ease\SQL\Orm;
use MultiFlexi\User;

/**
 * Data Retention Reporter.
 *
 * Generates reports and confirmations for data retention and deletion activities.
 * Provides compliance documentation and audit trails for GDPR compliance.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class RetentionReporter extends \Ease\Sand
{
    /**
     * @var Orm Database handle for retention reports
     */
    private Orm $reports;
    private RetentionService $retentionService;
    private DataArchiver $archiver;

    /**
     * @var null|User Current user
     */
    private ?User $currentUser = null;

    /**
     * Constructor.
     *
     * @param null|User $user User generating reports
     */
    public function __construct(?User $user = null)
    {
        parent::__construct();
        $this->reports = new Orm();
        $this->reports->setMyTable('retention_reports');
        $this->retentionService = new RetentionService();
        $this->archiver = new DataArchiver();
        $this->currentUser = $user;
    }

    /**
     * Generate daily retention summary report.
     *
     * @param array $data Report data
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    public function generateDailyReport(array $data): int
    {
        $reportDate = new \DateTime();
        $startDate = (clone $reportDate)->setTime(0, 0, 0);
        $endDate = (clone $reportDate)->setTime(23, 59, 59);

        $reportData = [
            'retention_policies_applied' => \count($this->retentionService->getActivePolicies()),
            'cleanup_jobs_run' => $data['jobs_processed'] ?? 0,
            'records_deleted' => $data['records_deleted'] ?? 0,
            'records_anonymized' => $data['records_anonymized'] ?? 0,
            'records_archived' => $data['records_archived'] ?? 0,
            'dry_run_mode' => $data['dry_run'] ?? false,
            'cleanup_date' => $data['cleanup_date'] ?? $reportDate,
            'errors_encountered' => $data['errors'] ?? [],
        ];

        $summary = sprintf(
            _('Daily retention cleanup: %d jobs processed, %d records deleted, %d anonymized, %d archived'),
            $reportData['cleanup_jobs_run'],
            $reportData['records_deleted'],
            $reportData['records_anonymized'],
            $reportData['records_archived'],
        );

        return $this->createReport(
            'daily_summary',
            $startDate,
            $endDate,
            $reportData,
            $summary,
        );
    }

    /**
     * Generate weekly retention summary report.
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    public function generateWeeklyReport(): int
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->sub(new \DateInterval('P7D'));

        $cleanupStats = $this->retentionService->getCleanupStatistics(7);
        $archiveStats = $this->archiver->getArchiveStatistics(7);

        $reportData = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'cleanup_statistics' => $cleanupStats,
            'archive_statistics' => $archiveStats,
            'compliance_summary' => $this->generateComplianceSummary(7),
        ];

        $summary = sprintf(
            _('Weekly retention summary: %d jobs completed, %d records processed, %d archives created'),
            $cleanupStats['completed_jobs'],
            $cleanupStats['total_records_processed'],
            $archiveStats['total_archived'],
        );

        return $this->createReport(
            'weekly_summary',
            $startDate,
            $endDate,
            $reportData,
            $summary,
        );
    }

    /**
     * Generate monthly retention summary report.
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    public function generateMonthlyReport(): int
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->sub(new \DateInterval('P1M'));

        $cleanupStats = $this->retentionService->getCleanupStatistics(30);
        $archiveStats = $this->archiver->getArchiveStatistics(30);

        $reportData = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'cleanup_statistics' => $cleanupStats,
            'archive_statistics' => $archiveStats,
            'policy_effectiveness' => self::analyzePolicyEffectiveness(),
            'compliance_summary' => $this->generateComplianceSummary(30),
            'recommendations' => self::generateRecommendations($cleanupStats, $archiveStats),
        ];

        $summary = sprintf(
            _('Monthly retention summary: %d jobs completed, %d records processed, compliance score: %s'),
            $cleanupStats['completed_jobs'],
            $cleanupStats['total_records_processed'],
            $reportData['compliance_summary']['score'],
        );

        return $this->createReport(
            'monthly_summary',
            $startDate,
            $endDate,
            $reportData,
            $summary,
        );
    }

    /**
     * Generate policy audit report.
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    public function generatePolicyAuditReport(): int
    {
        $reportDate = new \DateTime();
        $startDate = (clone $reportDate)->sub(new \DateInterval('P1Y'));

        $policies = $this->retentionService->getActivePolicies();
        $auditData = [];

        foreach ($policies as $policy) {
            $auditData[] = [
                'policy_name' => $policy['policy_name'],
                'table_name' => $policy['table_name'],
                'retention_period_days' => $policy['retention_period_days'],
                'deletion_action' => $policy['deletion_action'],
                'legal_basis' => $policy['legal_basis'],
                'last_executed' => self::getLastExecutionDate($policy['id']),
                'records_affected' => self::getRecordsAffectedByPolicy($policy['id']),
                'compliance_status' => self::evaluatePolicyCompliance($policy),
            ];
        }

        $reportData = [
            'audit_date' => $reportDate,
            'total_policies' => \count($policies),
            'active_policies' => \count(array_filter($policies, static fn ($p) => $p['enabled'])),
            'policy_details' => $auditData,
            'overall_compliance' => self::calculateOverallCompliance($auditData),
        ];

        $summary = sprintf(
            _('Policy audit: %d total policies, %d active, overall compliance: %s'),
            $reportData['total_policies'],
            $reportData['active_policies'],
            $reportData['overall_compliance']['status'],
        );

        return $this->createReport(
            'policy_audit',
            $startDate,
            $reportDate,
            $reportData,
            $summary,
        );
    }

    /**
     * Generate compliance report.
     *
     * @param int $days Period to analyze
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    public function generateComplianceReport(int $days = 30): int
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->sub(new \DateInterval('P'.$days.'D'));

        $complianceData = [
            'assessment_period' => $days,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'gdpr_compliance' => $this->assessGDPRCompliance($days),
            'data_minimization' => $this->assessDataMinimization(),
            'retention_effectiveness' => self::assessRetentionEffectiveness($days),
            'audit_trail_completeness' => self::assessAuditTrailCompleteness($days),
            'recommendations' => self::generateComplianceRecommendations(),
        ];

        $overallScore = self::calculateComplianceScore($complianceData);

        $summary = sprintf(
            _('Compliance assessment: Overall score %d/100, GDPR compliance: %s'),
            $overallScore,
            $complianceData['gdpr_compliance']['status'],
        );

        return $this->createReport(
            'compliance_report',
            $startDate,
            $endDate,
            $complianceData,
            $summary,
        );
    }

    /**
     * Generate deletion confirmation report.
     *
     * @param array $deletionData Deletion job data
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    public function generateDeletionConfirmation(array $deletionData): int
    {
        $reportDate = new \DateTime();

        $confirmationData = [
            'deletion_date' => $deletionData['deletion_date'] ?? $reportDate,
            'deletion_job_id' => $deletionData['job_id'] ?? null,
            'deletion_type' => $deletionData['deletion_type'] ?? 'unknown',
            'affected_tables' => $deletionData['tables'] ?? [],
            'records_deleted' => $deletionData['records_deleted'] ?? 0,
            'records_anonymized' => $deletionData['records_anonymized'] ?? 0,
            'records_archived' => $deletionData['records_archived'] ?? 0,
            'legal_basis' => $deletionData['legal_basis'] ?? 'GDPR Article 5(1)(e) - Data minimization',
            'retention_policy_applied' => $deletionData['policy_name'] ?? null,
            'data_subject_impact' => $deletionData['data_subjects_affected'] ?? 0,
            'verification_hash' => self::generateVerificationHash($deletionData),
        ];

        $summary = sprintf(
            _('Data deletion confirmation: %d records deleted, %d anonymized, %d archived on %s'),
            $confirmationData['records_deleted'],
            $confirmationData['records_anonymized'],
            $confirmationData['records_archived'],
            $confirmationData['deletion_date']->format('Y-m-d H:i:s'),
        );

        return $this->createReport(
            'deletion_confirmation',
            $confirmationData['deletion_date'],
            $confirmationData['deletion_date'],
            $confirmationData,
            $summary,
            self::generateDeletionReportFile($confirmationData),
        );
    }

    /**
     * Get reports by type and period.
     *
     * @param string $type Report type
     * @param int    $days Number of days to look back
     *
     * @return array Reports
     */
    public function getReports(string $type, int $days = 30): array
    {
        $startDate = (new \DateTime())->sub(new \DateInterval('P'.$days.'D'));

        return $this->reports->listingQuery()
            ->where('report_type', $type)
            ->where('generated_at', '>=', $startDate)
            ->orderBy('generated_at', 'DESC')
            ->fetchAll();
    }

    /**
     * Get latest report by type.
     *
     * @param string $type Report type
     *
     * @return null|array Latest report
     */
    public function getLatestReport(string $type): ?array
    {
        $report = $this->reports->listingQuery()
            ->where('report_type', $type)
            ->orderBy('generated_at', 'DESC')
            ->fetch();

        return $report ?: null;
    }

    /**
     * Export report to file.
     *
     * @param int         $reportId Report ID
     * @param string      $format   Export format (json, txt, pdf)
     * @param null|string $filePath Optional output file path
     *
     * @throws \Exception
     *
     * @return string File path
     */
    public function exportReport(int $reportId, string $format = 'json', ?string $filePath = null): string
    {
        $this->reports->loadFromSQL($reportId);
        $reportData = $this->reports->getData();

        if (empty($reportData)) {
            throw new \Exception(_('Report not found'));
        }

        if (!$filePath) {
            $timestamp = (new \DateTime())->format('Y-m-d_H-i-s');
            $filePath = sys_get_temp_dir()."/retention_report_{$reportId}_{$timestamp}.{$format}";
        }

        switch ($format) {
            case 'json':
                file_put_contents($filePath, json_encode($reportData, \JSON_PRETTY_PRINT));

                break;
            case 'txt':
                $textReport = self::formatReportAsText($reportData);
                file_put_contents($filePath, $textReport);

                break;

            default:
                throw new \Exception(sprintf(_('Unsupported export format: %s'), $format));
        }

        return $filePath;
    }

    /**
     * Create a new report record.
     *
     * @param string      $type        Report type
     * @param \DateTime   $periodStart Period start
     * @param \DateTime   $periodEnd   Period end
     * @param array       $data        Report data
     * @param string      $summary     Summary text
     * @param null|string $filePath    Optional file path
     *
     * @throws \Exception
     *
     * @return int Report ID
     */
    private function createReport(
        string $type,
        \DateTime $periodStart,
        \DateTime $periodEnd,
        array $data,
        string $summary,
        ?string $filePath = null,
    ): int {
        $userId = $this->currentUser ? $this->currentUser->getId() : self::getCurrentUserId();

        if (!$userId) {
            throw new \Exception(_('No user available for report generation'));
        }

        $reportRecord = [
            'report_type' => $type,
            'report_period_start' => $periodStart,
            'report_period_end' => $periodEnd,
            'generated_by' => $userId,
            'report_data' => json_encode($data, \JSON_PRETTY_PRINT),
            'summary' => $summary,
            'file_path' => $filePath,
            'generated_at' => new \DateTime(),
        ];

        if ($this->reports->insertToSQL($reportRecord)) {
            $reportId = $this->reports->getLastInsertID();

            $this->addStatusMessage(
                sprintf(_('Generated %s report (ID: %d)'), $type, $reportId),
                'info',
            );

            return $reportId;
        }

        throw new \Exception(sprintf(_('Failed to create %s report'), $type));
    }

    /**
     * Generate compliance summary.
     *
     * @param int $days Period to analyze
     *
     * @return array Compliance summary
     */
    private function generateComplianceSummary(int $days): array
    {
        $gdprCompliance = $this->assessGDPRCompliance($days);
        $dataMinimization = $this->assessDataMinimization();

        return [
            'score' => self::calculateComplianceScore([
                'gdpr_compliance' => $gdprCompliance,
                'data_minimization' => $dataMinimization,
            ]),
            'gdpr_status' => $gdprCompliance['status'],
            'data_minimization_score' => $dataMinimization['score'],
            'areas_for_improvement' => array_merge(
                $gdprCompliance['issues'] ?? [],
                $dataMinimization['issues'] ?? [],
            ),
        ];
    }

    /**
     * Assess GDPR compliance.
     *
     * @param int $days Period to analyze
     *
     * @return array GDPR compliance assessment
     */
    private function assessGDPRCompliance(int $days): array
    {
        $issues = [];
        $score = 100;

        // Check if retention policies are defined and active
        $activePolicies = $this->retentionService->getActivePolicies();

        if (empty($activePolicies)) {
            $issues[] = _('No active data retention policies defined');
            $score -= 30;
        }

        // Check if cleanup jobs are running regularly
        $cleanupStats = $this->retentionService->getCleanupStatistics($days);

        if ($cleanupStats['total_jobs'] === 0) {
            $issues[] = _('No cleanup jobs executed in the analyzed period');
            $score -= 20;
        }

        // Check for failed jobs
        if ($cleanupStats['failed_jobs'] > 0) {
            $failureRate = ($cleanupStats['failed_jobs'] / max(1, $cleanupStats['total_jobs'])) * 100;

            if ($failureRate > 10) {
                $issues[] = sprintf(_('High cleanup job failure rate: %.1f%%'), $failureRate);
                $score -= 15;
            }
        }

        // Check for expired records not being processed
        $expiredRecords = $this->retentionService->findExpiredRecords();
        $totalExpired = array_sum(array_map('count', $expiredRecords));

        if ($totalExpired > 1000) {
            $issues[] = sprintf(_('%d expired records awaiting cleanup'), $totalExpired);
            $score -= 10;
        }

        return [
            'status' => $score >= 80 ? 'compliant' : ($score >= 60 ? 'partially_compliant' : 'non_compliant'),
            'score' => max(0, $score),
            'issues' => $issues,
            'last_assessed' => new \DateTime(),
        ];
    }

    /**
     * Assess data minimization compliance.
     *
     * @return array Data minimization assessment
     */
    private function assessDataMinimization(): array
    {
        $issues = [];
        $score = 100;

        // Check for very old user accounts
        $userTable = new Orm();
        $userTable->setMyTable('user');

        $oldUsers = $userTable->listingQuery()
            ->where('last_activity_at', '<=', (new \DateTime())->sub(new \DateInterval('P3Y')))
            ->where('deleted_at', null)
            ->count();

        if ($oldUsers > 0) {
            $issues[] = sprintf(_('%d user accounts inactive for more than 3 years'), $oldUsers);
            $score -= 20;
        }

        // Check for excessive log retention
        $logTable = new Orm();
        $logTable->setMyTable('log');

        $oldLogs = $logTable->listingQuery()
            ->where('created', '<=', (new \DateTime())->sub(new \DateInterval('P2Y')))
            ->where('marked_for_deletion', false)
            ->count();

        if ($oldLogs > 10000) {
            $issues[] = sprintf(_('%d old log records not scheduled for deletion'), $oldLogs);
            $score -= 15;
        }

        return [
            'score' => max(0, $score),
            'issues' => $issues,
            'recommendations' => $this->generateDataMinimizationRecommendations(),
        ];
    }

    /**
     * Generate data minimization recommendations.
     *
     * @return array Recommendations
     */
    private function generateDataMinimizationRecommendations(): array
    {
        $recommendations = [];

        $expiredRecords = $this->retentionService->findExpiredRecords();

        if (!empty($expiredRecords)) {
            $recommendations[] = _('Execute cleanup command to process expired records');
        }

        $cleanupStats = $this->retentionService->getCleanupStatistics(30);

        if ($cleanupStats['failed_jobs'] > 0) {
            $recommendations[] = _('Review and fix failed cleanup jobs');
        }

        if (empty($recommendations)) {
            $recommendations[] = _('Data minimization practices are well implemented');
        }

        return $recommendations;
    }

    /**
     * Calculate overall compliance score.
     *
     * @param array $complianceData Compliance assessment data
     *
     * @return int Compliance score (0-100)
     */
    private static function calculateComplianceScore(array $complianceData): int
    {
        $totalScore = 0;
        $components = 0;

        if (isset($complianceData['gdpr_compliance']['score'])) {
            $totalScore += $complianceData['gdpr_compliance']['score'];
            ++$components;
        }

        if (isset($complianceData['data_minimization']['score'])) {
            $totalScore += $complianceData['data_minimization']['score'];
            ++$components;
        }

        return $components > 0 ? (int) ($totalScore / $components) : 0;
    }

    /**
     * Generate verification hash for deletion confirmation.
     *
     * @param array $deletionData Deletion data
     *
     * @return string Verification hash
     */
    private static function generateVerificationHash(array $deletionData): string
    {
        $hashData = [
            'deletion_date' => $deletionData['deletion_date']->format('Y-m-d H:i:s'),
            'records_deleted' => $deletionData['records_deleted'],
            'records_anonymized' => $deletionData['records_anonymized'],
            'records_archived' => $deletionData['records_archived'],
        ];

        return hash('sha256', json_encode($hashData));
    }

    /**
     * Generate deletion report file.
     *
     * @param array $confirmationData Deletion confirmation data
     *
     * @return string File path
     */
    private static function generateDeletionReportFile(array $confirmationData): string
    {
        $timestamp = $confirmationData['deletion_date']->format('Y-m-d_H-i-s');
        $filename = "deletion_confirmation_{$timestamp}.txt";
        $filepath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.$filename;

        $report = sprintf(
            "MultiFlexi Data Deletion Confirmation Report\n".
            "============================================\n\n".
            "Deletion Date: %s\n".
            "Deletion Job ID: %s\n".
            "Deletion Type: %s\n".
            "Legal Basis: %s\n".
            "Retention Policy: %s\n\n".
            "DELETION SUMMARY\n".
            "================\n".
            "Records Deleted: %d\n".
            "Records Anonymized: %d\n".
            "Records Archived: %d\n".
            "Data Subjects Affected: %d\n\n".
            "AFFECTED TABLES\n".
            "===============\n%s\n\n".
            "VERIFICATION\n".
            "============\n".
            "Verification Hash: %s\n".
            "Generated At: %s\n",
            $confirmationData['deletion_date']->format('Y-m-d H:i:s'),
            $confirmationData['deletion_job_id'] ?? 'N/A',
            $confirmationData['deletion_type'],
            $confirmationData['legal_basis'],
            $confirmationData['retention_policy_applied'] ?? 'Manual deletion',
            $confirmationData['records_deleted'],
            $confirmationData['records_anonymized'],
            $confirmationData['records_archived'],
            $confirmationData['data_subject_impact'],
            implode("\n", array_map(static fn ($table) => "- {$table}", $confirmationData['affected_tables'])),
            $confirmationData['verification_hash'],
            (new \DateTime())->format('Y-m-d H:i:s'),
        );

        file_put_contents($filepath, $report);

        return $filepath;
    }

    /**
     * Get current user ID.
     *
     * @return null|int User ID
     */
    private static function getCurrentUserId(): ?int
    {
        // Try various methods to get current user ID
        if (isset($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        if (class_exists('\\Ease\\User') && method_exists('\\Ease\\User', 'singleton')) {
            $user = \Ease\User::singleton();

            if ($user && method_exists($user, 'getId')) {
                return $user->getId();
            }
        }

        // Fallback to first admin user
        $userTable = new Orm();
        $userTable->setMyTable('user');
        $adminUser = $userTable->listingQuery()
            ->where('enabled', true)
            ->orderBy('id')
            ->fetch();

        return $adminUser['id'] ?? null;
    }

    /**
     * Format report data as text.
     *
     * @param array $reportData Report data
     *
     * @return string Formatted text
     */
    private static function formatReportAsText(array $reportData): string
    {
        $text = sprintf(
            "MultiFlexi Data Retention Report\n".
            "================================\n\n".
            "Report Type: %s\n".
            "Period: %s to %s\n".
            "Generated: %s\n".
            "Generated By: User ID %d\n\n".
            "SUMMARY\n".
            "=======\n%s\n\n",
            ucfirst(str_replace('_', ' ', $reportData['report_type'])),
            $reportData['report_period_start'],
            $reportData['report_period_end'],
            $reportData['generated_at'],
            $reportData['generated_by'],
            $reportData['summary'],
        );

        $data = json_decode($reportData['report_data'], true);

        if ($data) {
            $text .= "DETAILED DATA\n";
            $text .= "=============\n";
            $text .= print_r($data, true);
        }

        return $text;
    }

    // Placeholder methods for policy analysis (to be implemented based on specific requirements)

    private static function getLastExecutionDate(int $policyId): ?\DateTime
    {
        return null;
    }
    private static function getRecordsAffectedByPolicy(int $policyId): int
    {
        return 0;
    }
    private static function evaluatePolicyCompliance(array $policy): string
    {
        return 'compliant';
    }
    private static function calculateOverallCompliance(array $auditData): array
    {
        return ['status' => 'compliant'];
    }
    private static function analyzePolicyEffectiveness(): array
    {
        return [];
    }
    private static function generateRecommendations(array $cleanupStats, array $archiveStats): array
    {
        return [];
    }
    private static function assessRetentionEffectiveness(int $days): array
    {
        return ['score' => 100];
    }
    private static function assessAuditTrailCompleteness(int $days): array
    {
        return ['score' => 100];
    }
    private static function generateComplianceRecommendations(): array
    {
        return [];
    }
}
