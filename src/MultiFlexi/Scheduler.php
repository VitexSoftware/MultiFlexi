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

namespace MultiFlexi;

/**
 * Description of Scheduler.
 *
 * @author vitex
 */
class Scheduler extends Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'schedule';
        $this->nameColumn = '';
        parent::__construct($identifier, $options);
    }

    /**
     * Save Job execution time.
     */
    public function addJob(Job $job, \DateTime $when)
    {
        return $this->insertToSQL([
            'after' => $when->format('Y-m-d H:i:s'),
            'job' => $job->getMyKey(),
        ]);
    }

    /**
     * @return int
     */
    public function getCurrentJobs()
    {
        // TODO: NonMySQL ?!??
        return $this->listingQuery()->orderBy('after')->where('UNIX_TIMESTAMP(after) < UNIX_TIMESTAMP(NOW())');
    }
}
