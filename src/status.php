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

$jobber = new \MultiFlexi\Job();

$todaysJobs = $jobber->listingQuery()->where($jobber->todaysCond());

WebPage::singleton()->container->addItem(new JobSuccessGraph($todaysJobs));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
