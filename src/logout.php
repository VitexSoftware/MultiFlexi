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

if ((null === \Ease\Shared::user()->getUserID()) === false) {
    \Ease\Shared::user()->logout();
}

WebPage::singleton()->addItem(new PageTop(_('Sign Off')));

$byerow = new \Ease\TWB5\Row();
$byerow->addColumn(6, new \Ease\Html\ImgTag('images/openclipart/'.\MultiFlexi\Ui\OpenClipart::randomImage('images/openclipart')));
$byeInfo = $byerow->addColumn(6, new \Ease\Html\H1Tag(_('Good bye')));

$byeInfo->addItem('<br/><br/><br/><br/>');
$byeInfo->addItem(new \Ease\Html\DivTag(new \Ease\Html\ATag(
    'login.php',
    _('Thank you for your patronage and look forward to another visit'),
    ['class' => 'jumbotron'],
)));
$byeInfo->addItem('<br/><br/><br/><br/>');

WebPage::singleton()->container->addItem($byerow);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
