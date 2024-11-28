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

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

\Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');

foreach (\Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form') as $formAvailble) {
    $forms[$formAvailble] = '\\MultiFlexi\\Ui\\Form\\' . $formAvailble;
}


$apper = new \MultiFlexi\Application();
$reqs = [];
$apps = [];
foreach ($apper->listingQuery() as $appInfo) {
    $allApps[$appInfo['uuid']] = $appInfo;
    if ($appInfo['requirements']) {
        $appReqs = strchr($appInfo['requirements'], ',') ? explode(',', $appInfo['requirements']) : [$appInfo['requirements']];
        foreach ($appReqs as $requrement) {
            $reqs[$requrement][$appInfo['id']] = $appInfo['uuid'];
        }
    }
}

foreach ($reqs as $reqirement => $apps) {

    $head = new \Ease\TWB4\Row();
    $fields = [];

    $applications = [];
    
    foreach ($apps as $app){
        $applications[_($allApps[$app]['name'])] = new \Ease\Html\DivTag(new AppLinkButton(new \MultiFlexi\Application($allApps[$app])));
    }

    if (array_key_exists($reqirement, $forms)) {
        $head->addColumn(4, new \Ease\Html\ImgTag($forms[$reqirement]::$logo,$reqirement,['height'=>'40px']));
        $head->addColumn(4, new \Ease\Html\H2Tag($forms[$reqirement]::name()));
        
        foreach ($forms[$reqirement]::fields() as $name => $fieldsInfo) {
            $fields[] = new \Ease\TWB4\Badge('info', $name,['title'=>$fieldsInfo['type'],'style'=>'margin: 4px;']);
        }
        $reqPanel = new \Ease\TWB4\Panel($head,'success', $applications, $fields);
        
    } else {
        $head->addColumn(4, new \Ease\Html\ImgTag('images/cancel.svg',$reqirement,['height'=>'40px']));
        $head->addColumn(4, new \Ease\Html\H2Tag($reqirement));
        $reqPanel = new \Ease\TWB4\Panel($head,'warning', $applications, $fields);
    }
            
    
    WebPage::singleton()->container->addItem($reqPanel);
}


WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
