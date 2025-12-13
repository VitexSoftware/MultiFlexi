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

    /**
     * Job Schedule Form.
     */
    public function __construct(RunTemplate $runtemplate)
    {
        $this->runtemplate = $runtemplate;
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
                switch ($field->getType()) {
                    case 'file-path':
                        $this->addInput(new \Ease\Html\InputFileTag($field->getCode()), $field->getDescription());

                        break;

                    default:
                        $this->addInput(new \Ease\Html\InputTextTag($field->getCode(), WebPage::getRequestValue($field->getCode())), $field->getDescription());

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
