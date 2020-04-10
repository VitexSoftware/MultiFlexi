<?php
/**
 * Multi FlexiBee Setup - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use FlexiPeeHP\MultiSetup\FlexiBees;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi FlexiBee Setup')));

$oUser->addStatusMessage('HA','info');
$oUser->addStatusMessage('OK','success');
$oUser->addStatusMessage('EH','warning');
$oUser->addStatusMessage('WTF','error');


$oPage->addItem('OK');

$oPage->addItem(new PageBottom());

$oPage->draw();
