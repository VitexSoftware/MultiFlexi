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
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CompanyJobChart
 *
 * @author vitex
 */
class CompanyJobChart extends JobChart
{
    public function getJobs()
    {
        //  BETWEEN date('2014-10-09') AND date('2014-10-10')
        // Retrive only jobs from the same company and last month
        $today = date('Y-m-d');
        $lastMonth = date('Y-m-d', strtotime('-30 days', strtotime($today)));
        return parent::getJobs()->where(['company_id' => $this->engine->getDataValue('company_id')])->where("begin BETWEEN date('" . $lastMonth . "') AND  date('" . $today . "')");
//        return parent::getJobs()->where(['company_id' => $this->engine->getDataValue('company_id')])->where('begin BETWEEN (CURDATE() - INTERVAL 30 DAY) AND CURDATE()');
    }
}
