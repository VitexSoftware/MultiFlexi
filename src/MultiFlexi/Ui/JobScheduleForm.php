<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

/**
 *
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Application;
use MultiFlexi\Company;
use Ease\TWB4\Form;

/**
 * Description of JobScheduleForm
 *
 * @author vitex
 */
class JobScheduleForm extends Form
{
    private $company;
    private $app;

    /**
     * Job Sechedule Form
     *
     * @param Application $app
     * @param Company     $company
     */
    public function __construct(Application $app, Company $company)
    {
        $this->company = $company;
        $this->app = $app;
        parent::__construct(['enctype' => 'multipart/form-data']);
    }

    /**
     *
     * @return booelan
     */
    public function finalize()
    {
        $this->timeSelect();
        $this->uploadFields();
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save App Schedule'), 'success btn-lg'));
        return parent::finalize();
    }

    /**
     *
     */
    public function timeSelect()
    {
        $this->addInput(new \Ease\Html\InputDateTimeLocalTag('when', WebPage::isPosted() ? WebPage::getRequestValue('when') : new \DateTime()), _('Launch after'));
    }

    /**
     *
     */
    public function uploadFields()
    {
        /* check if app requires upload fields */
        $appFields = \MultiFlexi\Conffield::getAppConfigs($this->app->getMyKey());
        /* if any of fields is upload type then add file input button */
        $uploadFields = array_filter($appFields, function ($field) {
            return $field['type'] == 'file';
        });
        foreach ($uploadFields as $uploadField) {
            $this->addInput(new \Ease\Html\InputFileTag($uploadField['keyname']), $uploadField['description']);
        }
        $this->addInput(new AppExecutorSelect($this->app), _('Executor'), 'Native', _('Choose Executon platfom'));
    }
}
