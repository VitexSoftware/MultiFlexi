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

$ruleId = WebPage::getRequestValue('id', 'int');

$eventRule = new \MultiFlexi\EventRule($ruleId, ['autoload' => true]);

$delete = WebPage::getRequestValue('delete', 'int');

if (null !== $delete) {
    $eventRule->loadFromSQL($delete);

    if ($eventRule->deleteFromSQL($delete)) {
        $eventRule->addStatusMessage(_('Event Rule removed'), 'success');
    } else {
        $eventRule->addStatusMessage(_('Error removing Event Rule'), 'error');
    }

    WebPage::singleton()->redirect('eventrules.php');
}

if (WebPage::singleton()->isPosted()) {
    $eventRule->takeData($_POST);

    // Validate env_mapping JSON
    $envMapping = $eventRule->getDataValue('env_mapping');

    if (!empty($envMapping) && null === json_decode($envMapping, true)) {
        $eventRule->addStatusMessage(_('Invalid JSON in Environment Variable Mapping'), 'error');
    } else {
        if (null !== $eventRule->dbsync()) {
            $eventRule->addStatusMessage(_('Event Rule saved'), 'success');
        } else {
            $eventRule->addStatusMessage(_('Error saving Event Rule'), 'error');
        }
    }
}

WebPage::singleton()->addItem(new PageTop(_('Event Rule')));

WebPage::singleton()->container->addItem(new EventRuleForm($eventRule));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
