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

use Ease\TWB5\Form;
use MultiFlexi\Application;
use MultiFlexi\Company;

/**
 * Description of JobScheduleForm.
 *
 * @author vitex
 */
class JobScheduleForm extends Form
{
    private $company;
    private $app;

    /**
     * Job Sechedule Form.
     */
    public function __construct(Application $app, Company $company)
    {
        $this->company = $company;
        $this->app = $app;
        parent::__construct(['enctype' => 'multipart/form-data']);
    }

    /**
     * @return booelan
     */
    public function finalize(): void
    {
        $this->timeSelect();
        $this->uploadFields();
        $this->addItem(new \Ease\TWB5\SubmitButton('<img src="images/schedule.svg" height="30">&nbsp;'._('Save App Schedule'), 'success btn-lg'));

        parent::finalize();
    }

    public function timeSelect(): void
    {
        $this->addInput(new \Ease\Html\InputDateTimeLocalTag('when', WebPage::isPosted() ? WebPage::getRequestValue('when') : new \DateTime()), _('Launch after'));
    }

    public function uploadFields(): void
    {
        /* check if app requires upload fields */
        $appFields = \MultiFlexi\Conffield::getAppConfigs($this->app->getMyKey());
        /* if any of fields is upload type then add file input button */
        $uploadFields = array_filter($appFields, static function ($field) {
            return $field['type'] === 'file';
        });

        foreach ($uploadFields as $uploadField) {
            $this->addInput(new \Ease\Html\InputFileTag($uploadField['keyname']), $uploadField['description']);
        }

        $this->addInput(new AppExecutorSelect($this->app), _('Executor'), 'Native', _('Choose Executon platfom'));
    }
}
