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

// Ensure user is logged in
WebPage::singleton()->onlyForLogged();

$currentUser = new \MultiFlexi\User();
$currentUser->loadFromSQL(\Ease\Shared::user()->getUserID());

// Handle profile update form submission
if (WebPage::singleton()->isPosted()) {
    $correctionForm = new UserDataCorrectionForm($currentUser);
    $correctionForm->processSubmission($_POST);
}

WebPage::singleton()->addItem(new PageTop(_('My Profile')));

// Create main container
$container = WebPage::singleton()->container;

// Profile header section
$profileHeader = new \Ease\TWB4\Card(_('Profile Information'));
$profileHeader->addItem(new \Ease\Html\DivTag([
    new \Ease\Html\H4Tag($currentUser->getUserName()),
    new \Ease\Html\PTag([
        new \Ease\Html\StrongTag(_('Login').': '),
        $currentUser->getDataValue('login'),
    ]),
    new \Ease\Html\PTag([
        new \Ease\Html\StrongTag(_('Email').': '),
        $currentUser->getDataValue('email') ?: _('(not set)'),
    ]),
    new \Ease\Html\PTag([
        new \Ease\Html\StrongTag(_('Member since').': '),
        date('F j, Y', strtotime($currentUser->getDataValue($currentUser->createColumn))),
    ]),
]));

$container->addItem($profileHeader);

// Data correction form
$correctionFormCard = new \Ease\TWB4\Card(_('Update Personal Information'));
$correctionForm = new UserDataCorrectionForm($currentUser);
$correctionFormCard->addItem($correctionForm);

$container->addItem($correctionFormCard);

// GDPR Information section
$gdprInfo = new \Ease\TWB4\Card(_('Your Rights Under GDPR'));
$gdprInfo->addItem(new \Ease\Html\PTag(_('Under the General Data Protection Regulation (GDPR), you have several rights regarding your personal data:')));

$rightsList = new \Ease\Html\UlTag([
    new \Ease\Html\LiTag([
        new \Ease\Html\StrongTag(_('Right of Rectification (Article 16)').': '),
        _('You can request correction of inaccurate personal data using the form above.'),
    ]),
    new \Ease\Html\LiTag([
        new \Ease\Html\StrongTag(_('Right of Access (Article 15)').': '),
        new \Ease\TWB4\LinkButton('data-export.php', _('Export your data'), 'info', ['size' => 'sm', 'title' => _('Export your personal data'), 'id' => 'exportdatabutton']),
    ]),
    new \Ease\Html\LiTag([
        new \Ease\Html\StrongTag(_('Right to be Forgotten (Article 17)').': '),
        _('Contact an administrator to request account deletion.'),
    ]),
    new \Ease\Html\LiTag([
        new \Ease\Html\StrongTag(_('Right to Data Portability (Article 20)').': '),
        _('You can export your data in a machine-readable format.'),
    ]),
]);

$gdprInfo->addItem($rightsList);
$container->addItem($gdprInfo);

// Recent data changes (audit log preview)
$auditLogger = new \MultiFlexi\Audit\UserDataAuditLogger();
$recentChanges = $auditLogger->getUserAuditLog($currentUser->getId(), 10);

if (!empty($recentChanges)) {
    $auditCard = new \Ease\TWB4\Card(_('Recent Data Changes'));

    $auditTable = new \Ease\Html\TableTag(null, ['class' => 'table table-sm']);
    $auditTable->addRowHeaderColumns([
        _('Field'),
        _('Old Value'),
        _('New Value'),
        _('Type'),
        _('Date'),
    ]);

    foreach ($recentChanges as $change) {
        $fieldDisplayName = \MultiFlexi\GDPR\UserDataCorrectionRequest::getFieldDisplayName($change['field_name']);
        $changeTypeBadge = '';

        switch ($change['change_type']) {
            case 'direct':
                $changeTypeBadge = new \Ease\TWB4\Badge(_('Direct'), 'success');

                break;
            case 'pending_approval':
                $changeTypeBadge = new \Ease\TWB4\Badge(_('Pending'), 'warning');

                break;
            case 'approved':
                $changeTypeBadge = new \Ease\TWB4\Badge(_('Approved'), 'success');

                break;
            case 'rejected':
                $changeTypeBadge = new \Ease\TWB4\Badge(_('Rejected'), 'danger');

                break;
        }

        $auditTable->addRowColumns([
            $fieldDisplayName,
            substr($change['old_value'], 0, 30),
            substr($change['new_value'], 0, 30),
            $changeTypeBadge,
            date('M j, Y H:i', strtotime($change['created_at'])),
        ]);
    }

    $auditCard->addItem($auditTable);
    $container->addItem($auditCard);
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
