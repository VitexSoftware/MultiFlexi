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

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use MultiFlexi\Application;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Applications')));

$apps = new Application();

$allAppData = $apps->getAll();

$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('ID'), _('Enabled'), _('Image'), _('Name'), _('Description'), _('Executable'), _('Created'), _('Modified'), _('HomePage'), _('Requirements'), _('Container Image'), _('Version'), _('Code'), _('uuid')]);

foreach ($allAppData as $appData) {
    $appData['image'] = new \Ease\Html\ImgTag('appimage.php?uuid='.$appData['uuid'], _('Icon'), ['height' => 40]);
    $appData['enabled'] = ($appData['enabled'] === 1 ? '✔' : '❌');
    $executablePath = Application::findBinaryInPath($appData['executable']);
    $appData['executable'] = empty($executablePath) ? '<span title="'._('Command not found').'">⁉</span> '.$appData['executable'] : $executablePath;

    if (empty($appData['setup']) === false) {
        $initPath = Application::findBinaryInPath($appData['setup']);
        $appData['setup'] = (empty($initPath) ? '<span title="'._('Command not found').'">⁉</span> '.$appData['setup'] : $initPath);
    }

    $appData['homepage'] = new \Ease\Html\ATag($appData['homepage'], $appData['homepage']);
    $appData['name'] = new \Ease\Html\ATag('app.php?id='.$appData['id'], _($appData['name']));
    $appData['description'] = _($appData['description']);
    unset($appData['setup'], $appData['cmdparams'], $appData['deploy']);

    $fbtable->addRowColumns($appData);
}

$oPage->container->addItem(new Panel(_('Applications'), 'default', $fbtable, new LinkButton('app.php', _('Register new'))));

$oPage->addItem(new PageBottom());

$oPage->draw();
