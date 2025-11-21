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
$pageTitle = JobFilterToolbar::getPageTitle($filter);

WebPage::singleton()->addItem(new PageTop($pageTitle));

// Add filter toolbar
WebPage::singleton()->container->addItem(new JobFilterToolbar($filter, 'joblist.php'));

// Add custom success row styling
WebPage::singleton()->addCSS(<<<'CSS'
    /* Custom success row styling - lighter green with better contrast */
    #Molecule_wrapper table.dataTable tbody tr.job-success {
        background-color: #d4edda !important;
        color: #155724 !important;
    }
    #Molecule_wrapper table.dataTable tbody tr.job-success:hover {
        background-color: #c3e6cb !important;
    }
    #Molecule_wrapper table.dataTable tbody tr.job-success a {
        color: #0c5460 !important;
    }
    /* Custom scheduled row styling - light blue for waiting jobs */
    #Molecule_wrapper table.dataTable tbody tr.job-scheduled {
        background-color: #d1ecf1 !important;
        color: #0c5460 !important;
    }
    #Molecule_wrapper table.dataTable tbody tr.job-scheduled:hover {
        background-color: #bee5eb !important;
    }
    #Molecule_wrapper table.dataTable tbody tr.job-scheduled a {
        color: #004085 !important;
    }
    /* Custom orphaned row styling - yellow warning for jobs without schedule entry */
    #Molecule_wrapper table.dataTable tbody tr.job-orphaned {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }
    #Molecule_wrapper table.dataTable tbody tr.job-orphaned:hover {
        background-color: #ffe8a1 !important;
    }
    #Molecule_wrapper table.dataTable tbody tr.job-orphaned a {
        color: #533f03 !important;
    }
CSS);

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

    // Initialize Bootstrap popovers with delay to allow interaction
    $(function () {
        $('[data-toggle="popover"]').popover({
            trigger: 'manual',
            delay: { show: 100, hide: 300 }
        }).on('mouseenter', function() {
            var _this = this;
            $(this).popover('show');
            $('.popover').on('mouseleave', function() {
                $(_this).popover('hide');
            });
        }).on('mouseleave', function() {
            var _this = this;
            setTimeout(function() {
                if (!$('.popover:hover').length) {
                    $(_this).popover('hide');
                }
            }, 300);
        });
    });
    
    // Reinitialize popovers after DataTable reload
    $('#Molecule').on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            trigger: 'manual',
            delay: { show: 100, hide: 300 }
        }).on('mouseenter', function() {
            var _this = this;
            $(this).popover('show');
            $('.popover').on('mouseleave', function() {
                $(_this).popover('hide');
            });
        }).on('mouseleave', function() {
            var _this = this;
            setTimeout(function() {
                if (!$('.popover:hover').length) {
                    $(_this).popover('hide');
                }
            }, 300);
        });
    });

    setInterval(function () {
      Molecule.ajax.reload();
}, 300000);

EOD);

WebPage::singleton()->draw();
