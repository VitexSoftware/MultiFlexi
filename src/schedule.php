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
        $glassHourRow->addColumn(4);
        $glassHourRow->addColumn(4, new \Ease\Html\DivTag(new \Ease\Html\Widgets\SandClock(['class' => 'mx-auto d-block img-fluid'])), 'sm');
        $glassHourRow->addColumn(4);

        $currentTime = new \DateTime();
        $beginTime = new \DateTime($when);

        $a = $currentTime->format('Y-m-d H:i:s');
        $b = $beginTime->format('Y-m-d H:i:s');

        $waitTime = $beginTime->getTimestamp() - $currentTime->getTimestamp();

        $waitRow = new \Ease\TWB4\Row();
        $waitRow->addTagClass('justify-content-md-center');
        $waitRow->addColumn(4, $when);
        $waitRow->addColumn(4, new \Ease\Html\Widgets\LiveAge($beginTime), 'sm');
        $waitRow->addColumn(4, $waitTime);

        if ($waitTime > 0) {
            $oPage->addJavaScript(
                <<<'EOD'

function wait(seconds) {
    return new Promise(resolve => setTimeout(resolve, seconds * 1000));
}

async function pollApi(apiEndpoint, pollingInterval, maxPollingDuration) {
    const startTime = Date.now(); // Record the start time

    const makeRequest = async () => {
        try {
            const response = await fetch(apiEndpoint); // Make request
            const data = await response.json();

            if (data.job.exitcode != -1) {
                console.log('Success response received:', data);
                window.location.href = "job.php?id=
EOD.$jobber->getMyKey().<<<'EOD'
";
                return; // // Stop polling if success response
            } else {
                wait(5);
            }

            const elapsedTime = Date.now() - startTime;

            if (elapsedTime < maxPollingDuration) {
                setTimeout(makeRequest, pollingInterval); // Schedule next request
            } else {
                console.log('Maximum polling duration reached. Stopping polling.');
            }
        } catch (error) {
            console.error('Error making API request:', error);
            const elapsedTime = Date.now() - startTime;

            if (elapsedTime < maxPollingDuration) {
                setTimeout(makeRequest, pollingInterval); // Schedule next request
            } else {
                console.log('Maximum polling duration reached. Stopping polling.');
            }
        }
    };

    makeRequest(); // Start the first request
}

async function patience() {
    console.log('Waiting for
EOD.$when.<<<'EOD'
...');
    await wait(
EOD.$waitTime.<<<'EOD'
);
    console.log('
EOD.$waitTime.<<<'EOD'
 seconds have passed');
    pollApi("api/VitexSoftware/MultiFlexi/1.0.0/job/
EOD.$jobber->getMyKey().<<<'EOD'
.json", 5, 3600000);
}

patience();


EOD
            );
        } else {
            $oPage->addJavaScript(
                'window.location.href = "job.php?id='.$jobber->getMyKey().'";',
            );
        }

        $appPanel = new ApplicationPanel(
            $app,
            [$glassHourRow, $waitRow, new \Ease\Html\DivTag(nl2br($prepared)), new \Ease\TWB4\LinkButton('job.php?id='.$jobber->getMyKey(), _('Job details'), 'info btn-block')],
        );
        $appPanel->headRow->addItem(new RuntemplateButton($runTemplate));

        $oPage->container->addItem(new CompanyPanel($company, $appPanel));
    } else {
        if ($jobID) {
            $scheduler = new \MultiFlexi\Scheduler();
            $scheduler->deleteFromSQL(['job' => $jobID]);
            $canceller = new \MultiFlexi\Job($jobID);
            $canceller->deleteFromSQL();

            $oPage->container->addItem(new \Ease\TWB4\Label('success', _('Job Canceled')));
        } else {
            $appPanel = new ApplicationPanel($app, new JobScheduleForm($app, $company));
            $appPanel->headRow->addItem(new RuntemplateButton($runTemplate));
            $oPage->container->addItem(
                new CompanyPanel($company, [$appPanel]),
            );
        }
    }
}

$oPage->addItem(new PageBottom($jobber->getMyKey() ? 'job/'.$jobber->getMyKey() : ''));
$oPage->draw();
