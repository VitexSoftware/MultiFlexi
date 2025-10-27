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

/**
 * User Deletion Request Form.
 *
 * GDPR Article 17 - Right of Erasure request form
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class UserDeletionRequestForm extends SecureForm
{
    /**
     * @var User Target user for deletion
     */
    private User $targetUser;

    /**
     * @var bool Whether current user can delete themselves
     */
    private bool $isSelfDeletion;

    /**
     * Constructor.
     *
     * @param User  $targetUser     User to request deletion for
     * @param array $formProperties Form properties
     */
    public function __construct(User $targetUser, array $formProperties = [])
    {
        $this->targetUser = $targetUser;
        $currentUser = User::singleton();
        $this->isSelfDeletion = $currentUser->getId() === $targetUser->getId();

        $defaultProperties = [
            'method' => 'post',
            'action' => 'gdpr-user-deletion-request.php',
            'class' => 'form-horizontal',
        ];

        parent::__construct(array_merge($defaultProperties, $formProperties));

        $this->buildForm();
    }

    /**
     * Build the deletion request form.
     */
    private function buildForm(): void
    {
        // Check if user can request deletion
        $canRequest = UserDataEraser::canRequestDeletion($this->targetUser);

        if (!$canRequest['allowed']) {
            $this->addItem(new \Ease\TWB4\Alert(
                _('Deletion Request Not Allowed'),
                'danger',
                $canRequest['reason'],
            ));

            return;
        }

        // Warning banner
        $this->addItem($this->createWarningBanner());

        // Hidden field for target user ID
        $this->addItem(new \Ease\Html\InputHiddenTag('target_user_id', $this->targetUser->getId()));

        // User information section
        $this->addItem($this->createUserInfoSection());

        // Deletion type selection
        $this->addItem(self::createDeletionTypeSection());

        // Reason section
        $this->addItem(self::createReasonSection());

        // Legal information
        $this->addItem(self::createLegalInfoSection());

        // Confirmation checkboxes
        $this->addItem($this->createConfirmationSection());

        // Submit buttons
        $this->addItem($this->createSubmitSection());
    }

    /**
     * Create warning banner.
     */
    private function createWarningBanner(): \Ease\TWB4\Alert
    {
        $title = $this->isSelfDeletion ?
            _('⚠️ Account Deletion Request') :
            _('⚠️ User Deletion Request');

        $message = $this->isSelfDeletion ?
            _('You are about to request deletion of your own account. This action cannot be undone and will result in the permanent loss of your data.') :
            _('You are about to request deletion of another user\'s account. This action requires administrator approval and cannot be undone.');

        return new \Ease\TWB4\Alert($title, 'warning', $message);
    }

    /**
     * Create user information section.
     */
    private function createUserInfoSection(): \Ease\TWB4\Card
    {
        $card = new \Ease\TWB4\Card(_('User Information'));

        $userInfo = new \Ease\TWB4\Container();
        $userInfo->addItem(new \Ease\Html\StrongTag(_('User Login: ')));
        $userInfo->addItem($this->targetUser->getDataValue('login'));
        $userInfo->addItem(new \Ease\Html\PTag());

        $userInfo->addItem(new \Ease\Html\StrongTag(_('Full Name: ')));
        $userInfo->addItem($this->targetUser->getUserName());
        $userInfo->addItem(new \Ease\Html\PTag());

        $userInfo->addItem(new \Ease\Html\StrongTag(_('Email: ')));
        $userInfo->addItem($this->targetUser->getEmail());
        $userInfo->addItem(new \Ease\Html\PTag());

        $userInfo->addItem(new \Ease\Html\StrongTag(_('Account Created: ')));
        $userInfo->addItem($this->targetUser->getDataValue('DatCreate'));

        $card->addItem($userInfo);

        return $card;
    }

    /**
     * Create deletion type selection section.
     */
    private static function createDeletionTypeSection(): \Ease\TWB4\Card
    {
        $card = new \Ease\TWB4\Card(_('Deletion Type'));

        // Soft deletion option
        $softOption = new \Ease\TWB4\FormGroup();
        $softOption->addItem(new \Ease\Html\InputRadioTag('deletion_type', 'soft', true));
        $softOption->addItem(' ');
        $softOption->addItem(new \Ease\Html\StrongTag(_('Soft Deletion (Recommended)')));
        $softOption->addItem(new \Ease\Html\PTag());
        $softOption->addItem(_('Account will be disabled and marked as deleted, but data will be retained for legal compliance. This can be processed immediately.'));

        // Hard deletion option (admin only)
        $hardOption = new \Ease\TWB4\FormGroup();
        $hardOption->addItem(new \Ease\Html\InputRadioTag('deletion_type', 'hard', false, ['data-requires-approval' => 'true']));
        $hardOption->addItem(' ');
        $hardOption->addItem(new \Ease\Html\StrongTag(_('Hard Deletion')));
        $hardOption->addItem(' ');
        $hardOption->addItem(new \Ease\TWB4\Badge('danger', _('Requires Admin Approval')));
        $hardOption->addItem(new \Ease\Html\PTag());
        $hardOption->addItem(_('Permanently removes all user data except what\'s required for legal compliance. Cannot be undone.'));

        // Anonymization option
        $anonymizeOption = new \Ease\TWB4\FormGroup();
        $anonymizeOption->addItem(new \Ease\Html\InputRadioTag('deletion_type', 'anonymize', false, ['data-requires-approval' => 'true']));
        $anonymizeOption->addItem(' ');
        $anonymizeOption->addItem(new \Ease\Html\StrongTag(_('Data Anonymization')));
        $anonymizeOption->addItem(' ');
        $anonymizeOption->addItem(new \Ease\TWB4\Badge('warning', _('Requires Admin Approval')));
        $anonymizeOption->addItem(new \Ease\Html\PTag());
        $anonymizeOption->addItem(_('Replaces personal data with anonymous values while preserving data structure. Account will be disabled.'));

        $card->addItem($softOption);
        $card->addItem($hardOption);
        $card->addItem($anonymizeOption);

        return $card;
    }

    /**
     * Create reason section.
     */
    private static function createReasonSection(): \Ease\TWB4\Card
    {
        $card = new \Ease\TWB4\Card(_('Reason for Deletion'));

        $reasonField = new \Ease\TWB4\FormGroup(_('Please explain why you are requesting this deletion:'));
        $reasonTextarea = new \Ease\Html\TextareaTag('reason', '', [
            'class' => 'form-control',
            'rows' => 4,
            'placeholder' => _('e.g., No longer using the service, privacy concerns, account consolidation...'),
        ]);
        $reasonField->addItem($reasonTextarea);

        $card->addItem($reasonField);

        return $card;
    }

    /**
     * Create legal information section.
     */
    private static function createLegalInfoSection(): \Ease\TWB4\Card
    {
        $card = new \Ease\TWB4\Card(_('Legal Information'));

        $legalInfo = new \Ease\Html\PTag();
        $legalInfo->addItem(_('This request is made under '));
        $legalInfo->addItem(new \Ease\Html\StrongTag(_('Article 17 of the EU General Data Protection Regulation (GDPR)')));
        $legalInfo->addItem(_(', which grants you the right to erasure ("right to be forgotten").'));

        $card->addItem($legalInfo);

        $processingInfo = new \Ease\Html\UlTag();
        $processingInfo->addItem(new \Ease\Html\LiTag(_('Soft deletion requests are typically processed within 24 hours')));
        $processingInfo->addItem(new \Ease\Html\LiTag(_('Hard deletion and anonymization requests require administrator review')));
        $processingInfo->addItem(new \Ease\Html\LiTag(_('Some data may be retained for legal compliance purposes')));
        $processingInfo->addItem(new \Ease\Html\LiTag(_('You will receive confirmation when the request is processed')));

        $card->addItem($processingInfo);

        return $card;
    }

    /**
     * Create confirmation section.
     */
    private function createConfirmationSection(): \Ease\TWB4\Card
    {
        $card = new \Ease\TWB4\Card(_('Confirmation'));

        // Understanding confirmation
        $understandingCheck = new \Ease\TWB4\FormGroup();
        $understandingCheck->addItem(new \Ease\Html\InputCheckboxTag('confirm_understanding', '1', false, ['required' => 'required']));
        $understandingCheck->addItem(' ');
        $understandingCheck->addItem(_('I understand that this action cannot be easily undone and may result in permanent data loss'));

        // Legal basis confirmation
        $legalCheck = new \Ease\TWB4\FormGroup();
        $legalCheck->addItem(new \Ease\Html\InputCheckboxTag('confirm_legal_basis', '1', false, ['required' => 'required']));
        $legalCheck->addItem(' ');
        $legalCheck->addItem(_('I confirm that this request is made under my right to erasure as granted by GDPR Article 17'));

        // Self-deletion confirmation (only for self-deletion)
        if ($this->isSelfDeletion) {
            $selfCheck = new \Ease\TWB4\FormGroup();
            $selfCheck->addItem(new \Ease\Html\InputCheckboxTag('confirm_self_deletion', '1', false, ['required' => 'required']));
            $selfCheck->addItem(' ');
            $selfCheck->addItem(_('I confirm that I am requesting deletion of my own account'));
            $card->addItem($selfCheck);
        }

        $card->addItem($understandingCheck);
        $card->addItem($legalCheck);

        return $card;
    }

    /**
     * Create submit section.
     */
    private function createSubmitSection(): \Ease\TWB4\Container
    {
        $container = new \Ease\TWB4\Container();

        $buttonGroup = new \Ease\TWB4\ButtonGroup();

        $submitButton = new \Ease\TWB4\SubmitButton(_('Submit Deletion Request'), 'danger', [
            'onclick' => 'return confirmDeletionRequest();',
        ]);
        $submitButton->addTagClass('btn-lg');

        $cancelButton = new \Ease\TWB4\LinkButton('user.php?id='.$this->targetUser->getId(), _('Cancel'), 'secondary');
        $cancelButton->addTagClass('btn-lg');

        $buttonGroup->addItem($cancelButton);
        $buttonGroup->addItem($submitButton);

        $container->addItem($buttonGroup);

        // Add JavaScript for confirmation
        $container->addItem(self::getJavaScript());

        return $container;
    }

    /**
     * Get JavaScript for form validation and confirmation.
     */
    private static function getJavaScript(): \Ease\Html\ScriptTag
    {
        $js = <<<'JS'
function confirmDeletionRequest() {
    const deletionType = document.querySelector('input[name="deletion_type"]:checked').value;
    const userName = document.querySelector('input[name="target_user_id"]').value;

    let confirmMessage = '';

    switch(deletionType) {
        case 'soft':
            confirmMessage = 'Are you sure you want to request soft deletion? The account will be disabled but data will be preserved for legal compliance.';
            break;
        case 'hard':
            confirmMessage = 'Are you sure you want to request PERMANENT HARD DELETION? This will remove all data and cannot be undone. This request requires administrator approval.';
            break;
        case 'anonymize':
            confirmMessage = 'Are you sure you want to request data anonymization? All personal information will be replaced with anonymous values. This request requires administrator approval.';
            break;
    }

    return confirm(confirmMessage);
}

// Show/hide approval requirement notices
document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="deletion_type"]');

    radioButtons.forEach(function(radio) {
        radio.addEventListener('change', function() {
            const requiresApproval = this.hasAttribute('data-requires-approval');
            const submitButton = document.querySelector('button[type="submit"]');

            if (requiresApproval) {
                submitButton.innerHTML = 'Submit for Admin Review';
                submitButton.classList.remove('btn-danger');
                submitButton.classList.add('btn-warning');
            } else {
                submitButton.innerHTML = 'Submit Deletion Request';
                submitButton.classList.remove('btn-warning');
                submitButton.classList.add('btn-danger');
            }
        });
    });
});
JS;

        return new \Ease\Html\ScriptTag($js);
    }
}
