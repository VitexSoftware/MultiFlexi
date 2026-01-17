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

/**
 * Description of AppJsonImportForm.
 *
 * @author vitex
 */
class AppJsonImportForm extends \Ease\TWB4\Form
{
    public function __construct(array $formProperties = [], array $formDivProperties = [], $formContents = null)
    {
        parent::__construct(['method' => 'POST', 'enctype' => 'multipart/form-data']);
        $this->setTagClass('form');
        $this->addInput(new \Ease\Html\InputFileTag('app_json_upload', '', ['mask' => '*.json']), _('Import from your local device'), _('example.multiflexi.app.json'), _('Choose loca'));
        $this->addInput(new \Ease\Html\InputUrlTag('app_json_url', '', []), _('Import from website'), _('https:://multiflexi.eu/example.multiflexi.app.json'), _('Down'));

        $this->addItem(new \Ease\TWB4\SubmitButton(_('Import Json'), 'primary', ['title' => _('Import Application from JSON file'), 'id' => 'importappjsonbutton']));
    }
}
