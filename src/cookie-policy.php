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

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;
use MultiFlexi\Ui\WebPage;

require_once 'init.php';

WebPage::singleton()->addItem(new PageTop(_('Cookie Policy')));

$container = WebPage::singleton()->container;

// Introduction
$container->addItem('<h1>'._('Cookie Policy').'</h1>');
$container->addItem('<p><strong>'._('Last updated: ').date('Y-m-d').'</strong></p>');

$container->addItem('<p>'._('This Cookie Policy explains how MultiFlexi uses cookies and similar tracking technologies when you visit our application.').'</p>');

// What are Cookies
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('1. What Are Cookies?').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <p>
EOD._('Cookies are small data files that are placed on your computer or mobile device when you visit a website or application. Cookies are widely used by website and application owners to make their services work more efficiently and to provide reporting information.').<<<'EOD'
</p>
        <p>
EOD._('Cookies set by the website owner (in this case, MultiFlexi) are called "first-party cookies." Cookies set by parties other than the website owner are called "third-party cookies."').<<<'EOD'
</p>
    </div>
</div>

EOD);

// Types of Cookies
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('2. Types of Cookies We Use').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <h3>
EOD._('2.1 Essential Cookies').<<<'EOD'
</h3>
        <p>
EOD._('These cookies are strictly necessary to provide you with services available through our application and to use some of its features. Because these cookies are essential for the application to function, you cannot refuse them without impacting how our services work.').<<<'EOD'
</p>

        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>
EOD._('Cookie Name').<<<'EOD'
</th>
                    <th>
EOD._('Purpose').<<<'EOD'
</th>
                    <th>
EOD._('Duration').<<<'EOD'
</th>
                    <th>
EOD._('Type').<<<'EOD'
</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHPSESSID</td>
                    <td>
EOD._('Session management and user authentication').<<<'EOD'
</td>
                    <td>
EOD._('Session').<<<'EOD'
</td>
                    <td>
EOD._('First-party').<<<'EOD'
</td>
                </tr>
                <tr>
                    <td>multiflexi_consent</td>
                    <td>
EOD._('Stores your consent preferences').<<<'EOD'
</td>
                    <td>
EOD._('1 year').<<<'EOD'
</td>
                    <td>
EOD._('First-party').<<<'EOD'
</td>
                </tr>
                <tr>
                    <td>multiflexi_lang</td>
                    <td>
EOD._('Language preference').<<<'EOD'
</td>
                    <td>
EOD._('1 year').<<<'EOD'
</td>
                    <td>
EOD._('First-party').<<<'EOD'
</td>
                </tr>
            </tbody>
        </table>

        <h3 class="mt-4">
EOD._('2.2 Analytics Cookies').<<<'EOD'
</h3>
        <p>
EOD._('These cookies help us understand how visitors interact with our application by collecting and reporting information anonymously. They help us improve our application and your experience.').<<<'EOD'
</p>

        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>
EOD._('Cookie Name').<<<'EOD'
</th>
                    <th>
EOD._('Purpose').<<<'EOD'
</th>
                    <th>
EOD._('Duration').<<<'EOD'
</th>
                    <th>
EOD._('Type').<<<'EOD'
</th>
                </tr>
            </thead>
            <tbody>
EOD.
                (filter_var(\Ease\Shared::cfg('ENABLE_GOOGLE_ANALYTICS', 'false'), \FILTER_VALIDATE_BOOLEAN) ? <<<'EOD'

                <tr>
                    <td>_ga</td>
                    <td>
EOD._('Google Analytics - distinguishes unique users').<<<'EOD'
</td>
                    <td>
EOD._('2 years').<<<'EOD'
</td>
                    <td>
EOD._('Third-party').<<<'EOD'
</td>
                </tr>
                <tr>
                    <td>_ga_*</td>
                    <td>
EOD._('Google Analytics - maintains session state').<<<'EOD'
</td>
                    <td>
EOD._('2 years').<<<'EOD'
</td>
                    <td>
EOD._('Third-party').<<<'EOD'
</td>
                </tr>
                <tr>
                    <td>_gid</td>
                    <td>
EOD._('Google Analytics - distinguishes unique users').<<<'EOD'
</td>
                    <td>
EOD._('24 hours').<<<'EOD'
</td>
                    <td>
EOD._('Third-party').<<<'EOD'
</td>
                </tr>
EOD : <<<'EOD'

                <tr>
                    <td colspan="4" class="text-center text-muted">
                        <em>
EOD._('Google Analytics is disabled for this installation. Analytics cookies are not used.').<<<'EOD'
</em><br>
                        <small>
EOD._('This installation may use alternative analytics solutions like Matomo or AWStats for statistical analysis.').<<<'EOD'
</small>
                    </td>
                </tr>
EOD).<<<'EOD'

            </tbody>
        </table>
    </div>
</div>

EOD);

// Managing Cookies
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('3. How to Control Cookies').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <h3>
EOD._('3.1 MultiFlexi Cookie Preferences').<<<'EOD'
</h3>
        <p>
EOD._('You can control most cookies through our ').'<a href="consent-preferences.php">'._('Privacy Preferences').'</a>'._(' page. This allows you to accept or reject different categories of cookies.').<<<'EOD'
</p>

        <h3>
EOD._('3.2 Browser Settings').<<<'EOD'
</h3>
        <p>
EOD._('Most web browsers allow you to control cookies through their settings. You can:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('View what cookies are stored on your device').<<<'EOD'
</li>
            <li>
EOD._('Delete cookies individually or all at once').<<<'EOD'
</li>
            <li>
EOD._('Block cookies from specific sites').<<<'EOD'
</li>
            <li>
EOD._('Block all cookies (this may affect website functionality)').<<<'EOD'
</li>
        </ul>

        <h3>
EOD._('3.3 Do Not Track Signals').<<<'EOD'
</h3>
        <p>
EOD._('Some browsers include a "Do Not Track" feature that lets you tell websites that you do not want to have your online activities tracked. MultiFlexi respects Do Not Track signals and will not set non-essential cookies when this signal is detected.').<<<'EOD'
</p>
    </div>
</div>

EOD);

// Contact Information
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('4. Contact Us').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <p>
EOD._('If you have any questions about this Cookie Policy or our use of cookies, please contact us:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('Email: info@vitexsoftware.cz').<<<'EOD'
</li>
            <li>
EOD._('Website: ').<<<'EOD'
<a href="https://vitexsoftware.com">https://vitexsoftware.com</a></li>
            <li>
EOD._('Project: ').<<<'EOD'
<a href="https://multiflexi.eu">https://multiflexi.eu</a></li>
        </ul>
    </div>
</div>

EOD);

// Quick Actions
$container->addItem(<<<'EOD'

<div class="card mt-4 bg-light">
    <div class="card-header">
        <h3>
EOD._('Manage Your Cookie Preferences').<<<'EOD'
</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <a href="consent-preferences.php" class="btn btn-primary btn-block">
                    <i class="fas fa-cog"></i>
EOD._('Cookie Preferences').<<<'EOD'

                </a>
            </div>
            <div class="col-md-4">
                <a href="privacy-policy.php" class="btn btn-info btn-block">
                    <i class="fas fa-shield-alt"></i>
EOD._('Privacy Policy').<<<'EOD'

                </a>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-secondary btn-block" onclick="if(window.multiFxiConsent) window.multiFxiConsent.showConsentBanner();">
                    <i class="fas fa-cookie-bite"></i>
EOD._('Show Consent Banner').<<<'EOD'

                </button>
            </div>
        </div>
    </div>
</div>

EOD);

// Current Cookie Status section with JavaScript
$container->addItem(<<<'EOD'

<div class="card mt-4 border-info">
    <div class="card-header">
        <h4><i class="fas fa-info-circle"></i>
EOD._('Your Current Cookie Settings').<<<'EOD'
</h4>
    </div>
    <div class="card-body">
        <p>
EOD._('The table below shows your current cookie preferences. You can change these at any time.').<<<'EOD'
</p>
        <div id="current-consent-status"></div>
    </div>
</div>

EOD);

// JavaScript to display current consent status
WebPage::singleton()->addJavaScript(<<<'EOD'

document.addEventListener("DOMContentLoaded", function() {
    var statusDiv = document.getElementById("current-consent-status");
    if (statusDiv && window.consentStatus) {
        var table = document.createElement("table");
        table.className = "table table-sm table-striped";

        var header = table.createTHead();
        var headerRow = header.insertRow();
        headerRow.insertCell().textContent = "
EOD._('Cookie Type').<<<'EOD'
";
        headerRow.insertCell().textContent = "
EOD._('Status').<<<'EOD'
";
        headerRow.insertCell().textContent = "
EOD._('Last Updated').<<<'EOD'
";

        var tbody = table.createTBody();

        var consentTypes = {
            "essential": "
EOD._('Essential').<<<'EOD'
",
            "functional": "
EOD._('Functional').<<<'EOD'
",
            "analytics": "
EOD._('Analytics').<<<'EOD'
",
            "marketing": "
EOD._('Marketing').<<<'EOD'
",
            "personalization": "
EOD._('Personalization').<<<'EOD'
"
        };

        Object.keys(consentTypes).forEach(function(type) {
            var row = tbody.insertRow();
            row.insertCell().textContent = consentTypes[type];

            var statusCell = row.insertCell();
            if (window.consentStatus[type]) {
                var status = window.consentStatus[type].status;
                var badgeClass = status ? "badge badge-success" : "badge badge-danger";
                var badgeText = status ? "
EOD._('Enabled').'" : "'._('Disabled').<<<'EOD'
";
                statusCell.innerHTML = "<span class=\"" + badgeClass + "\">" + badgeText + "</span>";

                var dateCell = row.insertCell();
                if (window.consentStatus[type].granted_at) {
                    dateCell.textContent = new Date(window.consentStatus[type].granted_at).toLocaleDateString();
                }
            } else {
                statusCell.innerHTML = "<span class=\"badge badge-secondary\">
EOD._('Not Set').<<<'EOD'
</span>";
                row.insertCell().textContent = "-";
            }
        });

        statusDiv.appendChild(table);
    } else {
        statusDiv.innerHTML = "<div class=\"alert alert-info\">
EOD._('No cookie preferences have been set yet.').<<<'EOD'
</div>";
    }
});

EOD);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
