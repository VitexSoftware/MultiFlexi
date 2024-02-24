<?php

/**
 * Multi Flexi - Intervals setup page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();


$oPage->addItem(new PageTop(_('Intervals Setup')));

$addAppForm = new \Ease\TWB4\Form();
$addAppForm->addItem(new \Ease\Html\H1Tag(_('Periodical tasks')));

$periodSelectorsRow = new \Ease\TWB4\Row();


$content = new \Ease\Html\InputTag('x');

$helptext = 'h';

$periodSelectorsRow->addColumn(2, new \Ease\Html\ImgTag('images/stopwatch.svg', _('StopWatch'), ['class' => 'img-fluid']));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Hourly'), 'default', new \Ease\TWB4\FormGroup(_('Minute'), $content, 1, _('At which minute to run hourly jobs?'))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Daily'), 'default', new \Ease\TWB4\FormGroup(_('Hour'), $content, 1, _('At which hour to run daily jobs?'))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Weekly'), 'default', new \Ease\TWB4\FormGroup(_('Day'), $content, 1, _('Which day of the week to run the weekly tasks?'))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Monthly'), 'default', new \Ease\TWB4\FormGroup(_('Day'), $content, 1, _('Which day of the month to run the monthly tasks?'))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Yearly'), 'default', new \Ease\TWB4\FormGroup(_('Day'), $content, 1, _('Which day of the year to run the yearly tasks?'))));

//
//$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
//
//$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
//$chooseApp = new AppsSelector('appsassigned', implode(',', $assigned));

$addAppForm->addItem($periodSelectorsRow);
$addAppForm->addItem(new \Ease\Html\PTag());

$addAppForm->addItem(new \Ease\TWB4\SubmitButton(_('Apply'), 'success btn-lg btn-block'));

$oPage->container->addItem($addAppForm);

$oPage->addItem(new PageBottom());

$oPage->draw();
