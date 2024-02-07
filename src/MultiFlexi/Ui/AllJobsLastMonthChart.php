<?php

declare(strict_types=1);

/**
 * Multi Flexi -
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
    public function getJobs()
    {
        return parent::getJobs()->where('begin BETWEEN (CURDATE() - INTERVAL 30 DAY) AND CURDATE()');
    }
}
