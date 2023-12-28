<?php

/**
 * Multi Flexi - AdHoc Job Run.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Application;
use Ease\TWB4\Row;

require_once './init.php';

$oPage->onlyForLogged();

$companyId = $oPage->getRequestValue('company_id', 'int');
$appId = $oPage->getRequestValue('app_id', 'int');

$runTemplate = new \MultiFlexi\RunTemplate();

$runTemplateId = $runTemplate->runTemplateID($appId, $companyId);
$runTemplate->setMyKey($runTemplateId);

$jobber = new \MultiFlexi\Job();

$uploadEnv = [];
/**
 * Save all uploaded files into temporary directory and prepare job environment
 */
if (!empty($_FILES)) {
    foreach ($_FILES as $field => $file) {
        if ($file['error'] == 0) {
            $tmpName = tempnam(sys_get_temp_dir(), 'multiflexi_') . '_' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $tmpName)) {
                $uploadEnv[$field]['type'] = 'file';
                $uploadEnv[$field]['source'] = _('Launch Form');
                $uploadEnv[$field]['value'] = $tmpName;
            }
        }
    }
}

$confField = new \MultiFlexi\Conffield();
$envars = $confField->appConfigs($appId);

foreach ($envars as $envVarName => $envVarInfo) {
    $override = WebPage::getRequestValue($envVarName);
    if (is_null($override) === false) {
        $uploadEnv[$envVarName]['source'] = _('Launch Form');
        $uploadEnv[$envVarName]['value'] = $override;
    }
}


$jobber->prepareJob($runTemplate->getMyKey(), $uploadEnv, 'adhoc ' . (new \DateTime())->format('Y-m-d h:i:s'), WebPage::getRequestValue('executor'));
$jobber->scheduleJobRun(new \DateTime());

$appInfo = $runTemplate->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$oPage->addItem(new PageTop(_('Schedule Job run')));

$instanceRow = new Row();
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(empty($appInfo['image']) ? 'images/apps.svg' : $appInfo['image'], 'Logo', ['class' => 'img-fluid', 'style' => 'height: 64px']));
$instanceRow->addColumn(8, new \Ease\Html\H1Tag($instanceName));

$oPage->container->addItem($instanceRow);

$envTable = new \MultiFlexi\Ui\EnvironmentView($jobber->getEnv());

$oPage->container->addItem($envTable);

$oPage->container->addItem(new \Ease\TWB4\LinkButton('job.php?id=' . $jobber->getMyKey(), _('Job details'), 'info btn-block'));

$oPage->draw();
