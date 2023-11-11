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

namespace MultiFlexi;

/**
 * Description of Scheduler
 *
 * @author vitex
 */
class Scheduler extends Engine
{
    public $myTable = 'schedule';

    /**
     * Save Job excution time
     *
     * @param Job $job
     */
    public function addJob(Job $job, \DateTime $when)
    {
        return $this->insertToSQL([
                    'after' => $when->format('Y-m-d H:i:s'),
                    'job' => $job->getMyKey()
        ]);
    }

    /**
     *
     * @return int
     */
    public function getCurrentJobs()
    {
        return $this->listingQuery()->where('after > ' . (new \DateTime())->getTimestamp());
    }
}
