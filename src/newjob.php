<?php

/**
 * Multi Flexi - AdHoc Job Run.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;
use AbraFlexi\MultiFlexi\Applications;


require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Schedule Job run')));

$companyId = $oPage->getRequestValue('company_id','int');
$appId = $oPage->getRequestValue('app_id','int');

$appCompany = new \AbraFlexi\MultiFlexi\AppToCompany();
if ($companyId && $appId) {
    if ($appCompany->appCompanyID($appId, $companyId) == 0) {
        $appCompany->dbsync(['app_id' => $appId, 'company_id' => $companyId, 'interv' => 'n']);
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

$jobber->prepareJob($appCompany->getMyKey(), $uploadEnv);


$appInfo = $appCompany->getAppInfo();
$apps = new Applications($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$instanceRow = new Row();
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(empty($appInfo['image']) ? 'images/apps.svg' : $appInfo['image'], 'Logo', ['class' => 'img-fluid', 'style' => 'height: 64px']));
$instanceRow->addColumn(8, new \Ease\Html\H1Tag($instanceName));

$envTable = new EnvironmentView($appCompany->getAppEnvironment());

$oPage->draw();
