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

use Ease\WebPage;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\RunTemplate;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));

if (WebPage::getRequestValue('new', 'int') === 1) {
    $app = new Application(WebPage::getRequestValue('app_id', 'int'));
    $runTemplate->setDataValue('app_id', WebPage::getRequestValue('app_id', 'int'));
    $runTemplate->setDataValue('company_id', WebPage::getRequestValue('company_id', 'int'));
    $runTemplate->setDataValue('interv', 'n');
    $runTemplate->setDataValue('name', _($app->getRecordName()));
    $runTemplate->dbsync();
}

if (WebPage::getRequestValue('delete', 'int') === 2) {
    $runTemplate->deleteFromSQL();
    WebPage::singleton()->redirect('companyapp.php?company_id='.$runTemplate->getDataValue('company_id').'&app_id='.$runTemplate->getDataValue('app_id'));
}

$companies = new Company($runTemplate->getDataValue('company_id'));
$app = new Application($runTemplate->getDataValue('app_id'));

$app->setDataValue('company_id', $companies->getMyKey());
$app->setDataValue('app_id', $app->getMyKey());
$app->setDataValue('app_name', $app->getRecordName());

$configurator = new \MultiFlexi\Configuration([
    'runtemplate_id' => $runTemplate->getMyKey(),
    'app_id' => $app->getMyKey(),
    'company_id' => $companies->getMyKey(),
], ['autoload' => false]);

if (WebPage::singleton()->isPosted()) {
    $dataToSave = $_POST;

    if (\array_key_exists('credential', $dataToSave)) {
        if ($dataToSave['credential']) {
            $rtplcrd = new \MultiFlexi\RunTplCreds();
            foreach ($dataToSave['credential'] as $reqType => $reqId) {
                if($reqId){
                    $rtplcrd->bind($runTemplate->getMyKey(), intval($reqId));
                } else {

                    $rtplcrd->unbindAll($runTemplate->getMyKey(), $reqType);
                }
            }
        }

        unset($dataToSave['credential']);
    }

    $app->checkRequiredFields($dataToSave, true);

    if ($configurator->takeData($dataToSave) && null !== $configurator->saveToSQL()) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
    } else {
        $configurator->addStatusMessage(_('Error saving Config fields'), 'error');
    }
}

WebPage::singleton()->addItem(new PageTop($runTemplate->getRecordName().' '._('Configuration')));

$appPanel = new ApplicationPanel($app, new RunTemplatePanel($runTemplate));

WebPage::singleton()->container->addItem(new CompanyPanel($companies, $appPanel));

WebPage::singleton()->addItem(new PageBottom('runtemplate/'.$runTemplate->getMyKey()));
WebPage::singleton()->draw();
