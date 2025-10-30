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

// Získání parametrů filtru
$appId = WebPage::singleton()->getRequestValue('app_id');
$companyId = WebPage::singleton()->getRequestValue('company_id');
$filter = WebPage::singleton()->getRequestValue('filter');

// Nastavení titulku podle filtru
$pageTitle = _('Job history');
switch ($filter) {
    case 'success':
        $pageTitle = _('Successful Jobs');
        break;
    case 'failed':
        $pageTitle = _('Failed Jobs');
        break;
    case 'running':
        $pageTitle = _('Running Jobs');
        break;
    case 'today':
        $pageTitle = _('Today\'s Jobs');
        break;
}

WebPage::singleton()->addItem(new PageTop($pageTitle));

// Add responsive CSS for filter buttons layout
WebPage::singleton()->addCSS(<<<'CSS'
#job-filter-buttons {
    display: inline-block;
    margin-right: 1rem;
    vertical-align: middle;
}

#job-filter-buttons .btn-group {
    display: inline-flex;
}

/* Mobile: stack vertically */
@media (max-width: 767px) {
    #job-filter-buttons {
        display: block;
        width: 100%;
        margin-right: 0;
        margin-bottom: 1rem;
    }
    #job-filter-buttons .btn-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    #job-filter-buttons .btn-group a {
        width: 100%;
        border-radius: 0.25rem !important;
        margin-bottom: 0.25rem;
    }
}
CSS);

// Add filter buttons container with ID for CSS targeting
$filterButtonsContainer = new \Ease\Html\DivTag(null, ['id' => 'job-filter-buttons']);
$buttonGroup = $filterButtonsContainer->addItem(new \Ease\Html\DivTag(null, ['class' => 'btn-group btn-group-sm', 'role' => 'group']));

// All Jobs button
$allJobsClass = empty($filter) ? 'btn btn-primary' : 'btn btn-outline-primary';
$buttonGroup->addItem(new \Ease\Html\ATag('joblist.php', '🏁 '._('All Jobs'), ['class' => $allJobsClass]));

// Success button
$successClass = ($filter === 'success') ? 'btn btn-success' : 'btn btn-outline-success';
$buttonGroup->addItem(new \Ease\Html\ATag('joblist.php?filter=success', '✅ '._('Successful'), ['class' => $successClass]));

// Failed button
$failedClass = ($filter === 'failed') ? 'btn btn-danger' : 'btn btn-outline-danger';
$buttonGroup->addItem(new \Ease\Html\ATag('joblist.php?filter=failed', '❌ '._('Failed'), ['class' => $failedClass]));

// Running button
$runningClass = ($filter === 'running') ? 'btn btn-info' : 'btn btn-outline-info';
$buttonGroup->addItem(new \Ease\Html\ATag('joblist.php?filter=running', '▶️ '._('Running'), ['class' => $runningClass]));

// Today button
$todayClass = ($filter === 'today') ? 'btn btn-warning' : 'btn btn-outline-warning';
$buttonGroup->addItem(new \Ease\Html\ATag('joblist.php?filter=today', '📅 '._('Today'), ['class' => $todayClass]));

WebPage::singleton()->container->addItem($filterButtonsContainer);

// Vytvoření engine a aplikace filtru
$engine = new \MultiFlexi\CompanyJobLister();
$engine->setCompany($companyId);
$engine->setApp($appId);

// Aplikace filtru pokud je specifikován
if (!empty($filter)) {
    $engine->applyFilter($filter);
}
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
WebPage::singleton()->container->addItem(new DBDataTable($engine, ['buttons' => ['dismisAll']]));
WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->addJavaScript(<<<'EOD'

    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);

EOD);

// Move filter buttons next to DataTable controls after table initialization
WebPage::singleton()->addJavaScript(<<<'EOD'
$(document).ready(function() {
    // Wait for DataTable to be fully initialized
    setTimeout(function() {
        var filterButtons = $('#job-filter-buttons');
        var tableWrapper = $('.dataTables_wrapper');
        
        if (filterButtons.length && tableWrapper.length) {
            // On desktop: prepend to the first row (where length and filter are)
            if ($(window).width() >= 768) {
                tableWrapper.find('.row:first .col-sm-12:first').prepend(filterButtons);
            }
            // On mobile: keep as is (already before table)
        }
        
        // Handle window resize
        $(window).on('resize', function() {
            var filterButtons = $('#job-filter-buttons');
            if ($(window).width() >= 768) {
                // Move to DataTable controls area
                if (!tableWrapper.find('#job-filter-buttons').length) {
                    tableWrapper.find('.row:first .col-sm-12:first').prepend(filterButtons);
                }
            } else {
                // Move back before table
                if (tableWrapper.find('#job-filter-buttons').length) {
                    tableWrapper.before(filterButtons);
                }
            }
        });
    }, 100);
});
EOD);

WebPage::singleton()->draw();
