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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\RunTemplate;

/**
 * Description of JobScheduleForm.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class JobScheduleForm extends SecureForm
{
    private $runtemplate;
    private $uploadedFiles;

    /**
     * Job Schedule Form.
     *
     * @param array $uploadedFiles Array of already uploaded file references (field => file info)
     */
    public function __construct(RunTemplate $runtemplate, array $uploadedFiles = [])
    {
        $this->runtemplate = $runtemplate;
        $this->uploadedFiles = $uploadedFiles;
        parent::__construct(['enctype' => 'multipart/form-data']);
        $this->addCSS(<<<'CSS'
            .schedule-form .form-group { margin-bottom: 0.75rem; padding: 0.5rem; border-radius: 4px; transition: background-color 0.2s; }
            .schedule-form .form-group:hover { background-color: #f8f9fa; }
            .required-field { border-left: 3px solid #dc3545 !important; }
            .secret-field { border-left: 3px solid #343a40 !important; }
            .expiring-field { border-left: 3px solid #ffc107 !important; }
            .required-field.secret-field { border-left: 3px solid #dc3545 !important; border-right: 3px solid #343a40 !important; }
            .required-field.expiring-field { border-left: 3px solid #dc3545 !important; border-right: 3px solid #ffc107 !important; }
            .field-flags { display: inline; margin-left: 0.4rem; }
            .field-flags .badge { font-size: 0.7rem; margin-left: 0.15rem; vertical-align: middle; }
CSS);
        $this->addTagClass('schedule-form');
    }

    /**
     * @return bool
     */
    public function finalize(): void
    {
        $this->addInput(new AppExecutorSelect($this->runtemplate->getApplication()), _('Executor'), 'Native', _('Choose Executon platfom'));

        $this->timeSelect();
        $this->reqiredFields();
        $this->addItem(new \Ease\TWB4\SubmitButton('<img src="images/schedule.svg" height="30">&nbsp;'._('Save App Schedule'), 'success btn-lg'));

        parent::finalize();
    }

    /**
     * Add Inputs for required fields.
     */
    public function reqiredFields(): void
    {
        foreach ($this->runtemplate->getEnvironment() as $field) {
            if ($field->isRequired() && empty($field->getValue())) {
                $code = $field->getCode();

                switch ($field->getType()) {
                    case 'file-path':
                        if (!empty($this->uploadedFiles[$code])) {
                            $fileInfo = $this->uploadedFiles[$code];
                            $this->addItem(new \Ease\Html\InputHiddenTag($code.'_uploaded', $fileInfo['ref']));
                            $this->addItem('<div class="form-group"><label>'.$field->getDescription().'</label><div>'.
                                (isset($fileInfo['name']) ? htmlspecialchars($fileInfo['name']) : _('File uploaded')).
                                '</div></div>');
                        } else {
                            $formGroup = $this->addInput(new \Ease\Html\InputFileTag($code), $field->getDescription());
                            $this->addFieldFlags($formGroup, $field);
                        }

                        break;

                    case 'bool':
                        $formGroup = $this->addInput(
                            new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($code, false, 'true', ['data-size' => 'small'])),
                            $field->getDescription(),
                        );
                        $this->addFieldFlags($formGroup, $field);

                        break;

                    default:
                        if ($field->isMultiLine()) {
                            $input = new \Ease\Html\TextareaTag($code, WebPage::getRequestValue($code), ['class' => 'form-control', 'rows' => 4]);
                        } else {
                            $input = new \Ease\Html\InputTextTag($code, WebPage::getRequestValue($code));
                        }

                        $formGroup = $this->addInput($input, $field->getDescription());
                        $this->addFieldFlags($formGroup, $field);

                        break;
                }
            }
        }
    }

    /**
     * Append flag badges and hint to a form group.
     */
    private function addFieldFlags($formGroup, \MultiFlexi\ConfigField $field): void
    {
        $flags = new \Ease\Html\SpanTag(null, ['class' => 'field-flags']);

        if ($field->isRequired()) {
            $formGroup->addTagClass('required-field');
            $flags->addItem(new \Ease\TWB4\Badge('danger', _('required')));
        }

        if ($field->isSecret()) {
            $formGroup->addTagClass('secret-field');
            $flags->addItem(new \Ease\TWB4\Badge('dark', '🔒 ' . _('secret')));
        }

        if ($field->isExpiring()) {
            $formGroup->addTagClass('expiring-field');
            $flags->addItem(new \Ease\TWB4\Badge('warning', '⏳ ' . _('expiring')));
        }

        if ($field->isMultiLine()) {
            $flags->addItem(new \Ease\TWB4\Badge('info', _('multiline')));
        }

        if (!empty($flags->pageParts)) {
            $formGroup->addItem($flags);
        }

        $hint = $field->getHint();

        if (!empty($hint)) {
            $formGroup->addItem(new \Ease\Html\SmallTag($hint, ['class' => 'form-text text-muted']));
        }
    }

    public function timeSelect(): void
    {
        $this->addInput(new \Ease\Html\InputDateTimeLocalTag('when', WebPage::isPosted() ? WebPage::getRequestValue('when') : new \DateTime()), _('Launch after'));
    }
}
