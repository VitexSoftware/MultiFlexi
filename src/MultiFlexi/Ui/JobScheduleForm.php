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
                            // Show hidden input with file reference and a label/link
                            $fileInfo = $this->uploadedFiles[$code];
                            $this->addItem(new \Ease\Html\InputHiddenTag($code.'_uploaded', $fileInfo['ref']));
                            $this->addItem('<div class="form-group"><label>'.$field->getDescription().'</label><div>'.
                                (isset($fileInfo['name']) ? htmlspecialchars($fileInfo['name']) : _('File uploaded')).
                                '</div></div>');
                        } else {
                            $this->addInput(new \Ease\Html\InputFileTag($code), $field->getDescription());
                        }

                        break;

                    default:
                        $this->addInput(new \Ease\Html\InputTextTag($code, WebPage::getRequestValue($code)), $field->getDescription());

                        break;
                }
            }
        }
    }

    public function timeSelect(): void
    {
        $this->addInput(new \Ease\Html\InputDateTimeLocalTag('when', WebPage::isPosted() ? WebPage::getRequestValue('when') : new \DateTime()), _('Launch after'));
    }
}
