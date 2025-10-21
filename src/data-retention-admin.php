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

namespace MultiFlexi;

require_once './init.php';

use Ease\Html\InputCheckboxTag;
use Ease\Html\InputNumberTag;
use Ease\Html\InputTextTag;
use Ease\Html\SelectTag;
use Ease\Html\TextareaTag;
use Ease\TWB4\Alert;
use Ease\TWB4\Badge;
use Ease\TWB4\Button;
use Ease\TWB4\Card;
use Ease\TWB4\Form;
use Ease\TWB4\Modal;
use Ease\TWB4\Row;
use Ease\TWB4\Table;
use MultiFlexi\DataRetention\DataArchiver;
use MultiFlexi\DataRetention\RetentionPolicyManager;
use MultiFlexi\DataRetention\RetentionReporter;
use MultiFlexi\DataRetention\RetentionService;
use MultiFlexi\Ui\WebPage;

$user = User::singleton();

if (!$user->getDataValue('id')) {
    $user->addStatusMessage(_('Please sign in'), 'warning');
    WebPage::singleton()->redirect('login.php');
    exit;
}

// Check if user has admin privileges (you may need to adjust this based on your role system)
if (!$user->permitRead('admin')) {
    $user->addStatusMessage(_('Insufficient privileges'), 'error');
    \Ease\Shared::user(null)->redirect('index.php');

    exit;
}

$policyManager = new RetentionPolicyManager($user);
$retentionService = new RetentionService();
$reporter = new RetentionReporter($user);
$archiver = new DataArchiver($user);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create_policy':
            try {
                $policyData = [
                    'policy_name' => $_POST['policy_name'] ?? '',
                    'data_type' => $_POST['data_type'] ?? '',
                    'table_name' => $_POST['table_name'] ?? '',
                    'retention_period_days' => (int) ($_POST['retention_period_days'] ?? 0),
                    'grace_period_days' => (int) ($_POST['grace_period_days'] ?? 30),
                    'deletion_action' => $_POST['deletion_action'] ?? '',
                    'legal_basis' => $_POST['legal_basis'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'enabled' => isset($_POST['enabled']),
                ];

                $policyId = $policyManager->createPolicy($policyData);
                $user->addStatusMessage(
                    sprintf(_('Policy "%s" created successfully'), $policyData['policy_name']),
                    'success',
                );
            } catch (Exception $e) {
                $user->addStatusMessage($e->getMessage(), 'error');
            }

            break;
        case 'toggle_policy':
            try {
                $policyId = (int) ($_POST['policy_id'] ?? 0);
                $enabled = isset($_POST['enabled']);
                $policyManager->togglePolicy($policyId, $enabled);
            } catch (Exception $e) {
                $user->addStatusMessage($e->getMessage(), 'error');
            }

            break;
        case 'delete_policy':
            try {
                $policyId = (int) ($_POST['policy_id'] ?? 0);
                $policyManager->deletePolicy($policyId);
            } catch (Exception $e) {
                $user->addStatusMessage($e->getMessage(), 'error');
            }

            break;
        case 'run_cleanup':
            try {
                $dryRun = isset($_POST['dry_run']);
                $summary = $retentionService->processScheduledCleanup($dryRun);

                $user->addStatusMessage(
                    sprintf(
                        _('Cleanup completed: %d jobs processed, %d records deleted, %d anonymized, %d archived'),
                        $summary['jobs_processed'],
                        $summary['records_deleted'],
                        $summary['records_anonymized'],
                        $summary['records_archived'],
                    ),
                    empty($summary['errors']) ? 'success' : 'warning',
                );

                if (!empty($summary['errors'])) {
                    foreach ($summary['errors'] as $error) {
                        $user->addStatusMessage($error, 'error');
                    }
                }
            } catch (Exception $e) {
                $user->addStatusMessage($e->getMessage(), 'error');
            }

            break;
        case 'calculate_retention':
            try {
                $summary = $retentionService->calculateRetentionDates();
                $user->addStatusMessage(
                    sprintf(_('Updated retention dates for %d records'), $summary['updated_records']),
                    'success',
                );
            } catch (Exception $e) {
                $user->addStatusMessage($e->getMessage(), 'error');
            }

            break;
    }
}

$oPage = new WebPage(
    _('Data Retention Administration'),
    'retention-admin',
    'Admin/RetentionPolicies',
);

$container = $oPage->container;

// Page title
$container->addItem(new \Ease\Html\H1Tag(_('GDPR Data Retention Administration')));

// Statistics cards
$policies = $policyManager->getPolicies();
$enabledPolicies = $policyManager->getPolicies(true);
$expiredRecords = $retentionService->findExpiredRecords();
$totalExpired = array_sum(array_map('count', $expiredRecords));
$cleanupStats = $retentionService->getCleanupStatistics(7);

$statsRow = new Row();
$container->addItem($statsRow);

// Active Policies Card
$statsRow->addColumn(3, new Card(
    new \Ease\Html\H5Tag(_('Active Policies')),
    new \Ease\Html\H2Tag(
        \count($enabledPolicies).' / '.\count($policies),
        ['class' => 'text-primary'],
    ),
));

// Expired Records Card
$statsRow->addColumn(3, new Card(
    new \Ease\Html\H5Tag(_('Records Awaiting Cleanup')),
    new \Ease\Html\H2Tag(
        number_format($totalExpired),
        ['class' => $totalExpired > 0 ? 'text-warning' : 'text-success'],
    ),
));

// Recent Jobs Card
$statsRow->addColumn(3, new Card(
    new \Ease\Html\H5Tag(_('Cleanup Jobs (7 days)')),
    new \Ease\Html\H2Tag(
        sprintf('%d / %d', $cleanupStats['completed_jobs'], $cleanupStats['total_jobs']),
        ['class' => $cleanupStats['failed_jobs'] > 0 ? 'text-warning' : 'text-success'],
    ),
));

// Records Processed Card
$statsRow->addColumn(3, new Card(
    new \Ease\Html\H5Tag(_('Records Processed (7 days)')),
    new \Ease\Html\H2Tag(
        number_format($cleanupStats['total_records_processed']),
        ['class' => 'text-info'],
    ),
));

// Action buttons
$actionsRow = new Row();
$container->addItem($actionsRow);

$actionsCard = new Card(_('Quick Actions'));
$actionsRow->addColumn(12, $actionsCard);

$actionButtonsRow = new Row();
$actionsCard->addItem($actionButtonsRow);

// Calculate retention dates button
$calculateForm = new Form('post');
$calculateForm->addItem(new \Ease\Html\InputHiddenTag('action', 'calculate_retention'));
$calculateForm->addItem(new Button(
    _('Calculate Retention Dates'),
    'primary',
    ['type' => 'submit'],
));
$actionButtonsRow->addColumn(3, $calculateForm);

// Run cleanup button (with modal for dry run option)
$cleanupForm = new Form('post');
$cleanupForm->addItem(new \Ease\Html\InputHiddenTag('action', 'run_cleanup'));
$cleanupForm->addItem(new InputCheckboxTag('dry_run', true, false, ['id' => 'dry_run']));
$cleanupForm->addItem(new \Ease\Html\LabelTag('dry_run', _('Dry Run (simulate only)')));
$cleanupForm->addItem(new Button(
    _('Run Cleanup'),
    'warning',
    ['type' => 'submit'],
));
$actionButtonsRow->addColumn(6, $cleanupForm);

// Generate report button
$reportButton = new Button(_('Generate Report'), 'info');
$reportButton->addTagClass('btn-sm');
$actionButtonsRow->addColumn(3, $reportButton);

// Policies management
$policiesRow = new Row();
$container->addItem($policiesRow);

$policiesCard = new Card(_('Retention Policies Management'));
$policiesRow->addColumn(12, $policiesCard);

// Add policy button
$addPolicyBtn = new Button(_('Add New Policy'), 'success', [
    'data-toggle' => 'modal',
    'data-target' => '#addPolicyModal',
]);
$policiesCard->addItem($addPolicyBtn);

// Policies table
$table = new Table();
$table->addRowHeaderColumns([
    _('Policy Name'),
    _('Data Type'),
    _('Table'),
    _('Retention Period'),
    _('Action'),
    _('Status'),
    _('Actions'),
]);

foreach ($policies as $policy) {
    $statusBadge = $policy['enabled']
        ? new Badge('success', _('Enabled'))
        : new Badge('secondary', _('Disabled'));

    $toggleForm = new Form('post', '', ['style' => 'display: inline;']);
    $toggleForm->addItem(new \Ease\Html\InputHiddenTag('action', 'toggle_policy'));
    $toggleForm->addItem(new \Ease\Html\InputHiddenTag('policy_id', $policy['id']));

    if (!$policy['enabled']) {
        $toggleForm->addItem(new \Ease\Html\InputHiddenTag('enabled', '1'));
        $toggleBtn = new Button(_('Enable'), 'outline-success btn-sm', ['type' => 'submit']);
    } else {
        $toggleBtn = new Button(_('Disable'), 'outline-warning btn-sm', ['type' => 'submit']);
    }

    $toggleForm->addItem($toggleBtn);

    $deleteForm = new Form('post', '', ['style' => 'display: inline;']);
    $deleteForm->addItem(new \Ease\Html\InputHiddenTag('action', 'delete_policy'));
    $deleteForm->addItem(new \Ease\Html\InputHiddenTag('policy_id', $policy['id']));
    $deleteBtn = new Button(_('Delete'), 'outline-danger btn-sm', [
        'type' => 'submit',
        'onclick' => 'return confirm("'._('Are you sure you want to delete this policy?').'")',
    ]);
    $deleteForm->addItem($deleteBtn);

    $actions = new \Ease\Html\DivTag([$toggleForm, ' ', $deleteForm]);

    $table->addRowColumns([
        $policy['policy_name'],
        $policy['data_type'],
        $policy['table_name'],
        $policy['retention_period_days'].' '._('days'),
        ucfirst(str_replace('_', ' ', $policy['deletion_action'])),
        $statusBadge,
        $actions,
    ]);
}

$policiesCard->addItem($table);

// Add Policy Modal
$addPolicyModal = new Modal('addPolicyModal', _('Add New Retention Policy'));
$container->addItem($addPolicyModal);

$addPolicyForm = new Form('post');
$addPolicyModal->addItem($addPolicyForm);

$addPolicyForm->addItem(new \Ease\Html\InputHiddenTag('action', 'create_policy'));

// Policy name
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('policy_name', _('Policy Name')),
    new InputTextTag('policy_name', '', ['class' => 'form-control', 'required' => true]),
], ['class' => 'form-group']));

// Data type
$dataTypeOptions = [];

foreach ($policyManager->getSupportedDataTypes() as $key => $value) {
    $dataTypeOptions[$key] = $value;
}

$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('data_type', _('Data Type')),
    new SelectTag('data_type', $dataTypeOptions, null, ['class' => 'form-control', 'required' => true]),
], ['class' => 'form-group']));

// Table name
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('table_name', _('Table Name')),
    new InputTextTag('table_name', '', ['class' => 'form-control', 'required' => true]),
], ['class' => 'form-group']));

// Retention period
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('retention_period_days', _('Retention Period (days)')),
    new InputNumberTag('retention_period_days', '', ['class' => 'form-control', 'required' => true, 'min' => 0]),
], ['class' => 'form-group']));

// Grace period
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('grace_period_days', _('Grace Period (days)')),
    new InputNumberTag('grace_period_days', 30, ['class' => 'form-control', 'min' => 0]),
], ['class' => 'form-group']));

// Deletion action
$actionOptions = [];

foreach ($policyManager->getValidDeletionActions() as $action) {
    $actionOptions[$action] = ucfirst(str_replace('_', ' ', $action));
}

$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('deletion_action', _('Deletion Action')),
    new SelectTag('deletion_action', $actionOptions, null, ['class' => 'form-control', 'required' => true]),
], ['class' => 'form-group']));

// Legal basis
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('legal_basis', _('Legal Basis')),
    new InputTextTag('legal_basis', '', ['class' => 'form-control']),
], ['class' => 'form-group']));

// Description
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\LabelTag('description', _('Description')),
    new TextareaTag('description', '', ['class' => 'form-control', 'rows' => 3]),
], ['class' => 'form-group']));

// Enabled checkbox
$addPolicyForm->addItem(new \Ease\Html\DivTag([
    new InputCheckboxTag('enabled', true, true, ['id' => 'enabled']),
    new \Ease\Html\LabelTag('enabled', _('Enabled')),
], ['class' => 'form-check']));

$addPolicyModal->addFooterButton(new Button(_('Cancel'), 'secondary', [
    'data-dismiss' => 'modal',
]));
$addPolicyModal->addFooterButton(new Button(_('Create Policy'), 'primary', [
    'type' => 'submit',
    'form' => $addPolicyForm->getTagID(),
]));

// Show expired records if any
if ($totalExpired > 0) {
    $expiredRow = new Row();
    $container->addItem($expiredRow);

    $expiredCard = new Card(_('Records Awaiting Cleanup'));
    $expiredRow->addColumn(12, $expiredCard);

    $expiredAlert = new Alert(
        sprintf(_('%d records are past their retention period and ready for cleanup.'), $totalExpired),
        'warning',
    );
    $expiredCard->addItem($expiredAlert);

    $expiredTable = new Table();
    $expiredTable->addRowHeaderColumns([_('Table'), _('Records'), _('Policy')]);

    foreach ($expiredRecords as $tableName => $records) {
        $policy = $policyManager->getPoliciesByTable($tableName)[0] ?? null;
        $policyName = $policy ? $policy['policy_name'] : _('No policy');

        $expiredTable->addRowColumns([
            $tableName,
            number_format(\count($records)),
            $policyName,
        ]);
    }

    $expiredCard->addItem($expiredTable);
}

// Add some custom CSS
$oPage->includeCSS(<<<'EOD'

.card {
    margin-bottom: 1rem;
}

.stats-card {
    text-align: center;
}

.btn-group-sm > .btn {
    margin: 0 2px;
}

.table th {
    background-color: #f8f9fa;
}

EOD);

// Add JavaScript for form handling
$oPage->includeJavaScript(<<<'EOD'

$(document).ready(function() {
    // Auto-refresh page every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);

    // Confirm dangerous actions
    $("form").submit(function(e) {
        var action = $(this).find("input[name=action]").val();
        if (action === "run_cleanup" && !$(this).find("input[name=dry_run]").is(":checked")) {
            if (!confirm("
EOD._('Are you sure you want to run the cleanup? This will permanently delete or anonymize data.').<<<'EOD'
")) {
                e.preventDefault();
                return false;
            }
        }
    });
});

EOD);

$oPage->draw();
