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

WebPage::singleton()->addItem(new PageTop(_('Privacy Policy')));

$container = WebPage::singleton()->container;

// Introduction
$container->addItem('<h1>'._('Privacy Policy').'</h1>');
$container->addItem('<p><strong>'._('Last updated: ').date('Y-m-d').'</strong></p>');

$container->addItem('<p>'._('This Privacy Policy describes how MultiFlexi ("we", "our", or "us") collects, uses, and protects your information when you use our service.').'</p>');

// Data Controller Section
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('1. Data Controller').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <p>
EOD._('MultiFlexi is developed and maintained by Vitex Software. For privacy-related inquiries, please contact:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('Company: Vitex Software').<<<'EOD'
</li>
            <li>
EOD._('Website: ').<<<'EOD'
<a href="https://vitexsoftware.com">https://vitexsoftware.com</a></li>
            <li>
EOD._('Email: info@vitexsoftware.cz').<<<'EOD'
</li>
        </ul>
    </div>
</div>

EOD);

// Information We Collect
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('2. Information We Collect').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <h3>
EOD._('2.1 Personal Information').<<<'EOD'
</h3>
        <p>
EOD._('We may collect the following personal information:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('Account information: username, email address, and encrypted passwords').<<<'EOD'
</li>
            <li>
EOD._('Profile information: first name, last name, and user preferences').<<<'EOD'
</li>
            <li>
EOD._('System logs: IP addresses, browser information, and access timestamps').<<<'EOD'
</li>
        </ul>

        <h3>
EOD._('2.2 Technical Information').<<<'EOD'
</h3>
        <p>
EOD._('We automatically collect certain technical information:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('Device information: browser type, operating system, and device identifiers').<<<'EOD'
</li>
            <li>
EOD._('Usage data: pages visited, features used, and interaction patterns').<<<'EOD'
</li>
            <li>
EOD._('Performance data: system performance metrics and error logs').<<<'EOD'
</li>
        </ul>

        <h3>
EOD._('2.3 Application Data').<<<'EOD'
</h3>
        <p>
EOD._('As a task automation platform, we process:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('Company information and configurations').<<<'EOD'
</li>
            <li>
EOD._('Application settings and credentials (encrypted)').<<<'EOD'
</li>
            <li>
EOD._('Job execution logs and results').<<<'EOD'
</li>
            <li>
EOD._('Scheduled task configurations').<<<'EOD'
</li>
        </ul>
    </div>
</div>

EOD);

// Your Rights
$container->addItem(<<<'EOD'

<div class="card mt-4">
    <div class="card-header">
        <h2>
EOD._('3. Your Rights').<<<'EOD'
</h2>
    </div>
    <div class="card-body">
        <p>
EOD._('Under GDPR and applicable data protection laws, you have the following rights:').<<<'EOD'
</p>
        <ul>
            <li>
EOD._('Right of access: Request copies of your personal data').<<<'EOD'
</li>
            <li>
EOD._('Right to rectification: Correct inaccurate personal data').<<<'EOD'
</li>
            <li>
EOD._('Right to erasure: Request deletion of your personal data').<<<'EOD'
</li>
            <li>
EOD._('Right to restrict processing: Limit how we use your data').<<<'EOD'
</li>
            <li>
EOD._('Right to data portability: Receive your data in a structured format').<<<'EOD'
</li>
            <li>
EOD._('Right to object: Object to processing based on legitimate interest').<<<'EOD'
</li>
            <li>
EOD._('Right to withdraw consent: Withdraw consent for optional processing').<<<'EOD'
</li>
        </ul>
        <p>
EOD._('To exercise your rights, please use our ').'<a href="consent-preferences.php">'._('Privacy Preferences').'</a>'._(' page or contact us directly.').<<<'EOD'
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
EOD._('If you have any questions about this Privacy Policy or our data practices, please contact us:').<<<'EOD'
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
EOD._('Quick Actions').<<<'EOD'
</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <a href="consent-preferences.php" class="btn btn-primary btn-block">
                    <i class="fas fa-cog"></i>
EOD._('Privacy Preferences').<<<'EOD'

                </a>
            </div>
            <div class="col-md-4">
                <a href="cookie-policy.php" class="btn btn-info btn-block" id="viewcookiepolicybutton">
                    <i class="fas fa-cookie-bite"></i>
EOD._('Cookie Policy').<<<'EOD'

                </a>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-secondary btn-block" onclick="if(window.multiFxiConsent) window.multiFxiConsent.showConsentBanner();">
                    <i class="fas fa-shield-alt"></i>
EOD._('Show Consent Banner').<<<'EOD'

                </button>
            </div>
        </div>
    </div>
</div>

EOD);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
