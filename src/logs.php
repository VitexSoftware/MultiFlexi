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

$oPage->addItem(new PageTop(_('Logs')));

// $oPage->addItem(new LogViewer());

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

$oPage->container->addItem(new DBDataTable(new \MultiFlexi\Logger(), ['buttons' => ['dismisAll']]));

$oPage->addItem(new PageBottom());

$oPage->addJavaScript(<<<'EOD'

    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);

EOD);

$oPage->draw();
