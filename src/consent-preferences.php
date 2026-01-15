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

use MultiFlexi\Consent\ConsentManager;

require_once 'init.php';

WebPage::singleton()->onlyForLogged();

$consentManager = new ConsentManager();
$currentConsent = $consentManager->getAllConsentStatuses();

WebPage::singleton()->addItem(new PageTop(_('Privacy & Consent')));
WebPage::singleton()->container->addItem(new \Ease\Html\H1Tag(_('Privacy & Consent Preferences')));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consent'])) {
    $success = true;
    $errors = [];

    foreach (ConsentManager::getConsentTypes() as $type) {
        $status = isset($_POST["consent_{$type}"]);

        // Essential consent is always required
        if ($type === ConsentManager::CONSENT_ESSENTIAL) {
            $status = true;
        }

        if (!$consentManager->recordConsent($type, $status)) {
            $success = false;
            $errors[] = sprintf(_('Failed to save consent for %s'), $type);
        }
    }

    if ($success) {
        WebPage::singleton()->addStatusMessage(_('Your consent preferences have been updated successfully.'), 'success');
        // Refresh consent data
        $currentConsent = $consentManager->getAllConsentStatuses();
    } else {
        WebPage::singleton()->addStatusMessage(_('Some consent preferences could not be saved: ').implode(', ', $errors), 'error');
    }
}

// Create form container
$formContainer = new \Ease\Html\DivTag();

$formContainer->addItem(new \Ease\Html\PTag(_('Manage your privacy preferences and control how your data is processed. You can change these settings at any time.')));

// Consent types and descriptions
$consentTypes = [
    ConsentManager::CONSENT_ESSENTIAL => [
        'name' => _('Essential Cookies'),
        'description' => _('These cookies are necessary for the website to function and cannot be switched off. They are usually only set in response to actions made by you which amount to a request for services.'),
        'required' => true,
        'icon' => 'fas fa-shield-alt',
    ],
    ConsentManager::CONSENT_FUNCTIONAL => [
        'name' => _('Functional Cookies'),
        'description' => _('These cookies enable the website to provide enhanced functionality and personalisation. They may be set by us or by third party providers.'),
        'required' => false,
        'icon' => 'fas fa-cogs',
    ],
    ConsentManager::CONSENT_ANALYTICS => [
        'name' => _('Analytics Cookies'),
        'description' => filter_var(\Ease\Shared::cfg('ENABLE_GOOGLE_ANALYTICS', 'false'), \FILTER_VALIDATE_BOOLEAN)
            ? _('These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site. They help us know which pages are most and least popular.')
            : _('These cookies allow us to count visits and traffic sources using self-hosted analytics solutions (like Matomo or AWStats) to measure and improve the performance of our site. No data is shared with third parties.'),
        'required' => false,
        'icon' => 'fas fa-chart-bar',
    ],
    ConsentManager::CONSENT_MARKETING => [
        'name' => _('Marketing Cookies'),
        'description' => _('These cookies may be set through our site by our advertising partners. They may be used to build a profile of your interests and show you relevant adverts.'),
        'required' => false,
        'icon' => 'fas fa-bullhorn',
    ],
    ConsentManager::CONSENT_PERSONALIZATION => [
        'name' => _('Personalization Cookies'),
        'description' => _('These cookies allow us to remember choices you make and provide enhanced, more personal features based on your preferences.'),
        'required' => false,
        'icon' => 'fas fa-user-cog',
    ],
];

foreach ($consentTypes as $type => $config) {
    // Get current consent status
    $currentStatus = isset($currentConsent[$type]) ? $currentConsent[$type]['status'] : false;

    // Create panel with header
    $headerContent = new \Ease\Html\H5Tag([
        new \Ease\TWB4\Widgets\FaIcon($config['icon'], ['class' => 'me-2']),
        $config['name'],
    ], ['class' => 'mb-0']);

    $panel = new \Ease\TWB4\Panel($headerContent, 'info');
    $panel->addTagClass('mb-3');

    // Add description
    $panel->addItem(new \Ease\Html\PTag($config['description'], ['class' => 'card-text']));

    // Create switch
    $switch = new \Ease\TWB4\Widgets\Toggle("consent_{$type}", $currentStatus);
    $switch->setTagProperty('label', $config['name']);

    if ($config['required']) {
        $switch->setValue(true);
        $switch->setTagProperty('disabled', 'disabled');
        $panel->addItem(new \Ease\TWB4\Badge('success', _('Required')));
        $panel->addItem(new \Ease\Html\PTag(''));
    }

    $panel->addItem($switch);

    // Show last updated date if available
    if (isset($currentConsent[$type]['granted_at'])) {
        $panel->addItem(new \Ease\Html\SmallTag([
            _('Last updated: '),
            new \Ease\Html\StrongTag(date('Y-m-d H:i', strtotime($currentConsent[$type]['granted_at']))),
        ], ['class' => 'text-muted d-block mt-2']));
    }

    $formContainer->addItem($panel);
}

// Add save button
$buttonRow = new \Ease\TWB4\Row();
$buttonCol = new \Ease\TWB4\Col(12);
$buttonCol->addItem(new \Ease\TWB4\SubmitButton(_('Save Preferences'), 'success', ['name' => 'save_consent', 'class' => 'btn-lg', 'id' => 'saveconsentpreferencesbutton']));
$buttonRow->addItem($buttonCol);
$formContainer->addItem($buttonRow);

// Add CSRF token to form if CSRF protection is enabled

if (\Ease\Shared::cfg('CSRF_PROTECTION_ENABLED', true) && isset($GLOBALS['csrfProtection'])) {
    $csrfToken = $GLOBALS['csrfProtection']->generateToken();
    $formContainer->addItem(new \Ease\Html\InputHiddenTag('csrf_token', $csrfToken));
}

// Create the actual form
$form = new SecureForm(
    ['name' => 'consent-form', 'action' => 'consent-preferences.php', 'method' => 'POST'],
    $formContainer,
);

WebPage::singleton()->container->addItem($form);

// Add consent history section
$historyHeaderContent = new \Ease\Html\H5Tag([
    new \Ease\TWB4\Widgets\FaIcon('fas fa-history', ['class' => 'me-2']),
    _('Consent History'),
], ['class' => 'mb-0']);

$historyPanel = new \Ease\TWB4\Panel($historyHeaderContent, 'secondary');
$historyPanel->addTagClass('mt-4');

// Create table for consent history - temporarily disabled
// $table = new \Ease\TWB4\Table();
// $table->setTagProperty('class', 'table table-striped');
// $table->addRowHeaderColumns([
//     _('Consent Type'),
//     _('Status'),
//     _('Date'),
//     _('Actions')
// ]);

$table = new \Ease\Html\DivTag('Table temporarily disabled for debugging');

// Temporarily commenting out table operations
/*
if (!empty($currentConsent)) {
    foreach ($currentConsent as $type => $consent) {
        $statusBadge = new \Ease\TWB4\Badge($consent['status'] ? 'success' : 'danger',
                                           $consent['status'] ? _('Granted') : _('Denied'));

        $withdrawButton = '';
        if ($consent['status'] && $type !== ConsentManager::CONSENT_ESSENTIAL) {
            $withdrawButton = new \Ease\TWB4\Button(
                [new \Ease\TWB4\Widgets\FaIcon('fas fa-times'), ' ', _('Withdraw')],
                'outline-danger',
                ['size' => 'sm', 'onclick' => "withdrawConsent('{$type}')"]
            );
        }

        $table->addRowColumns([
            $consentTypes[$type]['name'] ?? ucfirst($type),
            $statusBadge,
            date('Y-m-d H:i', strtotime($consent['granted_at'])),
            $withdrawButton
        ]);
    }
} else {
    $table->addRowColumns([
        _('No consent data available'),
        '',
        '',
        ''
    ]);
}
 */

$historyPanel->addItem($table);

WebPage::singleton()->container->addItem($historyPanel);

// Add JavaScript for withdraw functionality
WebPage::singleton()->container->includeJavaScript(<<<'EOD'

function withdrawConsent(type) {
    if (confirm('
EOD._('Are you sure you want to withdraw this consent?').<<<'EOD'
')) {
        fetch('consent-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                action: 'withdraw_consent',
                consent_type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('
EOD._('Error withdrawing consent: ').<<<'EOD'
' + data.message);
            }
        })
        .catch(error => {
            alert('
EOD._('Error withdrawing consent').<<<'EOD'
');
        });
    }
}

EOD);

// Add information section
$infoHeaderContent = new \Ease\Html\H5Tag([
    new \Ease\TWB4\Widgets\FaIcon('fas fa-info-circle', ['class' => 'me-2']),
    _('Important Information'),
], ['class' => 'mb-0']);

$infoPanel = new \Ease\TWB4\Panel($infoHeaderContent, 'info');
$infoPanel->addTagClass('mt-4');

$infoPanel->addItem(new \Ease\Html\UlTag([
    new \Ease\Html\LiTag(_('You can change your consent preferences at any time.')),
    new \Ease\Html\LiTag(_('Essential cookies cannot be disabled as they are required for the site to function.')),
    new \Ease\Html\LiTag(_('Withdrawing consent may affect your experience on the site.')),
    new \Ease\Html\LiTag([_('For more information, see our '), new \Ease\Html\ATag('privacy-policy.php', _('Privacy Policy'))]),
]));

WebPage::singleton()->container->addItem($infoPanel);

WebPage::singleton()->container->addItem(new PageBottom());
WebPage::singleton()->draw();
