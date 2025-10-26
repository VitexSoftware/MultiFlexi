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

use Ease\WebPage;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\RunTemplate;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));

$actions = new \MultiFlexi\ActionConfig();

if (WebPage::isPosted()) {
    $succesActions = ActionsChooser::toggles('success');
    $failActions = ActionsChooser::toggles('fail');
    $runTemplate->setDataValue('fail', serialize($failActions));
    $runTemplate->setDataValue('success', serialize($succesActions));
    $runTemplate->saveToSQL();

    $successConfig = ActionsChooser::formModuleCofig('success');
    if (is_array($successConfig)) {
        $actions->saveModeConfigs('success', $successConfig, $runTemplate->getMyKey());
    }
    $failConfig = ActionsChooser::formModuleCofig('fail');
    if (is_array($failConfig)) {
        $actions->saveModeConfigs('fail', $failConfig, $runTemplate->getMyKey());
    }
} else {
    $failActions = $runTemplate->getDataValue('fail') ? unserialize($runTemplate->getDataValue('fail')) : [];
    $succesActions = $runTemplate->getDataValue('success') ? unserialize($runTemplate->getDataValue('success')) : [];
}

if (WebPage::getRequestValue('new', 'int') === 1) {
    $app = new Application(WebPage::getRequestValue('app_id', 'int'));
    $runTemplate->setDataValue('app_id', WebPage::getRequestValue('app_id', 'int'));
    $runTemplate->setDataValue('company_id', WebPage::getRequestValue('company_id', 'int'));
    $runTemplate->setDataValue('interv', 'n');
    $runTemplate->setDataValue('name', _($app->getRecordName()));

    $runTemplate->takeData([]);

    $runTemplate->dbsync();
}

if (WebPage::getRequestValue('delete', 'int') === 2) {
    $runTemplate->deleteFromSQL();
    WebPage::singleton()->redirect('companyapp.php?company_id='.$runTemplate->getDataValue('company_id').'&app_id='.$runTemplate->getDataValue('app_id'));
}

$companies = new Company($runTemplate->getDataValue('company_id'));
$app = new Application($runTemplate->getDataValue('app_id'));

$app->setDataValue('company_id', $companies->getMyKey());
$app->setDataValue('app_id', $app->getMyKey());
$app->setDataValue('app_name', $app->getRecordName());

$configurator = new \MultiFlexi\Configuration([
    'runtemplate_id' => $runTemplate->getMyKey(),
    'app_id' => $app->getMyKey(),
    'company_id' => $companies->getMyKey(),
], ['autoload' => false]);

if (WebPage::singleton()->isPosted()) {
    $dataToSave = $_POST;

    if (\array_key_exists('credential', $dataToSave)) {
        if ($dataToSave['credential']) {
            $rtplcrd = new \MultiFlexi\RunTplCreds();

            foreach ($dataToSave['credential'] as $reqType => $reqId) {
                if ($reqId && is_numeric($reqId)) {
                    $rtplcrd->bind($runTemplate->getMyKey(), (int) $reqId, $reqType);
                } else {
                    $rtplcrd->unbindAll($runTemplate->getMyKey(), $reqType);
                }
            }
        }

        unset($dataToSave['credential']);
    }

    $app->checkRequiredFields($dataToSave, true);

    $dataToSave['app_id'] = $app->getMyKey();
    $dataToSave['company_id'] = $companies->getMyKey();
    $dataToSave['runtemplate_id'] = $runTemplate->getMyKey();

    /**
     * Save all uploaded files into temporary directory and prepare job environment.
     */
    if (!empty($_FILES)) {
        $uploadEnv = [];
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

        if ($uploadEnv) {
            foreach ($uploadEnv as $field => $file) {
                $fileStore->storeFileForRuntemplate($field, $file['upload'], $file['value'], $runTemplate);
            }
        }
    }

    if ($configurator->takeData($dataToSave) && null !== $configurator->saveToSQL()) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
        // Spustit setup příkaz, pokud je nastaven
        $setupCommand = $app->getDataValue('setup');

        if (empty($setupCommand) === false) {
            $appEnvironment = $runTemplate->getEnvironment()->getEnvArray();
            $process = new \Symfony\Component\Process\Process(
                explode(' ', $setupCommand),
                null,
                $appEnvironment,
                null,
                32767,
            );
            $result = $process->run();
            $output = $process->getOutput();
            $error = $process->getErrorOutput();

            if ($result === 0) {
                $configurator->addStatusMessage(_('Setup command executed successfully:'), 'success');

                if ($output) {
                    $configurator->addStatusMessage($output, 'info');
                }
            } else {
                $configurator->addStatusMessage(_('Setup command failed:'), 'error');

                if ($error) {
                    $configurator->addStatusMessage($error, 'error');
                }
            }
        }
    } else {
        $configurator->addStatusMessage(_('Error saving Config fields'), 'error');
    }
}

WebPage::singleton()->addItem(new PageTop($runTemplate->getRecordName().' '._('Configuration')));

$appPanel = new ApplicationPanel($app, new RunTemplatePanel($runTemplate));

WebPage::singleton()->container->addItem(new CompanyPanel($companies, $appPanel));

WebPage::singleton()->addItem(new PageBottom('runtemplate/'.$runTemplate->getMyKey()));
WebPage::singleton()->draw();
