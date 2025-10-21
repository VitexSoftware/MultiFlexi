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

use MultiFlexi\DataErasure\DeletionAuditLogger;
use MultiFlexi\DataErasure\UserDataEraser;
use MultiFlexi\User;

require_once './init.php';

WebPage::singleton()->onlyForLogged();

$currentUser = User::singleton();

// Check if user is admin (adjust this based on your role system)
if (!$currentUser->getSettingValue('admin')) {
    WebPage::singleton()->addStatusMessage(_('Access denied. Administrator privileges required.'), 'error');
    WebPage::singleton()->redirect('main.php');

    exit;
}

// Handle request actions
if (WebPage::singleton()->isPosted()) {
    $action = WebPage::singleton()->getRequestValue('action', 'string');
    $requestId = WebPage::singleton()->getRequestValue('request_id', 'int');
    $notes = WebPage::singleton()->getRequestValue('notes', 'string');

    if (!$requestId) {
        WebPage::singleton()->addStatusMessage(_('Request ID is required'), 'error');
    } else {
        try {
            // Load request
            $request = new \Ease\SQL\Orm();
            $request->setMyTable('user_deletion_requests');
            $request->loadFromSQL($requestId);

            if (!$request->getId()) {
                throw new \Exception(_('Deletion request not found'));
            }

            $targetUser = new User($request->getDataValue('user_id'));
            $eraser = new UserDataEraser($targetUser, $currentUser);

            switch ($action) {
                case 'approve':
                    if ($eraser->approveDeletionRequest($requestId, $currentUser, $notes)) {
                        WebPage::singleton()->addStatusMessage(
                            sprintf(_('Deletion request #%d approved successfully'), $requestId),
                            'success',
                        );
                    } else {
                        WebPage::singleton()->addStatusMessage(_('Failed to approve deletion request'), 'error');
                    }

                    break;
                case 'reject':
                    if ($eraser->rejectDeletionRequest($requestId, $currentUser, $notes)) {
                        WebPage::singleton()->addStatusMessage(
                            sprintf(_('Deletion request #%d rejected successfully'), $requestId),
                            'success',
                        );
                    } else {
                        WebPage::singleton()->addStatusMessage(_('Failed to reject deletion request'), 'error');
                    }

                    break;
                case 'process':
                    if ($eraser->processDeletionRequest($requestId, false)) {
                        WebPage::singleton()->addStatusMessage(
                            sprintf(_('Deletion request #%d processed successfully'), $requestId),
                            'success',
                        );
                    } else {
                        WebPage::singleton()->addStatusMessage(_('Failed to process deletion request'), 'error');
                    }

                    break;

                default:
                    WebPage::singleton()->addStatusMessage(_('Unknown action'), 'error');
            }
        } catch (\Exception $e) {
            WebPage::singleton()->addStatusMessage($e->getMessage(), 'error');
        }
    }
}

WebPage::singleton()->addItem(new PageTop(_('GDPR Deletion Request Management')));

$container = WebPage::singleton()->container;

// Page title and description
$container->addItem(new \Ease\Html\H1Tag(_('User Deletion Request Management')));
$container->addItem(new \Ease\Html\PTag(_('Review and manage GDPR Article 17 user deletion requests.')));

// Statistics card
$container->addItem(createStatisticsCard());

// Filter options
$statusFilter = WebPage::singleton()->getRequestValue('status_filter', 'string') ?: 'pending';
$container->addItem(createFilterForm($statusFilter));

// Requests table
$container->addItem(createRequestsTable($statusFilter));

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();

/**
 * Create statistics card.
 */
function createStatisticsCard(): \Ease\TWB4\Card
{
    $requests = new \Ease\SQL\Orm();
    $requests->setMyTable('user_deletion_requests');

    $stats = [
        'pending' => $requests->listingQuery()->where('status', 'pending')->count(),
        'approved' => $requests->listingQuery()->where('status', 'approved')->count(),
        'completed' => $requests->listingQuery()->where('status', 'completed')->count(),
        'rejected' => $requests->listingQuery()->where('status', 'rejected')->count(),
    ];

    $card = new \Ease\TWB4\Card(_('Request Statistics'));

    $row = new \Ease\TWB4\Row();

    foreach ($stats as $status => $count) {
        $badgeClass = match ($status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };

        $col = new \Ease\TWB4\Col(3);
        $col->addItem(new \Ease\Html\H4Tag($count));
        $col->addItem(new \Ease\TWB4\Badge($badgeClass, ucfirst($status)));
        $row->addItem($col);
    }

    $card->addItem($row);

    return $card;
}

/**
 * Create filter form.
 */
function createFilterForm(string $currentFilter): \Ease\TWB4\Form
{
    $form = new \Ease\TWB4\Form(['method' => 'GET', 'class' => 'form-inline mb-3']);

    $form->addItem(new \Ease\Html\LabelTag(_('Filter by Status: '), ['class' => 'mr-2']));

    $select = new \Ease\Html\SelectTag('status_filter', [
        'all' => _('All'),
        'pending' => _('Pending'),
        'approved' => _('Approved'),
        'completed' => _('Completed'),
        'rejected' => _('Rejected'),
    ], $currentFilter, ['class' => 'form-control mr-2']);

    $form->addItem($select);
    $form->addItem(new \Ease\TWB4\SubmitButton(_('Filter'), 'primary', ['class' => 'btn-sm']));

    return $form;
}

/**
 * Create requests table.
 */
function createRequestsTable(string $statusFilter): \Ease\TWB4\Card
{
    $requests = new \Ease\SQL\Orm();
    $requests->setMyTable('user_deletion_requests');

    $query = $requests->listingQuery()
        ->select([
            'udr.*',
            'u.login as target_user_login',
            'u.firstname as target_user_firstname',
            'u.lastname as target_user_lastname',
            'u.email as target_user_email',
            'ru.login as requested_by_login',
            'rev.login as reviewed_by_login',
        ])
        ->join('user u', 'u.id = udr.user_id')
        ->join('user ru', 'ru.id = udr.requested_by_user_id')
        ->leftJoin('user rev', 'rev.id = udr.reviewed_by_user_id')
        ->orderBy('udr.request_date DESC');

    if ($statusFilter !== 'all') {
        $query->where('udr.status', $statusFilter);
    }

    $requestList = $query->fetchAll();

    $card = new \Ease\TWB4\Card(_('Deletion Requests'));

    if (empty($requestList)) {
        $card->addItem(new \Ease\TWB4\Alert(_('No requests found'), 'info'));

        return $card;
    }

    $table = new \Ease\TWB4\Table(null, ['class' => 'table-striped table-hover']);

    // Table header
    $header = new \Ease\Html\TheadTag();
    $headerRow = new \Ease\Html\TrTag();
    $headerRow->addItem(new \Ease\Html\ThTag(_('ID')));
    $headerRow->addItem(new \Ease\Html\ThTag(_('User')));
    $headerRow->addItem(new \Ease\Html\ThTag(_('Type')));
    $headerRow->addItem(new \Ease\Html\ThTag(_('Status')));
    $headerRow->addItem(new \Ease\Html\ThTag(_('Requested')));
    $headerRow->addItem(new \Ease\Html\ThTag(_('Requested By')));
    $headerRow->addItem(new \Ease\Html\ThTag(_('Actions')));
    $header->addItem($headerRow);
    $table->addItem($header);

    // Table body
    $tbody = new \Ease\Html\TbodyTag();

    foreach ($requestList as $request) {
        $row = new \Ease\Html\TrTag();

        // ID
        $row->addItem(new \Ease\Html\TdTag($request['id']));

        // User info
        $userName = trim($request['target_user_firstname'].' '.$request['target_user_lastname']);

        if (empty($userName)) {
            $userName = $request['target_user_login'];
        }

        $userInfo = sprintf('%s (%s)', $userName, $request['target_user_login']);
        $row->addItem(new \Ease\Html\TdTag($userInfo));

        // Type with color coding
        $typeClass = match ($request['deletion_type']) {
            'soft' => 'success',
            'hard' => 'danger',
            'anonymize' => 'warning',
            default => 'secondary',
        };
        $typeBadge = new \Ease\TWB4\Badge($typeClass, ucfirst($request['deletion_type']));
        $row->addItem(new \Ease\Html\TdTag($typeBadge));

        // Status
        $statusClass = match ($request['status']) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
        $statusBadge = new \Ease\TWB4\Badge($statusClass, ucfirst($request['status']));
        $row->addItem(new \Ease\Html\TdTag($statusBadge));

        // Request date
        $row->addItem(new \Ease\Html\TdTag(date('Y-m-d H:i', strtotime($request['request_date']))));

        // Requested by
        $row->addItem(new \Ease\Html\TdTag($request['requested_by_login']));

        // Actions
        $actions = new \Ease\TWB4\ButtonGroup(['size' => 'sm']);

        // View details button
        $viewBtn = new \Ease\TWB4\LinkButton(
            '#',
            new \Ease\TWB4\Widgets\FaIcon('eye'),
            'info',
            [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#requestModal'.$request['id'],
                'title' => _('View Details'),
            ],
        );
        $actions->addItem($viewBtn);

        // Approval/rejection buttons for pending requests
        if ($request['status'] === 'pending') {
            $approveBtn = new \Ease\Html\ButtonTag(
                new \Ease\TWB4\Widgets\FaIcon('check'),
                [
                    'type' => 'button',
                    'class' => 'btn btn-success btn-sm',
                    'onclick' => "approveRequest({$request['id']})",
                    'title' => _('Approve'),
                ],
            );
            $actions->addItem($approveBtn);

            $rejectBtn = new \Ease\Html\ButtonTag(
                new \Ease\TWB4\Widgets\FaIcon('times'),
                [
                    'type' => 'button',
                    'class' => 'btn btn-danger btn-sm',
                    'onclick' => "rejectRequest({$request['id']})",
                    'title' => _('Reject'),
                ],
            );
            $actions->addItem($rejectBtn);
        }

        // Process button for approved requests
        if ($request['status'] === 'approved') {
            $processBtn = new \Ease\Html\ButtonTag(
                new \Ease\TWB4\Widgets\FaIcon('cog'),
                [
                    'type' => 'button',
                    'class' => 'btn btn-warning btn-sm',
                    'onclick' => "processRequest({$request['id']})",
                    'title' => _('Process'),
                ],
            );
            $actions->addItem($processBtn);
        }

        $row->addItem(new \Ease\Html\TdTag($actions));
        $tbody->addItem($row);

        // Add modal for request details
        $card->addItem(createRequestModal($request));
    }

    $table->addItem($tbody);
    $card->addItem($table);

    // Add JavaScript for actions
    $card->addItem(createActionJavaScript());

    return $card;
}

/**
 * Create request details modal.
 */
function createRequestModal(array $request): \Ease\TWB4\Modal
{
    $modal = new \Ease\TWB4\Modal('requestModal'.$request['id'], _('Deletion Request Details'));

    $modal->addItem(new \Ease\Html\H5Tag(sprintf(_('Request #%d'), $request['id'])));

    $details = new \Ease\Html\DlTag(['class' => 'row']);

    $details->addItem(new \Ease\Html\DtTag(_('Target User:'), ['class' => 'col-sm-3']));
    $details->addItem(new \Ease\Html\DdTag($request['target_user_login'], ['class' => 'col-sm-9']));

    $details->addItem(new \Ease\Html\DtTag(_('Deletion Type:'), ['class' => 'col-sm-3']));
    $details->addItem(new \Ease\Html\DdTag(ucfirst($request['deletion_type']), ['class' => 'col-sm-9']));

    $details->addItem(new \Ease\Html\DtTag(_('Status:'), ['class' => 'col-sm-3']));
    $details->addItem(new \Ease\Html\DdTag(ucfirst($request['status']), ['class' => 'col-sm-9']));

    $details->addItem(new \Ease\Html\DtTag(_('Requested:'), ['class' => 'col-sm-3']));
    $details->addItem(new \Ease\Html\DdTag($request['request_date'], ['class' => 'col-sm-9']));

    $details->addItem(new \Ease\Html\DtTag(_('Requested By:'), ['class' => 'col-sm-3']));
    $details->addItem(new \Ease\Html\DdTag($request['requested_by_login'], ['class' => 'col-sm-9']));

    if ($request['reason']) {
        $details->addItem(new \Ease\Html\DtTag(_('Reason:'), ['class' => 'col-sm-3']));
        $details->addItem(new \Ease\Html\DdTag($request['reason'], ['class' => 'col-sm-9']));
    }

    if ($request['reviewed_by_login']) {
        $details->addItem(new \Ease\Html\DtTag(_('Reviewed By:'), ['class' => 'col-sm-3']));
        $details->addItem(new \Ease\Html\DdTag($request['reviewed_by_login'], ['class' => 'col-sm-9']));

        $details->addItem(new \Ease\Html\DtTag(_('Review Date:'), ['class' => 'col-sm-3']));
        $details->addItem(new \Ease\Html\DdTag($request['review_date'], ['class' => 'col-sm-9']));

        if ($request['review_notes']) {
            $details->addItem(new \Ease\Html\DtTag(_('Review Notes:'), ['class' => 'col-sm-3']));
            $details->addItem(new \Ease\Html\DdTag($request['review_notes'], ['class' => 'col-sm-9']));
        }
    }

    $modal->addItem($details);

    // Add audit trail if completed
    if ($request['status'] === 'completed') {
        $auditLogger = new DeletionAuditLogger();
        $auditTrail = $auditLogger->getAuditTrail($request['id']);

        if (!empty($auditTrail)) {
            $modal->addItem(new \Ease\Html\HrTag());
            $modal->addItem(new \Ease\Html\H6Tag(_('Audit Trail')));

            $auditTable = new \Ease\TWB4\Table(null, ['class' => 'table-sm']);
            $auditHeader = new \Ease\Html\TheadTag();
            $auditHeaderRow = new \Ease\Html\TrTag();
            $auditHeaderRow->addItem(new \Ease\Html\ThTag(_('Date')));
            $auditHeaderRow->addItem(new \Ease\Html\ThTag(_('Action')));
            $auditHeaderRow->addItem(new \Ease\Html\ThTag(_('Table')));
            $auditHeaderRow->addItem(new \Ease\Html\ThTag(_('Reason')));
            $auditHeader->addItem($auditHeaderRow);
            $auditTable->addItem($auditHeader);

            $auditBody = new \Ease\Html\TbodyTag();

            foreach ($auditTrail as $entry) {
                $auditRow = new \Ease\Html\TrTag();
                $auditRow->addItem(new \Ease\Html\TdTag(date('H:i:s', strtotime($entry['performed_date']))));
                $auditRow->addItem(new \Ease\Html\TdTag($entry['action']));
                $auditRow->addItem(new \Ease\Html\TdTag($entry['table_name']));
                $auditRow->addItem(new \Ease\Html\TdTag($entry['reason']));
                $auditBody->addItem($auditRow);
            }

            $auditTable->addItem($auditBody);
            $modal->addItem($auditTable);
        }
    }

    return $modal;
}

/**
 * Create JavaScript for request actions.
 */
function createActionJavaScript(): \Ease\Html\ScriptTag
{
    $js = <<<'JS'
function approveRequest(requestId) {
    const notes = prompt('Enter approval notes (optional):');
    if (notes !== null) {
        submitAction('approve', requestId, notes);
    }
}

function rejectRequest(requestId) {
    const notes = prompt('Enter rejection reason:');
    if (notes !== null && notes.trim() !== '') {
        if (confirm('Are you sure you want to reject this deletion request?')) {
            submitAction('reject', requestId, notes);
        }
    } else if (notes !== null) {
        alert('Rejection reason is required.');
    }
}

function processRequest(requestId) {
    if (confirm('Are you sure you want to process this deletion request? This action cannot be undone.')) {
        submitAction('process', requestId, '');
    }
}

function submitAction(action, requestId, notes) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);

    const requestIdInput = document.createElement('input');
    requestIdInput.type = 'hidden';
    requestIdInput.name = 'request_id';
    requestIdInput.value = requestId;
    form.appendChild(requestIdInput);

    const notesInput = document.createElement('input');
    notesInput.type = 'hidden';
    notesInput.name = 'notes';
    notesInput.value = notes;
    form.appendChild(notesInput);

    document.body.appendChild(form);
    form.submit();
}
JS;

    return new \Ease\Html\ScriptTag($js);
}
