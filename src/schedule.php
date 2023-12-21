<?php

/**
 * Multi Flexi - About page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();
$app = new \MultiFlexi\Application(WebPage::getRequestValue('app_id', 'int'));
$company = new \MultiFlexi\Company(WebPage::getRequestValue('company_id', 'int'));
$jobID = WebPage::getRequestValue('cancel', 'int');
$oPage->addItem(new PageTop(_('Schedule Job')));

if (is_null($app->getMyKey())) {
    $oPage->container->addItem(new \Ease\TWB4\Alert('error', _('app_id not specified')));
    $app->addStatusMessage(_('app_id not specified'), 'error');
} else {
    if (WebPage::isPosted()) {
        $jobber = new \MultiFlexi\Job();
        $when = WebPage::getRequestValue('when');
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

        $runTemplate = new \MultiFlexi\RunTemplate();
        if ($company->getMyKey() && $app->getMyKey()) {
            $runTemplateId = $runTemplate->runTemplateID($app->getMyKey(), $company->getMyKey());
            $runTemplate->setMyKey($runTemplateId);
        }

        $jobber->prepareJob($runTemplateId, $uploadEnv);
        $jobber->scheduleJobRun(new \DateTime($when));

        $oPage->container->addItem(new JobInfo($jobber));
        $oPage->container->addItem(new \Ease\TWB4\LinkButton('job.php?id=' . $jobber->getMyKey(), _('Job details'), 'info btn-block'));
    } else {
        if ($jobID) {
            $scheduler = new \MultiFlexi\Scheduler();
            $scheduler->deleteFromSQL(['job' => $jobID]);
            $canceller = new \MultiFlexi\Job($jobID);
            $canceller->deleteFromSQL();

            $oPage->container->addItem(new \Ease\TWB4\Label('success', _('Job Canceled')));
        } else {
            $oPage->container->addItem(new CompanyPanel($company, [new ApplicationInfo($app, $company), new JobScheduleForm($app, $company)]));
        }
    }
}

$oPage->addItem(new PageBottom());
$oPage->draw();
