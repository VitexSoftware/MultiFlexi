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
 * Description of AppJson.
 *
 * @author vitex
 */
class JobDotEnv extends \Ease\Html\DivTag
{
    /**
     * APP JSON Viewer.
     *
     * @param array<string, string> $properties
     */
    public function __construct(\MultiFlexi\Job $job, array $properties = [])
    {
        parent::__construct(new \Ease\Html\PreTag($job->envFile()), $properties);
        $this->addTagClass('ui-monospace custom-control');
        $this->addItem(new \Ease\TWB5\LinkButton('jobenv.php?id='.$job->getMyKey(), _('Download').' multiflexi_job_'.$job->getMyKey().'.env', 'info '));
    }
}
