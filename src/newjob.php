<?php

/**
 * Multi Flexi - AdHoc Job Run.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use \AbraFlexi\MultiFlexi\Application,
    \Ease\TWB4\Row;


require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Schedule Job run')));

$companyId = $oPage->getRequestValue('company_id','int');
$appId = $oPage->getRequestValue('app_id','int');

$runTemplate = new \AbraFlexi\MultiFlexi\RunTemplate();
if ($companyId && $appId) {
    $runTemplateId = $runTemplate->runTemplateID($appId, $companyId);
    if ($runTemplateId == 0) {
        $runTemplate->dbsync(['app_id' => $appId, 'company_id' => $companyId, 'interv' => 'n']);
    } else {
        $runTemplate->setMyKey($runTemplateId);
    }
}

$jobber = new \AbraFlexi\MultiFlexi\Job();

$uploadEnv = [];
/**
 * Save all uploaded files into temporary directory and prepare job environment
 */
if (!empty($_FILES)) {
  foreach ($_FILES as $field => $file) {
    if ($file['error'] == 0) {
      $tmpName = tempnam(sys_get_temp_dir(), 'multiflexi_').'_'.basename($file['name']);
      if (move_uploaded_file($file['tmp_name'], $tmpName)) {
        $uploadEnv[$field] = $tmpName;
      }
    }
  }
}

$jobber->prepareJob($runTemplate->getMyKey(), $uploadEnv);
$jobber->scheduleJobRun(new \DateTime);

$appInfo = $runTemplate->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$instanceRow = new Row();
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(empty($appInfo['image']) ? 'images/apps.svg' : $appInfo['image'], 'Logo', ['class' => 'img-fluid', 'style' => 'height: 64px']));
$instanceRow->addColumn(8, new \Ease\Html\H1Tag($instanceName));

$envTable = new \AbraFlexi\MultiFlexi\Ui\EnvironmentView($runTemplate->getAppEnvironment());

$oPage->container->addItem($envTable);

$oPage->container->addItem(new \Ease\TWB4\LinkButton('job.php?id='.$jobber->getMyKey(), _('Job details'),'info btn-block'));

$oPage->draw();