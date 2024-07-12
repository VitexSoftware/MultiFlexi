<?php

/**
 * Multi Flexi - Cron Scheduler
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();

$runTemplate = new \MultiFlexi\RunTemplate(WebPage::getRequestValue('id', 'int'));

$jobID = WebPage::getRequestValue('cancel', 'int');
$oPage->addItem(new PageTop(_('Schedule Job')));

if (is_null($runTemplate->getMyKey())) {
    $oPage->container->addItem(new \Ease\TWB4\Alert('error', _('RunTemplate id not specified')));
    $runTemplate->addStatusMessage(_('RunTemplate id not specified'), 'error');
} else {
    $app = $runTemplate->getApplication();
    $company = $runTemplate->getCompany();
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
                        $uploadEnv[$field]['value'] = $tmpName;
                        $uploadEnv[$field]['type'] = 'file';
                        $uploadEnv[$field]['source'] = 'Upload';
                    }
                }
            }
        }

        $jobber->prepareJob($runTemplate->getMyKey(), $uploadEnv, '', \Ease\WebPage::getRequestValue('executor'));
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
