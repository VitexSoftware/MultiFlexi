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

use MultiFlexi\Env\RunTemplate;

/**
 * MultiFlexi - Application Launch Form.
 *
 * @copyright  2015-2026 Vitex Software
 * @license    https://opensource.org/licenses/MIT MIT
 *
 * @no-named-arguments
 */
class RuntemplateJobsListing extends \MultiFlexi\Ui\JobHistoryTable
{
    public \MultiFlexi\RunTemplate $runtemplate;
    public bool $showIcon = false;
    public bool $showCompany = false;
    /**
     * Runtemplate Jobs Listing.
     *
     * @param array<string, string> $properties Additional properties
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate, array $properties = [])
    {
        $this->runtemplate = $runtemplate;
        parent::__construct([], $properties);
    }

    public function getJobs()
    {
        return parent::getJobs()->where('runtemplate_id', $this->runtemplate->getMyKey());
    }
}
