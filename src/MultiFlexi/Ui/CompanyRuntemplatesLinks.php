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
 * Description of CompanyRuntemplatesLinks.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CompanyRuntemplatesLinks extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\Company $company, \MultiFlexi\Application $application, array $properties = [], array $linkProperties = [])
    {
        $runTemplater = new \MultiFlexi\RunTemplate();
        $runtemplatesRaw = $runTemplater->listingQuery()->where('app_id', $application->getMyKey())->where('company_id', $company->getMyKey());

        $runtemplatesDiv = new \Ease\Html\DivTag();

        if ($runtemplatesRaw->count()) {
            foreach ($runtemplatesRaw as $runtemplateData) {
                $linkProperties['title'] = $runtemplateData['name'];
                $runtemplatesDiv->addItem(new \Ease\Html\ATag('runtemplate.php?id='.$runtemplateData['id'], '⚗️', $linkProperties));
            }
        } else {
            $runtemplatesDiv->addItem(new \Ease\Html\ATag('runtemplate.php?new=1&app_id='.$application->getMyKey().'&company_id='.$company->getMyKey(), '➕', 'success'));
        }

        parent::__construct($runtemplatesDiv, $properties);
    }
    
    public function count(): int {
        return $this->pageParts[0]->getItemsCount();
    }
}
