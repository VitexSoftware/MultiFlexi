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
 * Description of DbStatus.
 *
 * @author vitex
 */
class DbStatus extends \Ease\TWB4\Row
{
    /**
     * Show status of database.
     */
    public function __construct()
    {
        parent::__construct();
        $jobs = (string) (new \MultiFlexi\Job())->listingQuery()->count();
        $servers = (string) (new \MultiFlexi\Servers())->listingQuery()->count();
        $customers = (string) (new \MultiFlexi\Customer())->listingQuery()->count();
        $companies = (string) (new \MultiFlexi\Company())->listingQuery()->count();
        $apps = (string) (new \MultiFlexi\Application())->listingQuery()->count();
        $assigned = (string) (new \MultiFlexi\RunTemplate())->listingQuery()->count();

        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Jobs').'&nbsp;', new \Ease\TWB4\PillBadge('success', $jobs)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Apps').'&nbsp;', new \Ease\TWB4\PillBadge('success', $apps)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Servers').'&nbsp;', new \Ease\TWB4\PillBadge('success', $servers)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Customers').'&nbsp;', new \Ease\TWB4\PillBadge('success', $customers)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Companies').'&nbsp;', new \Ease\TWB4\PillBadge('success', $companies)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Assigned').'&nbsp;', new \Ease\TWB4\PillBadge('success', $assigned)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
    }
}
