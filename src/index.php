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

use Ease\TWB4\LinkButton;

require_once './init.php';

$oPage->addItem(new PageTop(_('MultiFlexi')));

try {
    if (empty(\Ease\Shared::user()->listingQuery()->count())) {
        \Ease\Shared::user()->addStatusMessage(_('There is no administrators in the database.'), 'warning');
        $oPage->container->addItem(new LinkButton('createaccount.php', _('Create first Administrator Account'), 'success'));
    }
} catch (\PDOException $exc) {
    \Ease\Shared::user()->addStatusMessage($exc->getMessage());
}

$imageRow = new \Ease\TWB4\Row();
$imageRow->addTagClass('justify-content-md-center');
$imageRow->addColumn('4');
$imageRow->addColumn('4', new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/openclipart/170381.svg', _('Powered by '), ['class' => 'mx-auto d-block img-fluid'])), 'sm');
// $imageRow->addColumn('4', new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/openclipart/345630.svg', _('AI and Human Relationship'), ['class' => 'mx-auto d-block img-fluid'])), 'sm');
$imageRow->addColumn('4');

$oPage->container->addItem($imageRow);

$oPage->addItem(new PageBottom());

$oPage->draw();
