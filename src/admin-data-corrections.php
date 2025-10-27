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

namespace MultiFlexi\Ui;

require_once './init.php';

// Ensure user is logged in and is an admin
WebPage::singleton()->onlyForLogged();

$currentUser = new \MultiFlexi\User();
$currentUser->loadFromSQL(\Ease\Shared::user()->getUserID());

// Check if user is admin (you may need to adjust this based on your role system)
if (!($currentUser->getSettingValue('admin') === true || $currentUser->getDataValue('role') === 'admin')) {
    WebPage::singleton()->addStatusMessage(_('Access denied: Administrator privileges required'), 'error');
    WebPage::singleton()->redirect('index.php');

    exit;
}

$correctionRequest = new \MultiFlexi\GDPR\UserDataCorrectionRequest();

// Handle form submissions (approve/reject requests)
if (WebPage::singleton()->isPosted()) {
    $action = WebPage::singleton()->getRequestValue('action');
    $requestId = WebPage::singleton()->getRequestValue('request_id', 'int');
    $reviewerNotes = WebPage::singleton()->getRequestValue('reviewer_notes', 'string');

    switch ($action) {
        case 'approve':
            if ($correctionRequest->approveRequest($requestId, $currentUser->getId(), $reviewerNotes)) {
                WebPage::singleton()->addStatusMessage(_('Request approved successfully'), 'success');
            }

            break;
        case 'reject':
            if ($correctionRequest->rejectRequest($requestId, $currentUser->getId(), $reviewerNotes)) {
                WebPage::singleton()->addStatusMessage(_('Request rejected'), 'info');
            }

            break;
    }
}

WebPage::singleton()->addItem(new PageTop(_('Data Correction Requests Administration')));

$container = WebPage::singleton()->container;

// Statistics overview
$auditLogger = new \MultiFlexi\Audit\UserDataAuditLogger();
$stats = $auditLogger->getAuditStatistics(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));

$statsCard = new \Ease\TWB4\Card(_('Last 30 Days Statistics'));
$statsRow = new \Ease\TWB4\Row();

$statsRow->addColumn(3, [
    new \Ease\Html\H4Tag($stats['total_changes'], ['class' => 'text-primary']),
    new \Ease\Html\SmallTag(_('Total Changes')),
]);

$statsRow->addColumn(3, [
    new \Ease\Html\H4Tag($stats['unique_users_affected'], ['class' => 'text-info']),
    new \Ease\Html\SmallTag(_('Users Affected')),
]);

$statsRow->addColumn(3, [
    new \Ease\Html\H4Tag($stats['by_type']['pending_approval'] ?? 0, ['class' => 'text-warning']),
    new \Ease\Html\SmallTag(_('Pending Approval')),
]);

$statsRow->addColumn(3, [
    new \Ease\Html\H4Tag($stats['by_type']['approved'] ?? 0, ['class' => 'text-success']),
    new \Ease\Html\SmallTag(_('Approved')),
]);

$statsCard->addItem($statsRow);
$container->addItem($statsCard);

// Pending requests table
$pendingRequests = $correctionRequest->getPendingRequests(20);

if (!empty($pendingRequests)) {
    $pendingCard = new \Ease\TWB4\Card(_('Pending Data Correction Requests'));

    foreach ($pendingRequests as $request) {
        $requestPanel = new \Ease\TWB4\Card(
            sprintf(_('Request #%d - %s'), $request['id'], $request['login']),
            'light',
        );

        // Request details
        $detailsTable = new \Ease\Html\TableTag(null, ['class' => 'table table-sm table-borderless']);
        $detailsTable->addRowColumns([_('User'), $request['firstname'].' '.$request['lastname'].' ('.$request['login'].')']);
        $detailsTable->addRowColumns([_('Email'), $request['email']]);
        $detailsTable->addRowColumns([_('Field to Change'), \MultiFlexi\GDPR\UserDataCorrectionRequest::getFieldDisplayName($request['field_name'])]);
        $detailsTable->addRowColumns([_('Current Value'), new \Ease\Html\CodeTag($request['current_value'])]);
        $detailsTable->addRowColumns([_('Requested Value'), new \Ease\Html\CodeTag($request['requested_value'])]);
        $detailsTable->addRowColumns([_('Justification'), $request['justification'] ?: _('(no justification provided)')]);
        $detailsTable->addRowColumns([_('Requested On'), date('F j, Y g:i A', strtotime($request['created_at']))]);
        $detailsTable->addRowColumns([_('IP Address'), $request['requested_by_ip']]);

        $requestPanel->addItem($detailsTable);

        // Review form
        $reviewForm = new \MultiFlexi\Ui\SecureForm(['method' => 'POST']);
        $reviewForm->addItem(new \Ease\Html\InputHiddenTag('request_id', $request['id']));

        $reviewForm->addItem(new \Ease\TWB4\FormGroup([
            new \Ease\Html\LabelTag('reviewer_notes', _('Admin Notes')),
            new \Ease\Html\TextareaTag('reviewer_notes', '', [
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => _('Enter your notes about this decision...'),
            ]),
        ]));

        $buttonGroup = new \Ease\TWB4\ButtonGroup();
        $buttonGroup->addItem(new \Ease\TWB4\SubmitButton(_('Approve'), 'success', [
            'name' => 'action',
            'value' => 'approve',
            'onclick' => 'return confirm("'._('Are you sure you want to approve this request?').'")',
        ]));
        $buttonGroup->addItem(new \Ease\TWB4\SubmitButton(_('Reject'), 'danger', [
            'name' => 'action',
            'value' => 'reject',
            'onclick' => 'return confirm("'._('Are you sure you want to reject this request?').'")',
        ]));

        $reviewForm->addItem($buttonGroup);
        $requestPanel->addItem($reviewForm);

        $pendingCard->addItem($requestPanel);
        $pendingCard->addItem(new \Ease\Html\HrTag());
    }

    $container->addItem($pendingCard);
} else {
    $container->addItem(new \Ease\TWB4\Alert(
        _('No pending data correction requests.'),
        'info',
    ));
}

// Recent activity (processed requests)
$recentActivity = $correctionRequest->listingQuery()
    ->select([
        'r.*',
        'u.login',
        'u.firstname',
        'u.lastname',
        'reviewer.login as reviewer_login',
    ])
    ->from($correctionRequest->myTable.' r')
    ->leftJoin('user u ON r.user_id = u.id')
    ->leftJoin('user reviewer ON r.reviewed_by_user_id = reviewer.id')
    ->where('r.status IN %in', ['approved', 'rejected'])
    ->orderBy('r.reviewed_at DESC')
    ->limit('10')
    ->fetchAll();

if (!empty($recentActivity)) {
    $activityCard = new \Ease\TWB4\Card(_('Recent Activity'));

    $activityTable = new \Ease\Html\TableTag(null, ['class' => 'table table-striped']);
    $activityTable->addRowHeaderColumns([
        _('User'),
        _('Field'),
        _('Change'),
        _('Status'),
        _('Reviewed By'),
        _('Date'),
        _('Notes'),
    ]);

    foreach ($recentActivity as $activity) {
        $statusBadge = $activity['status'] === 'approved' ?
            new \Ease\TWB4\Badge(_('Approved'), 'success') :
            new \Ease\TWB4\Badge(_('Rejected'), 'danger');

        $fieldDisplayName = \MultiFlexi\GDPR\UserDataCorrectionRequest::getFieldDisplayName($activity['field_name']);

        $activityTable->addRowColumns([
            $activity['login'],
            $fieldDisplayName,
            \Ease\Functions::truncateString($activity['current_value'], 20).' → '.
            \Ease\Functions::truncateString($activity['requested_value'], 20),
            $statusBadge,
            $activity['reviewer_login'],
            date('M j, Y', strtotime($activity['reviewed_at'])),
            \Ease\Functions::truncateString($activity['reviewer_notes'], 30),
        ]);
    }

    $activityCard->addItem($activityTable);
    $container->addItem($activityCard);
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
