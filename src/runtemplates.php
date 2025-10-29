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

WebPage::singleton()->addItem(new PageTop(_('Runtemplates')));

$buttonRow = new \Ease\TWB4\Row();
$buttonRow->addColumn(12, [
    new \Ease\TWB4\LinkButton('activation-wizard.php', '🧙 '._('Activation Wizard'), 'success btn-lg'),
    '&nbsp;',
    new \Ease\Html\SmallTag(_('Use the wizard to easily activate an application in a company')),
]);
WebPage::singleton()->container->addItem($buttonRow);
WebPage::singleton()->container->addItem(new \Ease\Html\HrTag());

WebPage::singleton()->container->addItem(new DBDataTable(new \MultiFlexi\RunTemplateLister()));

WebPage::singleton()->addItem(new PageBottom('runtemplates'));

WebPage::singleton()->draw();
