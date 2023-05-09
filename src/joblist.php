<?php

/**
 * Multi Flexi - List all jobs from database.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Job history')));
$appId = $oPage->getRequestValue('app_id');
$companyId = $oPage->getRequestValue('company_id');
$engine = new \AbraFlexi\MultiFlexi\CompanyJob();
$engine->setCompany($companyId);
$engine->setApp($appId);
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
$oPage->container->addItem(new DBDataTable($engine, ['buttons' => ['dismisAll']]));
$oPage->addItem(new PageBottom());
$oPage->addJavaScript('
    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);
');
$oPage->draw();
