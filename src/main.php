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

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem(new CompaniesBar());

// Get filter parameter
$filter = WebPage::singleton()->getRequestValue('filter');

$engine = new \MultiFlexi\CompanyJobLister();

// Apply filter if specified
if (!empty($filter)) {
    $engine->applyFilter($filter);
}

WebPage::singleton()->container->addItem(new AllJobsLastMonthChart($engine, ['id' => 'container']));

// Add filter toolbar
WebPage::singleton()->container->addItem(new JobFilterToolbar($filter, 'main.php'));

WebPage::singleton()->container->addItem(new DBDataTable($engine));

WebPage::singleton()->addItem(new PageBottom('jobs'));

// Add compact table styling
WebPage::singleton()->addCSS(<<<'CSS'
    #Molecule_wrapper table.dataTable tbody td {
        padding: 4px 8px !important;
        line-height: 1.3 !important;
        vertical-align: middle !important;
    }
    #Molecule_wrapper table.dataTable tbody tr {
        height: 32px !important;
    }
    #Molecule_wrapper table.dataTable thead th {
        padding: 6px 8px !important;
        font-size: 0.9em !important;
    }
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
CSS);

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
    }, 60000);

EOD);

WebPage::singleton()->draw();
