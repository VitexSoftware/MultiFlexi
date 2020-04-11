<?php

/**
 * Multi FlexiBee Setup - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use FlexiPeeHP\MultiSetup\FlexiBees;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Multi FlexiBee')));

$flexiBees = new FlexiBees();

$allFbData = $flexiBees->getAll();

$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('ID'), _('Name'), _('Url'), _('Username'),_('Add company')]);

foreach ($allFbData as $fbData) {
    unset($fbData['password']);
    unset($fbData['DatCreate']);
    unset($fbData['DatSave']);
    unset($fbData['ic']);
    
    $fbData['name'] = new \Ease\Html\ATag('flexibee.php?id='.$fbData['id'], new \Ease\Html\StrongTag($fbData['name']) );
    $fbData['url'] = new \Ease\Html\ATag($fbData['url'],$fbData['url']);
    $fbData['company'] = new LinkButton('company.php?fbid='.$fbData['id'],_('Add company'),'success');
    $fbtable->addRowColumns($fbData);
}

$oPage->container->addItem(new Panel(_('FlexiBee Instances'), 'default', $fbtable, new LinkButton('flexibee.php', _('Register new'))));

$oPage->addItem(new PageBottom());

$oPage->draw();
