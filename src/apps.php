<?php

/**
 * Multi Flexi - Index of Applications.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use MultiFlexi\Application;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Applications')));

$servers = new Application();

$allAppData = $servers->getAll();

$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('ID'), _('Enabled'), _('Image'), _('Name'), _('Description'), _('Executable'), _('Created'), _('Modified'), _('Init Command'),_('Deploy Command'),_('HomePage')]);

foreach ($allAppData as $appData) {
    $appData['image'] = new \Ease\Html\ImgTag($appData['image'], _('Icon'), ['height' => 40]);
    $appData['enabled'] = ($appData['enabled'] == 1 ? '✔' : '❌');
    $executablePath = Application::findBinaryInPath($appData['executable']);
    $appData['executable'] = empty($executablePath) ? '<span title="' . _('Command not found') . '">⁉</span> ' . $appData['executable'] : $executablePath;

    if (empty($appData['setup']) === false) {
        $initPath = Application::findBinaryInPath($appData['setup']);
        $appData['setup'] = (empty($initPath) ? '<span title="' . _('Command not found') . '">⁉</span> ' . $appData['setup'] : $initPath);
    }

    $appData['name'] = new \Ease\Html\ATag('app.php?id=' . $appData['id'], $appData['name']);
    $fbtable->addRowColumns($appData);
}

$oPage->container->addItem(new Panel(_('Applications'), 'default', $fbtable, new LinkButton('app.php', _('Register new'))));

$oPage->addItem(new PageBottom());

$oPage->draw();
