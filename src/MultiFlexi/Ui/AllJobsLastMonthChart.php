<?php

declare(strict_types=1);

/**
 * Multi Flexi - All Jobs in last Month Chart
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AllJobsLastMonthChart
 *
 * @author vitex
 */
class AllJobsLastMonthChart extends JobChart
{
    /**
     *
     * @return type
     */
    public function getJobs()
    {
        $today = date('Y-m-d');
        $lastMonth = date('Y-m-d', strtotime('-30 days', strtotime($today)));
        return parent::getJobs()->where("begin BETWEEN date('" . $lastMonth . "') AND  date('" . $today . "')");
//        return parent::getJobs()->where('begin BETWEEN (CURDATE() - INTERVAL 30 DAY) AND CURDATE()');
    }
}
