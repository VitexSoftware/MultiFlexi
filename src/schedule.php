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

use MultiFlexi\ConfigField;

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
        $uploadedFiles = [];
        $allFieldsFilled = true;

        // TODO: Upload required fiels
        //        // Gather already uploaded file references from hidden fields
        //        foreach ($runTemplate->getEnvironment() as $field) {
        //            if ($field->getType() === 'file-path') {
        //                $code = $field->getCode();
        //                $ref = WebPage::getRequestValue($code.'_uploaded');
        //
        //                if ($ref) {
        //                    $uploadedFiles[$code] = [
        //                        'ref' => $ref,
        //                        'name' => $field->getValue() ?: $ref,
        //                    ];
        //                    // Mark as filled
        //                    $cfg = new ConfigField($code, 'file-path', $ref);
        //                } else {
        //                    $cfg = new ConfigField($code.'_uploaded', 'file-path', $ref);
        //                    $allFieldsFilled = false;
        //                }
        //                $cfg->setValue($ref);
        //                $uploadEnv->addField($cfg);
        //
        //            } elseif ($field->isRequired() && empty($field->getValue()) && empty(WebPage::getRequestValue($field->getCode()))) {
        //                $allFieldsFilled = false;
        //            }
        //        }

        // Handle new file uploads
        if (!empty($_FILES)) {
            $fileStore = new \MultiFlexi\FileStore();

            foreach ($_FILES as $field => $file) {
                if ($file['error'] === 0 && is_uploaded_file($file['tmp_name'])) {
                    // Store file and get reference (simulate with tmp_name for now)
                    $ref = $file['tmp_name'];
                    $cfg = new ConfigField($field, 'file-path', $file['name']);
                    $cfg->setValue($ref);
                    $cfg->setHint($file['name']);
                    $uploadEnv->addField($cfg);
                    $uploadedFiles[$field] = [
                        'ref' => $ref,
                        'name' => $file['name'],
                    ];
                    // $fileStore->storeFileForJob($field, $file['tmp_name'], $file['name'], $jobber); // Uncomment if FileStore is available
                }
            }
        }

        // Check all required fields (text and file)
        foreach ($runTemplate->getEnvironment() as $field) {
            if ($field->isRequired() && empty($field->getValue())) {
                $code = $field->getCode();

                if ($field->getType() === 'file-path') {
                    if (empty($uploadedFiles[$code])) {
                        $allFieldsFilled = false;
                    }
                } else {
                    if (empty(WebPage::getRequestValue($code))) {
                        $allFieldsFilled = false;
                    }
                }
            }
        }

        if ($allFieldsFilled) {
            $prepared = $jobber->prepareJob($runTemplate, $uploadEnv, new \DateTime($when), \Ease\WebPage::getRequestValue('executor'), 'adhoc');
            // Store files for job if needed (simulate)
            // foreach ($uploadEnv as $field => $file) {
            //     $fileStore->storeFileForJob($field, $file->getValue(), $file->getHint(), $jobber);
            // }

            // ...existing code for job scheduling and polling...
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
// ...existing code...

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
            // Not all required fields filled, re-show form with persisted file references
            $appPanel = new ApplicationPanel($app, new JobScheduleForm($runTemplate, $uploadedFiles));
            $appPanel->headRow->addItem(new RuntemplateButton($runTemplate));
            WebPage::singleton()->container->addItem(
                new CompanyPanel($company, [$appPanel]),
            );
        }
    } else {
        if ($jobID) {
            $scheduler = new \MultiFlexi\Scheduler();
            $scheduler->deleteFromSQL(['job' => $jobID]);
            $canceller = new \MultiFlexi\Job($jobID);
            $canceller->deleteFromSQL();

            WebPage::singleton()->container->addItem(new \Ease\TWB4\Label('success', _('Job Canceled')));

            WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('queue.php', sprintf(_('remaining %d 🏁 jobs scheduled'), $scheduler->listingQuery()->count()), 'info btn-large'));
        } else {
            $appPanel = new ApplicationPanel($app, new JobScheduleForm($runTemplate));
            $appPanel->headRow->addItem(new RuntemplateButton($runTemplate));
            WebPage::singleton()->container->addItem(
                new CompanyPanel($company, [$appPanel]),
            );
        }
    }
}

WebPage::singleton()->addItem(new PageBottom($jobber->getMyKey() ? 'job/'.$jobber->getMyKey() : ''));
WebPage::singleton()->draw();
