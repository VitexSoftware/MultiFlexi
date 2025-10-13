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
use MultiFlexi\Configuration;
use MultiFlexi\RunTemplate;
use MultiFlexi\RunTplCreds;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$sourceId = WebPage::getRequestValue('id', 'int');
$cloneName = WebPage::getRequestValue('clonename');

if (empty($sourceId) || empty($cloneName)) {
    WebPage::singleton()->addItem(new PageTop(_('Runtemplate Clone')));
    WebPage::singleton()->addStatusMessage(_('Missing required parameters'), 'error');
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();

    exit;
}

try {
    // Load source runtemplate
    $sourceTemplate = new RunTemplate($sourceId);

    // Create new runtemplate
    $newTemplate = new RunTemplate();

    // Copy basic data from source template
    $templateData = $sourceTemplate->getData();
    unset($templateData[$sourceTemplate->getKeyColumn()]); // Remove ID
    $templateData['name'] = $cloneName;

    // Insert new template
    $newId = $newTemplate->insertToSQL($templateData);

    if ($newId) {
        // Copy configurations
        $configFields = $sourceTemplate->getRuntemplateEnvironment()->getFields();

        $newConfigurator = new Configuration([], ['autoload' => false]);

        foreach ($configFields as $field) {
            $newConfigurator->insertToSQL([
                'runtemplate_id' => $newId,
                'app_id' => $templateData['app_id'],
                'company_id' => $templateData['company_id'],
                'name' => $field->getName(),
                'value' => $field->getValue(),
                'config_type' => $field->getType()]);
        }

        // Copy credential assignments
        $credHelper = new RunTplCreds();
        $credentials = $credHelper->getCredentialsForRuntemplate($sourceId)->fetchAll();

        foreach ($credentials as $cred) {
            $credHelper->insertToSQL([
                'runtemplate_id' => $newId,
                'credentials_id' => $cred['credentials_id'],
            ]);
        }

        WebPage::singleton()->addStatusMessage(
            sprintf(
                _('Runtemplate %s cloned as %s'),
                $sourceTemplate->getRecordName(),
                $cloneName,
            ),
            'success',
        );
        WebPage::singleton()->redirect('runtemplate.php?id='.$newId);
    } else {
        throw new \Exception(_('Failed to create new runtemplate'));
    }
} catch (\Exception $exc) {
    WebPage::singleton()->addItem(new PageTop(_('Runtemplate Clone')));
    WebPage::singleton()->addStatusMessage(
        sprintf(_('Error cloning runtemplate: %s'), $exc->getMessage()),
        'error',
    );
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();
}
