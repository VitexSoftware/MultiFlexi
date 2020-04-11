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

$mainPageMenu = new \Ease\TWB4\Widgets\MainPageMenu();

$mainPageMenu->addMenuItem(_('Matcher'), 'matcher.php', 'images/matcher.png', _('invoice matching'), _('Configure'));
$mainPageMenu->addMenuItem(_('Reminder'), 'reminder.php', 'images/reminder.png', _('reminds sender'), _('Configure'));
$mainPageMenu->addMenuItem(_('Digest'), 'digest.php', 'images/digest.svg', _('modular digest'), _('Configure'));


$oPage->container->addItem( new Panel( _('Modules Availble'),'default', $mainPageMenu, new DbStatus())  );


$oPage->addItem(new PageBottom());

$oPage->draw();
