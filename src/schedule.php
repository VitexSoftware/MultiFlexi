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

$jobID = WebPage::getRequestValue('cancel', 'int');
$jobber = new \MultiFlexi\Job($jobID);
$runTemplate = $jobID ? $jobber->runTemplate : new \MultiFlexi\RunTemplate(WebPage::getRequestValue('id', 'int'));

WebPage::singleton()->addItem(new PageTop(_('Schedule Job')));

if (null === $runTemplate->getMyKey()) {
    WebPage::singleton()->container->addItem(new \Ease\TWB4\Alert('error', _('RunTemplate id not specified')));
    $runTemplate->addStatusMessage(_('RunTemplate id not specified'), 'error');
} else {
    $app = $runTemplate->getApplication();
    $company = $runTemplate->getCompany();
    $when = WebPage::getRequestValue('when');

    if (WebPage::isPosted() || $when === 'now') {
        $uploadEnv = new \MultiFlexi\ConfigFields(_('Upload'));

        /**
         * Save all uploaded files into temporary directory and prepare job environment.
         */
        if (!empty($_FILES)) {
            $fileStore = new \MultiFlexi\FileStore();

            foreach ($_FILES as $field => $file) {
                if ($file['error'] === 0) {
                    if (is_uploaded_file($file['tmp_name'])) {
                        $uploadEnv[$field]['value'] = $file['name'];
                        $uploadEnv[$field]['upload'] = $file['tmp_name'];
                        $uploadEnv[$field]['type'] = 'file';
                        $uploadEnv[$field]['source'] = 'Upload';
                    }
                }
            }
        }

        $prepared = $jobber->prepareJob($runTemplate->getMyKey(), $uploadEnv, new \DateTime($when), \Ease\WebPage::getRequestValue('executor'), 'adhoc');

        if ($uploadEnv) {
            foreach ($uploadEnv as $field => $file) {
                $fileStore->storeFileForJob($field, $file['upload'], $file['value'], $jobber->getMyKey());
            }
        }

        // scheduleJobRun() is now called automatically inside prepareJob()

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
        $waitRow->addColumn(4, sprintf(_('Start after %s'), $when));
        $waitRow->addColumn(4, new \Ease\Html\Widgets\LiveAge($beginTime), 'sm');
        $waitRow->addColumn(4, sprintf(_('Seconds to wait: %d'), $waitTime));

        if ($waitTime > 0) {
            WebPage::singleton()->addJavaScript(
                <<<'EOD'

function pollJobCompletion(jobId, pollingInterval, maxPollingDuration) {
    const startTime = Date.now();
    const apiEndpoint = `api/VitexSoftware/MultiFlexi/1.0.0/job/${jobId}.json`;

    const makeRequest = () => {
        const elapsedTime = Date.now() - startTime;

        if (elapsedTime >= maxPollingDuration) {
            console.log('Maximum polling duration reached. Stopping polling.');
            return;
        }

        fetch(apiEndpoint)
            .then(response => response.json())
            .then(data => {
                if (data.job && data.job.exitcode !== null) {
                    console.log('Job completed with exitcode:', data.job.exitcode);
                    window.location.href = `job.php?id=${jobId}`;
                } else {
                    console.log('Job still running, polling again in ' + (pollingInterval / 1000) + 's...');
                    setTimeout(makeRequest, pollingInterval);
                }
            })
            .catch(error => {
                console.error('Error polling API:', error);
                setTimeout(makeRequest, pollingInterval);
            });
    };

    makeRequest();
}

function startPollingAfterDelay(jobId, delaySeconds, pollingIntervalMs, maxPollingMs) {
    console.log(`Waiting ${delaySeconds} seconds before starting job execution...`);
    setTimeout(() => {
        console.log('Starting job polling...');
        pollJobCompletion(jobId, pollingIntervalMs, maxPollingMs);
    }, delaySeconds * 1000);
}

startPollingAfterDelay(
EOD.$jobber->getMyKey().<<<'EOD'
,
EOD.$waitTime.<<<'EOD'
, 5000, 3600000);

EOD
            );
        } else {
            WebPage::singleton()->addJavaScript(
                'window.location.href = "job.php?id='.$jobber->getMyKey().'";',
            );
        }

        $appPanel = new ApplicationPanel(
            $app,
            [$glassHourRow, $waitRow, new \Ease\Html\DivTag(nl2br($prepared)), new \Ease\TWB4\LinkButton('job.php?id='.$jobber->getMyKey(), _('Job details'), 'info btn-block')],
        );
        $appPanel->headRow->addItem(new RuntemplateButton($runTemplate));

        WebPage::singleton()->container->addItem(new CompanyPanel($company, $appPanel));
    } else {
        if ($jobID) {
            $scheduler = new \MultiFlexi\Scheduler();
            $scheduler->deleteFromSQL(['job' => $jobID]);
            $canceller = new \MultiFlexi\Job($jobID);
            $canceller->deleteFromSQL();

            WebPage::singleton()->container->addItem(new \Ease\TWB4\Label('success', _('Job Canceled')));

            WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('queue.php', sprintf(_('remaining %d 🏁 jobs scheduled'), $scheduler->listingQuery()->count()), 'info btn-large'));
        } else {
            $appPanel = new ApplicationPanel($app, new JobScheduleForm($app, $company));
            $appPanel->headRow->addItem(new RuntemplateButton($runTemplate));
            WebPage::singleton()->container->addItem(
                new CompanyPanel($company, [$appPanel]),
            );
        }
    }
}

WebPage::singleton()->addItem(new PageBottom($jobber->getMyKey() ? 'job/'.$jobber->getMyKey() : ''));
WebPage::singleton()->draw();
