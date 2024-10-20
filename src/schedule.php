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
$oPage->onlyForLogged();

$jobID = WebPage::getRequestValue('cancel', 'int');
$jobber = new \MultiFlexi\Job($jobID);
$runTemplate = $jobID ? $jobber->runTemplate : new \MultiFlexi\RunTemplate(WebPage::getRequestValue('id', 'int'));

$oPage->addItem(new PageTop(_('Schedule Job')));

if (null === $runTemplate->getMyKey()) {
    $oPage->container->addItem(new \Ease\TWB4\Alert('error', _('RunTemplate id not specified')));
    $runTemplate->addStatusMessage(_('RunTemplate id not specified'), 'error');
} else {
    $app = $runTemplate->getApplication();
    $company = $runTemplate->getCompany();

    if (WebPage::isPosted()) {
        $when = WebPage::getRequestValue('when');
        $uploadEnv = [];

        /**
         * Save all uploaded files into temporary directory and prepare job environment.
         */
        if (!empty($_FILES)) {
            foreach ($_FILES as $field => $file) {
                if ($file['error'] === 0) {
                    $tmpName = tempnam(sys_get_temp_dir(), 'multiflexi_').'_'.basename($file['name']);

                    if (move_uploaded_file($file['tmp_name'], $tmpName)) {
                        $uploadEnv[$field]['value'] = $tmpName;
                        $uploadEnv[$field]['type'] = 'file';
                        $uploadEnv[$field]['source'] = 'Upload';
                    }
                }
            }
        }

        $prepared = $jobber->prepareJob($runTemplate->getMyKey(), $uploadEnv, '', \Ease\WebPage::getRequestValue('executor'));
        $jobber->scheduleJobRun(new \DateTime($when));

        $glassHourRow = new \Ease\TWB4\Row();
        $glassHourRow->addTagClass('justify-content-md-center');
        $glassHourRow->addColumn('4');
        $glassHourRow->addColumn('4', new \Ease\Html\DivTag(new \Ease\Html\Widgets\SandClock(['class' => 'mx-auto d-block img-fluid'])), 'sm');
        // $glassHourRow->addColumn('4', new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/openclipart/345630.svg', _('AI and Human Relationship'), ['class' => 'mx-auto d-block img-fluid'])), 'sm');
        $glassHourRow->addColumn('4');

        $oPage->container->addItem(new CompanyPanel($company, [new ApplicationPanel(
            $app,
            [$glassHourRow, new \Ease\Html\DivTag(nl2br($prepared)), new \Ease\TWB4\LinkButton('job.php?id='.$jobber->getMyKey(), _('Job details'), 'info btn-block')],
        )]));
    } else {
        if ($jobID) {
            $scheduler = new \MultiFlexi\Scheduler();
            $scheduler->deleteFromSQL(['job' => $jobID]);
            $canceller = new \MultiFlexi\Job($jobID);
            $canceller->deleteFromSQL();

            $oPage->container->addItem(new \Ease\TWB4\Label('success', _('Job Canceled')));
        } else {
            $oPage->container->addItem(
                new CompanyPanel($company, [new ApplicationPanel($app, new JobScheduleForm($app, $company))]),
            );
        }
    }
}

$oPage->addItem(new PageBottom());
$oPage->draw();
