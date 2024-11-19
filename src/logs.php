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

WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new PageTop(_('Logs')));

// WebPage::singleton()->addItem(new LogViewer());

WebPage::singleton()->addJavaScript(<<<'EOD'
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

WebPage::singleton()->includeJavascript('js/dismisLog.js');

WebPage::singleton()->container->addItem(new DBDataTable(new \MultiFlexi\Logger(), ['buttons' => ['dismisAll']]));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->addJavaScript(<<<'EOD'

    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);

EOD);

WebPage::singleton()->draw();
