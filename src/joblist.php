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
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Job history')));
$appId = $oPage->getRequestValue('app_id');
$companyId = $oPage->getRequestValue('company_id');
$engine = new \MultiFlexi\CompanyJob();
$engine->setCompany($companyId);
$engine->setApp($appId);
$oPage->addJavaScript(<<<'EOD'
$.fn.dataTable.ext.buttons.dismisAll = {
    text: '
EOD._('Dismis All').<<<'EOD'
',
    action: function ( e, dt, node, config ) {
        $( ".dismis" ).each(function() {
            $( this ).click();
        });
        dt.ajax.reload();
    }
};
EOD);
$oPage->includeJavascript('js/dismisLog.js');
$oPage->container->addItem(new DBDataTable($engine, ['buttons' => ['dismisAll']]));
$oPage->addItem(new PageBottom());
$oPage->addJavaScript(<<<'EOD'

    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);

EOD);
$oPage->draw();
