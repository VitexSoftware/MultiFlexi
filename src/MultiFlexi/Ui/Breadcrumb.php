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
 * Description of Breadcrumb.
 *
 * @author vitex
 */
class Breadcrumb extends \Ease\TWB4\Breadcrumb
{
    /**
     * App Breadcrumb.
     *
     * @param mixed                 $content
     * @param array<string, string> $properties
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct($content, $properties);

        if (empty($_SESSION['customer'])) {
            $this->addPage(_('choose Customer'), 'customers.php');
        } else {
            $customer = new \MultiFlexi\Customer($_SESSION['customer']);
            $this->addPage(_('Customer').': '.$customer->getRecordName(), $customer->getLink());
        }

        if (empty($_SESSION['server'])) {
            $this->addPage(_('choose Server'), 'servers.php');
        } else {
            $server = new \MultiFlexi\Servers($_SESSION['server']);
            $this->addPage(_('Server').': '.$server->getRecordName(), $server->getLink());
        }

        if (empty($_SESSION['company'])) {
            $this->addPage(_('choose Company'), 'companies.php');
        } else {
            $company = new \MultiFlexi\Company($_SESSION['company']);
            $this->addPage(_('Company').': '.$company->getRecordName(), $company->getLink());
        }

        //        $this->addCurrentPage('Service');
    }
}
