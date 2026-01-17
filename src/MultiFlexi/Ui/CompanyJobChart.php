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
 * @copyright  2023-2026 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CompanyJobChart.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class CompanyJobChart extends JobChart
{
    public function getJobs()
    {
        //  BETWEEN date('2014-10-09') AND date('2014-10-10')
        // Retrive only jobs from the same company and last month
        $today = date('Y-m-d');
        $lastMonth = date('Y-m-d', strtotime('-30 days', strtotime($today)));

        return parent::getJobs()->where(['company_id' => $this->engine->getDataValue('company_id')])->where("begin BETWEEN date('".$lastMonth."') AND  date('".$today."')");
        //        return parent::getJobs()->where(['company_id' => $this->engine->getDataValue('company_id')])->where('begin BETWEEN (CURDATE() - INTERVAL 30 DAY) AND CURDATE()');
    }
}
