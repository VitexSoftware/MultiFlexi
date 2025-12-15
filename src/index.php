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

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

$imageRow = new \Ease\TWB4\Row();
$imageRow->addTagClass('justify-content-md-center');
$imageRow->addColumn(4);

$imageRow->addColumn(4, new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/openclipart/345630.svg', _('AI and Human Relationship'), ['class' => 'mx-auto d-block img-fluid'])), 'sm');

$imageRow->addColumn(4);

WebPage::singleton()->container->addItem($imageRow);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
