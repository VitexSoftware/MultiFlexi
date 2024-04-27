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

$jobsDataRaw = (new \MultiFlexi\Job())->listingQuery()->select(['app_id','exitcode'])->fetchAll();
foreach ($jobsDataRaw as $jobData) {
    $exitcodeCounts = [];
    $exitcodeSuccess = [];

    foreach ($jobsData as $jobData) {
        $app_id = $jobData['app_id'];
        $exitcode = $jobData['exitcode'];

        if (!isset($exitcodeCounts[$app_id])) {
            $exitcodeCounts[$app_id] = [];
            $exitcodeSuccess[$app_id] = [];
        }

        if (!isset($exitcodeCounts[$app_id][$exitcode])) {
            $exitcodeCounts[$app_id][$exitcode] = 0;
            $exitcodeSuccess[$app_id][$exitcode] = 0;
        }

        $exitcodeCounts[$app_id][$exitcode]++;
        if ($exitcode == 0) {
            $exitcodeSuccess[$app_id][$exitcode]++;
        }
    }

    $averageSuccess = [];

    foreach ($exitcodeCounts as $app_id => $exitcodes) {
        $averageSuccess[$app_id] = [];

        foreach ($exitcodes as $exitcode => $count) {
            $averageSuccess[$app_id][$exitcode] = $exitcodeSuccess[$app_id][$exitcode] / $count;
        }
    }
    $jobsData[$jobData['app_id']] = $jobData['exitcode'];
}


$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('Success'),_('ID'), _('Enabled'), _('Image'), _('Name'), _('Description'), _('Executable'), _('Created'), _('Modified'), _('HomePage'), _('Requirements'), _('Container Image'), _('Version'), _('Code'), _('uuid')]);

foreach ($allAppData as $appData) {
    $appData['success'] = $averageSuccess[$appData['id']][0] ?? 0;
    $appData['image'] = new \Ease\Html\ImgTag($appData['image'], _('Icon'), ['height' => 40]);
    $appData['enabled'] = ($appData['enabled'] == 1 ? '✔' : '❌');
    $executablePath = Application::findBinaryInPath($appData['executable']);
    $appData['executable'] = empty($executablePath) ? '<span title="' . _('Command not found') . '">⁉</span> ' . $appData['executable'] : $executablePath;

    if (empty($appData['setup']) === false) {
        $initPath = Application::findBinaryInPath($appData['setup']);
        $appData['setup'] = (empty($initPath) ? '<span title="' . _('Command not found') . '">⁉</span> ' . $appData['setup'] : $initPath);
    }

    $appData['homepage'] = new \Ease\Html\ATag($appData['homepage'], $appData['homepage']);
    $appData['name'] = new \Ease\Html\ATag('app.php?id=' . $appData['id'], _($appData['name']));
    $appData['description'] = _($appData['description']);
    unset($appData['setup']);
    unset($appData['cmdparams']);
    unset($appData['deploy']);
    $fbtable->addRowColumns($appData);
}

$oPage->container->addItem(new Panel(_('Applications'), 'default', $fbtable, new LinkButton('app.php', _('Register new'))));

$oPage->addItem(new PageBottom());

$oPage->draw();
