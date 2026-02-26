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

WebPage::singleton()->onlyForLogged();

$sourceId = WebPage::getRequestValue('id', 'int');

$eventSource = new \MultiFlexi\EventSource($sourceId, ['autoload' => true]);

$delete = WebPage::getRequestValue('delete', 'int');

if (null !== $delete) {
    $eventSource->loadFromSQL($delete);

    if ($eventSource->deleteFromSQL($delete)) {
        $eventSource->addStatusMessage(_('Event Source removed'), 'success');
    } else {
        $eventSource->addStatusMessage(_('Error removing Event Source'), 'error');
    }

    WebPage::singleton()->redirect('eventsources.php');
}

if (WebPage::singleton()->isPosted()) {
    $eventSource->takeData($_POST);

    if (null !== $eventSource->dbsync()) {
        $eventSource->addStatusMessage(_('Event Source saved'), 'success');
    } else {
        $eventSource->addStatusMessage(_('Error saving Event Source'), 'error');
    }
}

WebPage::singleton()->addItem(new PageTop(_('Event Source')));

if (WebPage::getRequestValue('test') && $sourceId) {
    if ($eventSource->isReachable()) {
        $eventSource->addStatusMessage(_('Connection to adapter database successful'), 'success');
    } else {
        $eventSource->addStatusMessage(_('Connection to adapter database failed'), 'error');
    }
}

WebPage::singleton()->container->addItem(new EventSourceForm($eventSource));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
