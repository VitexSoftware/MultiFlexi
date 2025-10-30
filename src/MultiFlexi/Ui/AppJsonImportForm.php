<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppJsonImportForm
 *
 * @author vitex
 */
class AppJsonImportForm extends \Ease\TWB4\Form {
    public function __construct(array $formProperties = [], array $formDivProperties = [], $formContents = null) {
        parent::__construct(['method' => 'POST', 'enctype' => 'multipart/form-data']);
        $this->setTagClass('form');
        $this->addInput(new \Ease\Html\InputFileTag('app_json_upload', '', ['mask'=>'*.json']), _('Import from your local device'), _('example.multiflexi.app.json'), _('Choose loca'));
        $this->addInput(new \Ease\Html\InputUrlTag('app_json_url', '', []), _('Import from website'), _('https:://multiflexi.eu/example.multiflexi.app.json'), _('Down'));
        
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Import Json'), 'primary'));
    }
}
