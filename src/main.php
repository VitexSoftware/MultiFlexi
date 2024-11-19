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

require_once './init.php';

WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem(new AllJobsLastMonthChart(new \MultiFlexi\Job(), ['id' => 'container']));

WebPage::singleton()->container->addItem(new \Ease\TWB4\Panel(_('Last 20 Jobs'), 'default', new JobHistoryTable(), new DbStatus()));

WebPage::singleton()->addItem(new PageBottom('jobs/'));

WebPage::singleton()->draw();
