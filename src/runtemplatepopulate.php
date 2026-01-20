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

// $runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));
//
// if (WebPage::singleton()->isPosted()) {
//    if ($runTemplate->takeData($_POST) && !is_null($runTemplate->saveToSQL())) {
//        $runTemplate->addStatusMessage(_('Config fields Saved'), 'success');
//    } else {
//        $runTemplate->addStatusMessage(_('Error saving Config fields'), 'error');
//    }
// }

require_once './init.php';
WebPage::singleton()->onlyForLogged();
$result = false;
$id = \Ease\TWB4\WebPage::getRequestValue('id', 'int');
$replace = \Ease\TWB4\WebPage::getRequestValue('replace', 'string') === 'on';

if (null !== $id) {
    $runtemplater = new \MultiFlexi\RunTemplate($id);

    if (isset($_ENV) && \is_array($_ENV) && \array_key_exists('env', $_FILES)  && strlen($_FILES['env']['name'])) {
        $env = [];

        foreach (file($_FILES['env']['tmp_name']) as $cfgRow) {
            if (strstr($cfgRow, '=')) {
                [$key, $value] = preg_split('/=/', $cfgRow, 2);
                $env[$key] = trim($value, " \t\n\r\0\x0B'\"");
            }
        }

        if ($env) {
            $configurator = new \MultiFlexi\Configuration(
                [
                    'runtemplate_id' => $runtemplater->getMyKey(),
                    'app_id' => $runtemplater->getApplication()->getMyKey(),
                    'company_id' => $runtemplater->getCompany()->getMyKey(),
                ],
                ['autoload' => false],
            );

            if ($replace === false) {
                $oldEnv = $runtemplater->getRuntemplateEnvironment();

                foreach ($oldEnv as $field => $value) {
                    if (\array_key_exists($field, $env) && \array_key_exists($field, $oldEnv) && !empty($oldEnv[$field])) {
                        unset($env[$field]);
                    }
                }
            }

            if ($configurator->takeData($env) && null !== $configurator->saveToSQL()) {
                $configurator->addStatusMessage(_('Config fields Saved'), 'success');
            } else {
                $configurator->addStatusMessage(_('Error saving Config fields'), 'error');
            }
        }
    } else {
        $configurator->addStatusMessage(_('No .env file provided'));
    }
}

WebPage::singleton()->redirect('runtemplate.php?id='.$runtemplater->getMyKey());
