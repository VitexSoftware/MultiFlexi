<?php


use FlexiPeeHP\MultiSetup\Ui\AppsMenu;
use FlexiPeeHP\MultiSetup\Ui\DbStatus;
use FlexiPeeHP\MultiSetup\Ui\PageBottom;
use FlexiPeeHP\MultiSetup\Ui\PageTop;

/**
 * Multi FlexiBee Setup - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi FlexiBee Setup')));

$mainPageMenu = new AppsMenu();

$oPage->container->addItem( new \Ease\TWB4\Panel( _('Modules Availble'),'default', $mainPageMenu, new DbStatus())  );


$oPage->addItem(new PageBottom());

$oPage->draw();
