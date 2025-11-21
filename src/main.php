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

// Get dynamic object name for JavaScript and CSS (without namespace)
$objectName = \Ease\Functions::baseClassName($engine);

// Add compact table styling
WebPage::singleton()->addCSS(<<<CSS
    #{$objectName}_wrapper table.dataTable tbody td {
        padding: 4px 8px !important;
        line-height: 1.3 !important;
        vertical-align: middle !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr {
        height: 32px !important;
    }
    #{$objectName}_wrapper table.dataTable thead th {
        padding: 6px 8px !important;
        font-size: 0.9em !important;
    }
    /* Custom success row styling - lighter green with better contrast */
    #{$objectName}_wrapper table.dataTable tbody tr.job-success {
        background-color: #d4edda !important;
        color: #155724 !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.job-success:hover {
        background-color: #c3e6cb !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.job-success a {
        color: #0c5460 !important;
    }
    /* Custom scheduled row styling - light blue for waiting jobs */
    #{$objectName}_wrapper table.dataTable tbody tr.job-scheduled {
        background-color: #d1ecf1 !important;
        color: #0c5460 !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.job-scheduled:hover {
        background-color: #bee5eb !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.job-scheduled a {
        color: #004085 !important;
    }
    /* Custom orphaned row styling - yellow warning for jobs without schedule entry */
    #{$objectName}_wrapper table.dataTable tbody tr.job-orphaned {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.job-orphaned:hover {
        background-color: #ffe8a1 !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.job-orphaned a {
        color: #533f03 !important;
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
    $('#
EOD.$objectName.<<<'EOD'
').on('draw.dt', function() {
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
      $('#
EOD.$objectName.<<<'EOD'
').DataTable().ajax.reload();
    }, 60000);

EOD);

WebPage::singleton()->draw();
