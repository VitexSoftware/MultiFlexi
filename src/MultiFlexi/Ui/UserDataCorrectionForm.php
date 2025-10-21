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

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTag;
use Ease\Html\TextareaTag;
use Ease\TWB4\Alert;
use Ease\TWB4\Badge;
use Ease\TWB4\Form;
use Ease\TWB4\SubmitButton;
use MultiFlexi\Audit\UserDataAuditLogger;
use MultiFlexi\GDPR\UserDataCorrectionRequest;
use MultiFlexi\User;

/**
 * Enhanced User Form with GDPR Article 16 compliance for data rectification.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class UserDataCorrectionForm extends Form
{
    /**
     * User holder.
     */
    public User $user;

    /**
     * Current logged in user (who is editing).
     */
    public User $currentUser;

    /**
     * Correction request handler.
     */
    private UserDataCorrectionRequest $correctionRequest;

    /**
     * Audit logger.
     */
    private UserDataAuditLogger $auditLogger;

    /**
     * Whether current user can edit directly (admin or self for non-sensitive fields).
     */
    private bool $canEditDirectly = false;

    /**
     * @param User      $user        User to edit
     * @param null|User $currentUser Current logged in user (null = same as user)
     */
    public function __construct(User $user, ?User $currentUser = null)
    {
        $userID = $user->getMyKey();
        $this->user = $user;
        $this->currentUser = $currentUser ?: $user;
        $this->correctionRequest = new UserDataCorrectionRequest();
        $this->auditLogger = new UserDataAuditLogger();

        $this->canEditDirectly = $this->currentUser->getId() === $user->getId()
                                || self::isAdmin($this->currentUser);

        parent::__construct([
            'name' => 'user_correction_form_'.$userID,
            'method' => 'POST',
            'action' => $_SERVER['REQUEST_URI'],
        ]);

        $this->buildForm();
    }

    /**
     * Process form submission.
     *
     * @param array $formData Posted form data
     *
     * @return bool Success of processing
     */
    public function processSubmission(array $formData): bool
    {
        $success = true;
        $processed = false;

        foreach (UserDataCorrectionRequest::DIRECT_FIELDS as $fieldName => $displayName) {
            if (isset($formData[$fieldName]) && $this->canEditDirectly) {
                $oldValue = $this->user->getDataValue($fieldName);
                $newValue = trim($formData[$fieldName]);

                if ($oldValue !== $newValue && $this->validateField($fieldName, $newValue)) {
                    $this->user->setDataValue($fieldName, $newValue);
                    $this->auditLogger->logDataChange(
                        $this->user->getId(),
                        $fieldName,
                        $oldValue,
                        $newValue,
                        'direct',
                        $this->currentUser->getId(),
                    );
                    $processed = true;
                }
            }
        }

        // Handle sensitive fields that require approval
        foreach (UserDataCorrectionRequest::SENSITIVE_FIELDS as $fieldName => $displayName) {
            $newValueField = $fieldName.'_new';
            $justificationField = $fieldName.'_justification';

            if (isset($formData[$newValueField]) && !empty(trim($formData[$newValueField]))) {
                $currentValue = $this->user->getDataValue($fieldName);
                $requestedValue = trim($formData[$newValueField]);
                $justification = trim($formData[$justificationField] ?? '');

                if ($currentValue !== $requestedValue && $this->validateField($fieldName, $requestedValue)) {
                    if (self::isAdmin($this->currentUser)) {
                        // Admin can make direct changes
                        $this->user->setDataValue($fieldName, $requestedValue);
                        $this->auditLogger->logDataChange(
                            $this->user->getId(),
                            $fieldName,
                            $currentValue,
                            $requestedValue,
                            'direct',
                            $this->currentUser->getId(),
                            null,
                            null,
                            'Admin direct change: '.$justification,
                        );
                        $processed = true;
                    } else {
                        // Regular user - create correction request
                        if ($this->correctionRequest->createRequest(
                            $this->user->getId(),
                            $fieldName,
                            $currentValue,
                            $requestedValue,
                            $justification,
                        )) {
                            $this->addStatusMessage(
                                sprintf(_('Your request to change %s has been submitted for administrator review.'), $displayName),
                                'info',
                            );
                            $processed = true;
                        } else {
                            $success = false;
                        }
                    }
                }
            }
        }

        if ($processed && $success) {
            if ($this->user->dbsync()) {
                $this->addStatusMessage(_('Profile updated successfully'), 'success');
            } else {
                $success = false;
                $this->addStatusMessage(_('Failed to update profile'), 'error');
            }
        }

        return $success;
    }

    /**
     * Build the form with appropriate fields and validation.
     */
    private function buildForm(): void
    {
        $this->addItem(new Alert(
            '<strong>'._('GDPR Article 16 - Right of Rectification').'</strong><br>'.
            _('You have the right to request correction of inaccurate personal data. Some changes require administrator approval.'),
            'info',
        ));

        // Show user's pending requests if any
        $this->showPendingRequests();

        // Personal Information Section
        $this->addItem(new \Ease\Html\H3Tag(_('Personal Information')));

        // First Name - can be changed directly
        $this->addPersonalDataField('firstname', _('First Name'), 'text', false);

        // Last Name - can be changed directly
        $this->addPersonalDataField('lastname', _('Last Name'), 'text', false);

        // Email - requires approval for sensitive changes
        $this->addPersonalDataField('email', _('Email Address'), 'email', true);

        // Username - requires approval
        $this->addPersonalDataField('login', _('Username'), 'text', true);

        // Hidden fields
        $this->addItem(new InputHiddenTag('class', $this->user::class));

        if ($userID) {
            $this->addItem(new InputHiddenTag($this->user->keyColumn, $userID));
        }

        // Submit button
        $this->addItem(new \Ease\Html\DivTag(
            new SubmitButton(_('Save Changes'), 'primary'),
            ['style' => 'text-align: right; margin-top: 20px;'],
        ));
    }

    /**
     * Add a personal data field with appropriate handling.
     *
     * @param string $fieldName        Database field name
     * @param string $label            Human-readable label
     * @param string $inputType        HTML input type
     * @param bool   $requiresApproval Whether changes to this field require approval
     */
    private function addPersonalDataField(string $fieldName, string $label, string $inputType, bool $requiresApproval): void
    {
        $currentValue = $this->user->getDataValue($fieldName);
        $canEditDirectly = $this->canEditDirectly && (!$requiresApproval || self::isAdmin($this->currentUser));

        // Create container for the field
        $fieldContainer = new \Ease\TWB4\FormGroup();

        // Label with info badge
        $labelContent = $label;

        if ($requiresApproval && !self::isAdmin($this->currentUser)) {
            $labelContent .= ' '.new Badge(_('Requires Approval'), 'warning');
        }

        $fieldContainer->addItem(new \Ease\Html\LabelTag($fieldName, $labelContent));

        if ($canEditDirectly) {
            // Regular input field for direct editing
            $input = new InputTag($fieldName, $currentValue, ['type' => $inputType]);
            $input->addTagClass('form-control');

            // Add validation
            self::addValidationAttributes($input, $fieldName, $inputType);

            $fieldContainer->addItem($input);
        } else {
            // Show current value and request change option
            $fieldContainer->addItem(new \Ease\Html\DivTag([
                new \Ease\Html\StrongTag(_('Current Value').': '),
                new \Ease\Html\SpanTag($currentValue ?: _('(not set)'), ['class' => 'text-muted']),
            ], ['class' => 'mb-2']));

            if ($requiresApproval) {
                // Add request change fields
                $newInput = new InputTag($fieldName.'_new', '', ['type' => $inputType]);
                $newInput->addTagClass('form-control');
                $newInput->setTagProperty('placeholder', _('Enter new value'));
                self::addValidationAttributes($newInput, $fieldName, $inputType);

                $fieldContainer->addItem(new \Ease\Html\LabelTag($fieldName.'_new', _('Requested New Value')));
                $fieldContainer->addItem($newInput);

                // Justification textarea
                $justificationTextarea = new TextareaTag($fieldName.'_justification', '', [
                    'class' => 'form-control',
                    'rows' => 2,
                    'placeholder' => _('Please explain why this change is needed (optional)'),
                ]);

                $fieldContainer->addItem(new \Ease\Html\LabelTag($fieldName.'_justification', _('Justification')));
                $fieldContainer->addItem($justificationTextarea);
            }
        }

        $this->addItem($fieldContainer);
    }

    /**
     * Add validation attributes to input field.
     *
     * @param InputTag $input     Input element
     * @param string   $fieldName Field name
     * @param string   $inputType Input type
     */
    private static function addValidationAttributes(InputTag $input, string $fieldName, string $inputType): void
    {
        switch ($fieldName) {
            case 'email':
                $input->setTagProperty('pattern', '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$');
                $input->setTagProperty('title', _('Please enter a valid email address'));

                break;
            case 'firstname':
            case 'lastname':
                $input->setTagProperty('pattern', '[A-Za-z\\s\\-\'\.]{2,50}');
                $input->setTagProperty('title', _('Only letters, spaces, hyphens, and apostrophes allowed (2-50 characters)'));
                $input->setTagProperty('maxlength', '50');

                break;
            case 'login':
                $input->setTagProperty('pattern', '[a-zA-Z0-9_\\-\\.]{3,30}');
                $input->setTagProperty('title', _('Only letters, numbers, underscore, hyphen, and dot allowed (3-30 characters)'));
                $input->setTagProperty('maxlength', '30');

                break;
        }
    }

    /**
     * Show user's pending correction requests.
     */
    private function showPendingRequests(): void
    {
        $pendingRequests = $this->correctionRequest->getUserRequests($this->user->getId(), 5);

        if (!empty($pendingRequests)) {
            $pendingPanel = new \Ease\TWB4\Card(_('Your Data Change Requests'));

            foreach ($pendingRequests as $request) {
                $statusBadge = self::getStatusBadge($request['status']);
                $fieldDisplayName = UserDataCorrectionRequest::getFieldDisplayName($request['field_name']);

                $requestItem = new \Ease\Html\DivTag([
                    new \Ease\Html\StrongTag($fieldDisplayName.': '),
                    new \Ease\Html\SpanTag($request['current_value'].' → '.$request['requested_value']),
                    ' ',
                    $statusBadge,
                    new \Ease\Html\SmallTag(' ('.date('M j, Y', strtotime($request['created_at'])).')', ['class' => 'text-muted']),
                ], ['class' => 'mb-1']);

                if ($request['reviewer_notes'] && \in_array($request['status'], ['approved', 'rejected'], true)) {
                    $requestItem->addItem(new \Ease\Html\DivTag([
                        new \Ease\Html\SmallTag(_('Admin notes').': '.$request['reviewer_notes'], ['class' => 'text-muted']),
                    ]));
                }

                $pendingPanel->addItem($requestItem);
            }

            $this->addItem($pendingPanel);
        }
    }

    /**
     * Get status badge for request status.
     *
     * @param string $status Request status
     *
     * @return Badge Status badge
     */
    private static function getStatusBadge(string $status): Badge
    {
        switch ($status) {
            case 'pending':
                return new Badge(_('Pending Review'), 'warning');
            case 'approved':
                return new Badge(_('Approved'), 'success');
            case 'rejected':
                return new Badge(_('Rejected'), 'danger');
            case 'cancelled':
                return new Badge(_('Cancelled'), 'secondary');

            default:
                return new Badge($status, 'secondary');
        }
    }

    /**
     * Check if user is admin.
     *
     * @param User $user User to check
     *
     * @return bool True if user is admin
     */
    private static function isAdmin(User $user): bool
    {
        // This would need to be implemented based on your role system
        // For now, we'll check if user has admin permissions
        return $user->getSettingValue('admin') === true
               || $user->getDataValue('role') === 'admin';
    }

    /**
     * Validate field value.
     *
     * @param string $fieldName Field name
     * @param string $value     Field value
     *
     * @return bool Validation result
     */
    private function validateField(string $fieldName, string $value): bool
    {
        switch ($fieldName) {
            case 'email':
                if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
                    $this->addStatusMessage(_('Invalid email address format'), 'error');

                    return false;
                }

                break;
            case 'firstname':
            case 'lastname':
                if (\strlen($value) < 2 || \strlen($value) > 50 || !preg_match('/^[A-Za-z\s\-\'\.]+$/', $value)) {
                    $this->addStatusMessage(
                        sprintf(
                            _('Invalid %s: only letters, spaces, hyphens, and apostrophes allowed (2-50 characters)'),
                            $fieldName === 'firstname' ? _('first name') : _('last name'),
                        ),
                        'error',
                    );

                    return false;
                }

                break;
            case 'login':
                if (\strlen($value) < 3 || \strlen($value) > 30 || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $value)) {
                    $this->addStatusMessage(_('Invalid username: only letters, numbers, underscore, hyphen, and dot allowed (3-30 characters)'), 'error');

                    return false;
                }

                // Check if username already exists
                $existingUser = new User();

                if ($existingUser->loadFromSQL(['login' => $value]) && $existingUser->getId() !== $this->user->getId()) {
                    $this->addStatusMessage(_('Username already exists'), 'error');

                    return false;
                }

                break;
        }

        return true;
    }
}
