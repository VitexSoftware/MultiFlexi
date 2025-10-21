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

// Check if user is logged in
$user = \Ease\Shared::user();

if (!$user || !$user->getUserID()) {
    header('Location: login.php?redirect='.urlencode($_SERVER['REQUEST_URI']));

    exit;
}

WebPage::singleton()->addItem(new PageTop(_('Personal Data Export')));

// Add the main export widget
WebPage::singleton()->container->addItem(new DataExportWidget(['class' => 'mb-4']));

// Add additional GDPR information
$gdprInfoCard = new \Ease\TWB4\Card();
$gdprInfoCard->setCardHeader([
    new \Ease\TWB4\Widgets\FaIcon('fas fa-shield-alt', ['class' => 'me-2']),
    _('Your Rights Under GDPR'),
]);

$gdprBody = new \Ease\Html\DivTag();

$rights = [
    _('Right of Access (Article 15)') => _('You have the right to obtain confirmation as to whether personal data concerning you is being processed, and access to such data.'),
    _('Right to Rectification (Article 16)') => _('You have the right to obtain rectification of inaccurate personal data and to have incomplete personal data completed.'),
    _('Right to Erasure (Article 17)') => _('You have the right to obtain erasure of personal data concerning you under certain circumstances.'),
    _('Right to Data Portability (Article 20)') => _('You have the right to receive your personal data in a structured, commonly used and machine-readable format.'),
];

$rightsList = new \Ease\Html\UlTag();
$rightsList->addTagClass('list-unstyled');

foreach ($rights as $title => $description) {
    $listItem = new \Ease\Html\LiTag();
    $listItem->addTagClass('mb-3');

    $titleDiv = new \Ease\Html\DivTag($title);
    $titleDiv->addTagClass('fw-bold text-primary mb-1');

    $descDiv = new \Ease\Html\DivTag($description);
    $descDiv->addTagClass('text-muted');

    $listItem->addItem($titleDiv);
    $listItem->addItem($descDiv);
    $rightsList->addItem($listItem);
}

$gdprBody->addItem($rightsList);

// Contact information
$contactInfo = new \Ease\Html\DivTag();
$contactInfo->addTagClass('alert alert-light');
$contactIcon = new \Ease\TWB4\Widgets\FaIcon('fas fa-envelope', ['class' => 'me-2']);
$contactInfo->addItem($contactIcon);
$contactInfo->addItem(_('For questions about your personal data or to exercise other GDPR rights, please contact our Data Protection Officer.'));

$gdprBody->addItem($contactInfo);

$gdprInfoCard->setCardBody($gdprBody);
WebPage::singleton()->container->addItem($gdprInfoCard);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
