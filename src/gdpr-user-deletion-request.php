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

use MultiFlexi\DataErasure\UserDataEraser;
use MultiFlexi\User;

require_once './init.php';

WebPage::singleton()->onlyForLogged();

$targetUserId = WebPage::singleton()->getRequestValue('target_user_id', 'int');
$currentUser = User::singleton();

// If no target user specified, use current user
if (!$targetUserId) {
    $targetUserId = $currentUser->getId();
}

$targetUser = new User($targetUserId);

if (!$targetUser->getId()) {
    WebPage::singleton()->addStatusMessage(_('User not found'), 'error');
    WebPage::singleton()->redirect('users.php');

    exit;
}

// Check permissions - users can delete themselves, admins can delete others
$canDelete = ($currentUser->getId() === $targetUser->getId())
             || $currentUser->getSettingValue('admin'); // Adjust this based on your role system

if (!$canDelete) {
    WebPage::singleton()->addStatusMessage(_('You do not have permission to request deletion for this user'), 'error');
    WebPage::singleton()->redirect('users.php');

    exit;
}

// Process form submission
if (WebPage::singleton()->isPosted()) {
    try {
        $deletionType = WebPage::singleton()->getRequestValue('deletion_type', 'string');
        $reason = WebPage::singleton()->getRequestValue('reason', 'string');
        $confirmUnderstanding = WebPage::singleton()->getRequestValue('confirm_understanding', 'bool');
        $confirmLegalBasis = WebPage::singleton()->getRequestValue('confirm_legal_basis', 'bool');

        // Validation
        if (!\in_array($deletionType, ['soft', 'hard', 'anonymize'], true)) {
            throw new \Exception(_('Invalid deletion type selected'));
        }

        if (!$confirmUnderstanding || !$confirmLegalBasis) {
            throw new \Exception(_('All confirmation checkboxes must be checked'));
        }

        // For self-deletion, check the self-deletion confirmation
        if ($currentUser->getId() === $targetUser->getId()) {
            $confirmSelfDeletion = WebPage::singleton()->getRequestValue('confirm_self_deletion', 'bool');

            if (!$confirmSelfDeletion) {
                throw new \Exception(_('Self-deletion confirmation is required'));
            }
        }

        // Create deletion request
        $eraser = new UserDataEraser($targetUser, $currentUser);
        $requestId = $eraser->createDeletionRequest($deletionType, $reason);

        // Process immediately for soft deletion
        if ($deletionType === 'soft') {
            $processResult = $eraser->processDeletionRequest($requestId, false);

            if ($processResult) {
                WebPage::singleton()->addStatusMessage(
                    sprintf(_('Deletion request %d has been processed successfully. The account has been disabled.'), $requestId),
                    'success',
                );

                // If user deleted themselves, log them out
                if ($currentUser->getId() === $targetUser->getId()) {
                    $currentUser->logout();
                    WebPage::singleton()->redirect('login.php');

                    exit;
                }
            } else {
                WebPage::singleton()->addStatusMessage(
                    sprintf(_('Deletion request %d was created but processing failed. Please contact an administrator.'), $requestId),
                    'warning',
                );
            }
        } else {
            WebPage::singleton()->addStatusMessage(
                sprintf(_('Deletion request %d has been submitted for administrator review. You will be notified when it is processed.'), $requestId),
                'info',
            );
        }

        WebPage::singleton()->redirect('user.php?id='.$targetUser->getId());

        exit;
    } catch (\Exception $e) {
        WebPage::singleton()->addStatusMessage($e->getMessage(), 'error');
    }
}

// Build page
WebPage::singleton()->addItem(new PageTop(_('GDPR User Deletion Request')));

$container = WebPage::singleton()->container;

// Add breadcrumb navigation
$breadcrumb = new \Ease\TWB4\Breadcrumb();
$breadcrumb->addItem(new \Ease\Html\ATag('users.php', _('Users')));
$breadcrumb->addItem(new \Ease\Html\ATag('user.php?id='.$targetUser->getId(), $targetUser->getUserName()));
$breadcrumb->addItem(_('Delete Account'));
$container->addItem($breadcrumb);

// Page title
$pageTitle = ($currentUser->getId() === $targetUser->getId()) ?
    _('Delete My Account') :
    sprintf(_('Delete User: %s'), $targetUser->getUserName());

$container->addItem(new \Ease\Html\H1Tag($pageTitle));

// Add deletion request form
$deletionForm = new UserDeletionRequestForm($targetUser);
$container->addItem($deletionForm);

// Add information about pending requests
$pendingRequests = new \Ease\SQL\Orm();
$pendingRequests->setMyTable('user_deletion_requests');
$existingRequest = $pendingRequests->listingQuery()
    ->where('user_id', $targetUser->getId())
    ->where('status', ['pending', 'approved'])
    ->orderBy('request_date DESC')
    ->fetch();

if ($existingRequest) {
    $alert = new \Ease\TWB4\Alert(
        _('Existing Deletion Request'),
        'info',
        sprintf(
            _('There is already a %s deletion request for this user (Request #%d, submitted %s). Status: %s'),
            $existingRequest['deletion_type'],
            $existingRequest['id'],
            $existingRequest['request_date'],
            $existingRequest['status'],
        ),
    );
    $container->addItem($alert);
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
