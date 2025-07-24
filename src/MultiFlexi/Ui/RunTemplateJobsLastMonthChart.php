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

namespace MultiFlexi\Ui;

/**
 * Description of RunTemplateJobsLastMonthChart.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class RunTemplateJobsLastMonthChart extends JobChart
{
    public function __construct(\MultiFlexi\RunTemplate $engine, $properties = [])
    {
        parent::__construct($engine, $properties);
    }

    /**
     * @return type
     */
    public function getJobs()
    {
        $today = date('Y-m-d');
        $lastMonth = date('Y-m-d', strtotime('-30 days', strtotime($today)));

        return parent::getJobs()->where("begin BETWEEN date('".$lastMonth."') AND  date('".$today."')")->where('runtemplate_id', $this->engine->getMyKey());
    }
}
