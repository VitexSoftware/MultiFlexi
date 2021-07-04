<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Logs')));

//$oPage->addItem(new LogViewer());

$oPage->addJavaScript('$.fn.dataTable.ext.buttons.dismisAll = {
    text: \'' . _('Dismis All') . '\',
    action: function ( e, dt, node, config ) {
        $( ".dismis" ).each(function() {
            $( this ).click();
        });
        dt.ajax.reload();
    }
};');


$oPage->includeJavascript('js/dismisLog.js');

//$oPage->container->addItem( new ui\DashboardMessages());
$oPage->container->addItem(new DBDataTable(new \AbraFlexi\MultiSetup\Logger(),
                ['buttons' => ['dismisAll']]));

$oPage->addItem(new PageBottom());

$oPage->addJavaScript('
    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);
');



$oPage->draw();
