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

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi')));

$oPage->container->addItem(new AllJobsLastMonthChart(new \MultiFlexi\Job(), ['id' => 'container']));

$oPage->container->addItem(new \Ease\TWB4\Panel(_('Last 20 Jobs'), 'default', new JobHistoryTable(), new DbStatus()));

$oPage->addItem(new PageBottom());

$oPage->draw();
