<?php

/**
 * Multi Flexi - About page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();
$app = new \AbraFlexi\MultiFlexi\Application(WebPage::getRequestValue('app_id', 'int'));
$company = new \AbraFlexi\MultiFlexi\Company(WebPage::getRequestValue('company_id', 'int'));
$oPage->addItem(new PageTop(_('About')));
$oPage->container->addItem(new ApplicationInfo($app, $company));
if (is_null($app->getMyKey())) {
    $oPage->container->addItem(new \Ease\TWB4\Alert('error', _('app_id not specified')));
    $app->addStatusMessage(_('app_id not specified'), 'error');
} else {

    if (WebPage::isPosted() && WebPage::getRequestValue('when')) {
        $jobber = new \AbraFlexi\MultiFlexi\Job();
        $uploadEnv = [];
        /**
         * Save all uploaded files into temporary directory and prepare job environment
         */
        if (!empty($_FILES)) {
            foreach ($_FILES as $field => $file) {
                if ($file['error'] == 0) {
                    $tmpName = tempnam(sys_get_temp_dir(), 'multiflexi_') . '_' . basename($file['name']);
                    if (move_uploaded_file($file['tmp_name'], $tmpName)) {
                        $uploadEnv[$field] = $tmpName;
                    }
                }
            }
        }

        $runTemplate = new \AbraFlexi\MultiFlexi\RunTemplate();
        if ($company->getMyKey() && $app->getMyKey()) {
            $runTemplateId = $runTemplate->runTemplateID($app->getMyKey(), $company->getMyKey());
        }

        $jobber->prepareJob($runTemplateId, $uploadEnv);
        $jobber->scheduleJobRun(new \DateTime(WebPage::getRequestValue('when')));
        $envTable = new \AbraFlexi\MultiFlexi\Ui\EnvironmentView($runTemplate->getAppEnvironment());
        $oPage->container->addItem($envTable);
        $oPage->container->addItem(new \Ease\TWB4\LinkButton('job.php?id=' . $jobber->getMyKey(), _('Job details'), 'info btn-block'));
    } else {
        $oPage->container->addItem(new JobScheduleForm($app, $company));
    }
}

$oPage->addItem(new PageBottom());
$oPage->draw();
