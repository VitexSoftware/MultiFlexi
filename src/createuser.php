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

require_once './init.php';

use MultiFlexi\Ui\WebPage;

WebPage::singleton()->onlyForLogged();
WebPage::singleton()->addItem(new \Ease\TWB4\PageTop(_('Create New User Account')));
WebPage::singleton()->container->addItem(new \Ease\TWB4\Panel(_('User Account Creation'), 'info', new \MultiFlexi\Ui\UserForm()));
WebPage::singleton()->addItem(new \Ease\TWB4\PageBottom());
WebPage::singleton()->draw();

// Example usage in any PHP file
$span = $tracer->spanBuilder('user_creation')->startSpan();
try {
    // ... your code here ...
} finally {
    $span->end();
}
