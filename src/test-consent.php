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

use MultiFlexi\Consent\ConsentHelper;
use MultiFlexi\Consent\ConsentManager;

require_once 'init.php';

WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new \Ease\Html\H1(_('GDPR Consent Management Test')));

$consentManager = new ConsentManager();

// Test section
$testCard = new \Ease\TWB4\Card();
$testCard->addItem(new \Ease\TWB4\CardHeader(new \Ease\Html\H4(_('Consent Status Test'))));

$cardBody = new \Ease\TWB4\CardBody();

// Display current consent statuses
$consents = $consentManager->getAllConsentStatuses();

if (empty($consents)) {
    $cardBody->addItem(new \Ease\TWB4\Alert('info', _('No consent preferences set yet. The consent banner should appear.')));
} else {
    $table = new \Ease\TWB4\Table(['class' => 'table table-striped']);
    $table->addRowHeaderColumns([_('Consent Type'), _('Status'), _('Date')]);

    foreach ($consents as $type => $consent) {
        $statusBadge = new \Ease\TWB4\Badge(
            $consent['status'] ? 'success' : 'danger',
            $consent['status'] ? _('Granted') : _('Denied'),
        );

        $table->addRowColumns([
            ucfirst($type),
            $statusBadge,
            date('Y-m-d H:i', strtotime($consent['granted_at'])),
        ]);
    }

    $cardBody->addItem($table);
}

// Test helper functions
$cardBody->addItem(new \Ease\Html\H5(_('Helper Function Tests')));

$helperTests = new \Ease\Html\UlTag([
    new \Ease\Html\LiTag(_('Analytics Consent: ').(ConsentHelper::hasAnalyticsConsent() ? '✅ Granted' : '❌ Denied')),
    new \Ease\Html\LiTag(_('Marketing Consent: ').(ConsentHelper::hasMarketingConsent() ? '✅ Granted' : '❌ Denied')),
    new \Ease\Html\LiTag(_('Functional Consent: ').(ConsentHelper::hasFunctionalConsent() ? '✅ Granted' : '❌ Denied')),
    new \Ease\Html\LiTag(_('Personalization Consent: ').(ConsentHelper::hasPersonalizationConsent() ? '✅ Granted' : '❌ Denied')),
    new \Ease\Html\LiTag(_('Any Consent: ').(ConsentHelper::hasAnyConsent() ? '✅ Yes' : '❌ No')),
]);

$cardBody->addItem($helperTests);

// Test conditional content
$cardBody->addItem(new \Ease\Html\H5(_('Conditional Content Tests')));

$cardBody->addItem(new \Ease\Html\H6(_('Analytics Content')));
$analyticsContent = ConsentHelper::conditionalScript(
    '<div class="alert alert-success">🎯 Analytics tracking is enabled!</div>',
    ConsentManager::CONSENT_ANALYTICS,
);
$cardBody->addItem(new \Ease\Html\DivTag($analyticsContent ?: '<div class="alert alert-secondary">📊 Analytics tracking is disabled</div>'));

$cardBody->addItem(new \Ease\Html\H6(_('Marketing Content')));
$marketingContent = ConsentHelper::conditionalScript(
    '<div class="alert alert-success">📢 Marketing features are enabled!</div>',
    ConsentManager::CONSENT_MARKETING,
);
$cardBody->addItem(new \Ease\Html\DivTag($marketingContent ?: '<div class="alert alert-secondary">🚫 Marketing features are disabled</div>'));

// Add test iframe
$cardBody->addItem(new \Ease\Html\H6(_('Consent-Aware Iframe Test')));
$cardBody->addItem(new \Ease\Html\DivTag(
    ConsentHelper::renderConsentAwareIframe(
        'https://example.com',
        ConsentManager::CONSENT_MARKETING,
        ['width' => '100%', 'height' => '200', 'class' => 'border'],
    ),
));

$testCard->addItem($cardBody);
WebPage::singleton()->addItem($testCard);

// Controls section
$controlsCard = new \Ease\TWB4\Card();
$controlsCard->addCSSClass('mt-4');
$controlsCard->addItem(new \Ease\TWB4\CardHeader(new \Ease\Html\H4(_('Test Controls'))));

$controlsBody = new \Ease\TWB4\CardBody();

// Buttons for testing
$buttonRow = new \Ease\TWB4\Row();

$col1 = new \Ease\TWB4\Col(6);
$col1->addItem(new \Ease\Html\H6(_('Show Consent Banner')));
$col1->addItem(new \Ease\TWB4\Button(
    [new \Ease\Html\I(null, ['class' => 'fas fa-cookie-bite']), ' ', _('Show Banner')],
    'primary',
    ['onclick' => 'if(window.multiFxiConsent) window.multiFxiConsent.showConsentBanner();'],
));

$col2 = new \Ease\TWB4\Col(6);
$col2->addItem(new \Ease\Html\H6(_('Consent Preferences')));
$col2->addItem(ConsentHelper::renderConsentPreferencesButton());

$buttonRow->addItem($col1);
$buttonRow->addItem($col2);

$controlsBody->addItem($buttonRow);

// Simulate different consent scenarios buttons
$controlsBody->addItem(new \Ease\Html\H6(_('Quick Test Actions'), ['class' => 'mt-4']));

$quickTestRow = new \Ease\TWB4\Row();

$scenarios = [
    ['label' => _('Accept All'), 'action' => 'acceptAll', 'class' => 'success'],
    ['label' => _('Deny All'), 'action' => 'denyAll', 'class' => 'danger'],
    ['label' => _('Analytics Only'), 'action' => 'analyticsOnly', 'class' => 'info'],
    ['label' => _('Refresh Page'), 'action' => 'refresh', 'class' => 'secondary'],
];

// Add cookie debugging buttons
$controlsBody->addItem(new \Ease\Html\H6(_('Cookie Debugging'), ['class' => 'mt-4']));
$cookieDebugRow = new \Ease\TWB4\Row();

$debugActions = [
    ['label' => _('Debug Cookies'), 'action' => 'debugConsentCookies()', 'class' => 'info'],
    ['label' => _('Clear Cookie'), 'action' => 'clearConsentCookie()', 'class' => 'warning'],
    ['label' => _('Check Console'), 'action' => 'console.log("Check browser console for debug info")', 'class' => 'secondary'],
];

foreach ($debugActions as $action) {
    $col = new \Ease\TWB4\Col(4);
    $col->addItem(new \Ease\TWB4\Button(
        $action['label'],
        $action['class'],
        [
            'class' => 'btn-sm w-100 mb-2',
            'onclick' => $action['action'],
        ],
    ));
    $cookieDebugRow->addItem($col);
}

$controlsBody->addItem($cookieDebugRow);

foreach ($scenarios as $scenario) {
    $col = new \Ease\TWB4\Col(3);
    $col->addItem(new \Ease\TWB4\Button(
        $scenario['label'],
        $scenario['class'],
        [
            'class' => 'btn-sm w-100 mb-2',
            'onclick' => "testScenario('{$scenario['action']}')",
        ],
    ));
    $quickTestRow->addItem($col);
}

$controlsBody->addItem($quickTestRow);

$controlsCard->addItem($controlsBody);
WebPage::singleton()->addItem($controlsCard);

// JavaScript for test scenarios and cookie debugging
WebPage::singleton()->addJavaScript(<<<'EOD'

function testScenario(action) {
    switch(action) {
        case "acceptAll":
            if(window.multiFxiConsent) {
                window.multiFxiConsent.acceptAll();
            }
            break;
        case "denyAll":
            if(window.multiFxiConsent) {
                window.multiFxiConsent.declineAll();
            }
            break;
        case "analyticsOnly":
            // Simulate analytics-only consent via API
            fetch("consent-api.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                credentials: "same-origin",
                body: JSON.stringify({
                    action: "save_consent",
                    consent: {
                        essential: true,
                        functional: false,
                        analytics: true,
                        marketing: false,
                        personalization: false
                    },
                    version: "1.0"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                alert("Error setting consent");
            });
            break;
        case "refresh":
            location.reload();
            break;
    }
}

// Debug functions for testing cookie persistence
function debugConsentCookies() {
    console.log("=== Consent Cookie Debug ===");
    console.log("All cookies:", document.cookie);

    const consentCookie = getCookieValue("multiflexi_consent");
    if (consentCookie) {
        try {
            const data = JSON.parse(decodeURIComponent(consentCookie));
            console.log("Consent cookie data:", data);
        } catch (e) {
            console.error("Error parsing consent cookie:", e);
        }
    } else {
        console.log("No consent cookie found");
    }

    if (window.multiFxiConsent) {
        console.log("Current consent from banner:", window.multiFxiConsent.getConsentStatus());
    }
}

function getCookieValue(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === " ") c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function clearConsentCookie() {
    document.cookie = "multiflexi_consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    console.log("Consent cookie cleared");
    debugConsentCookies();
}

// Auto-debug on page load
setTimeout(function() {
    debugConsentCookies();
}, 1000);


EOD);

// Information section
$infoCard = new \Ease\TWB4\Card();
$infoCard->addCSSClass('mt-4');
$infoCard->addItem(new \Ease\TWB4\CardHeader(new \Ease\Html\H4(_('Implementation Information'))));

$infoBody = new \Ease\TWB4\CardBody();
$infoBody->addItem(new \Ease\Html\PTag(_('This test page demonstrates the GDPR consent management system implementation:')));

$infoList = new \Ease\Html\Ol([
    new \Ease\Html\LiTag(_('Database tables: consent and consent_log for storing consent data and audit trail')),
    new \Ease\Html\LiTag(_('ConsentManager class: Backend PHP class for managing consent operations')),
    new \Ease\Html\LiTag(_('JavaScript consent banner: User-friendly interface for consent collection')),
    new \Ease\Html\LiTag(_('Cookie persistence: Consent is stored in cookies for session persistence')),
    new \Ease\Html\LiTag(_('Consent API: AJAX endpoint for consent operations')),
    new \Ease\Html\LiTag(_('ConsentHelper: Utility functions for checking consent in templates')),
    new \Ease\Html\LiTag(_('Integration: Automatic integration into existing pages via init.php')),
]);

$infoBody->addItem($infoList);

$infoCard->addItem($infoBody);
WebPage::singleton()->addItem($infoCard);

// Add link to consent preferences
WebPage::singleton()->addItem(new \Ease\Html\DivTag(
    new \Ease\TWB4\LinkButton('consent-preferences.php', [
        new \Ease\Html\I(null, ['class' => 'fas fa-cog']),
        ' ',
        _('Manage Consent Preferences'),
    ], 'primary', ['class' => 'mt-3']),
));
