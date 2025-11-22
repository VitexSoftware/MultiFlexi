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

use Ease\Html\ATag;
use Ease\Html\H3Tag;
use Ease\TWB4\LinkButton;
use Ease\TWB4\Row;
use Ease\TWB4\Table;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\Job;
use MultiFlexi\RunTemplate;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$companer = new Company(WebPage::getRequestValue('company_id', 'int'));
$application = new Application(WebPage::getRequestValue('app_id', 'int'));

WebPage::singleton()->addItem(new PageTop(_($application->getRecordName()).'@'.$companer->getRecordName()));

// Create CompanyApp object for chart
$companyApp = (new \MultiFlexi\CompanyApp($companer))->setApp($application);

// RunTemplates section with header and button
$runtemplatesDiv = new \Ease\Html\DivTag();

// Add chart above RunTemplates table
$runtemplatesDiv->addItem(new CompanyAppJobsLastMonthChart($companyApp));

$runtemplatesHeader = new Row(null, ['style' => 'margin-top: 20px;']);
$runtemplatesHeader->addColumn(6, new H3Tag('⚗️ '._('RunTemplates for this Company')));
$runtemplatesHeader->addColumn(6, [
    new LinkButton(
        'runtemplate.php?new=1&app_id='.$application->getMyKey().'&company_id='.$companer->getMyKey(),
        '⚗️&nbsp;➕ '._('New RunTemplate'),
        'success'
    ),
    '&nbsp;',
    new \Ease\Html\DivTag(
        [
            new \Ease\Html\ButtonTag(
                '⚙️ '._('Bulk Actions'),
                [
                    'id' => 'bulkActionsBtn',
                    'class' => 'btn btn-warning dropdown-toggle',
                    'type' => 'button',
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false',
                    'disabled' => 'disabled',
                    'data-container' => 'body',
                    'data-trigger' => 'hover',
                    'data-placement' => 'top',
                    'data-content' => _('Select one or more RunTemplates by clicking on table rows to enable bulk actions')
                ]
            ),
            new \Ease\Html\DivTag(
                [
                    new \Ease\Html\ATag('#', _('🔧 Bulk Reconfigure'), ['class' => 'dropdown-item bulk-reconfigure']),
                    new \Ease\Html\ATag('#', _('▶️ Bulk Execute'), ['class' => 'dropdown-item bulk-execute']),
                    new \Ease\Html\ATag('#', _('🔘 Bulk Enable/Disable'), ['class' => 'dropdown-item bulk-toggle']),
                ],
                ['class' => 'dropdown-menu', 'aria-labelledby' => 'bulkActionsBtn']
            )
        ],
        ['class' => 'btn-group', 'role' => 'group', 'style' => 'display: inline-block;']
    )
]);
$runtemplatesDiv->addItem($runtemplatesHeader);

// Create RunTemplate lister engine
$engine = (new \MultiFlexi\CompanyAppRunTemplateLister())
    ->setCompany($companer)
    ->setApp($application);

// Add DataTable
$runtemplatesDiv->addItem(new DBDataTable($engine));

// Last 10 jobs table
$runtemplatesDiv->addItem(new \Ease\Html\HrTag());
$runtemplatesDiv->addItem(new H3Tag('🏁 '._('Last 10 jobs')));

$jobber = new Job();
$jobs = $jobber->listingQuery()
    ->select(['job.id', 'begin', 'schedule', 'exitcode', 'launched_by', 'login', 'runtemplate_id', 'runtemplate.name AS runtemplate_name'], true)
    ->leftJoin('user ON user.id = job.launched_by')
    ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')
    ->where('job.company_id', $companer->getMyKey())
    ->where('job.app_id', $application->getMyKey())
    ->limit(10)
    ->orderBy('job.id DESC')
    ->fetchAll();

$jobList = new Table(null, ['class' => 'table table-sm table-hover']);
$jobList->addRowHeaderColumns([_('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('RunTemplate')]);

foreach ($jobs as $job) {
    $jobRow = [];
    
    // Job ID
    $jobRow[] = new ATag('job.php?id='.$job['id'], '🏁 '.$job['id']);
    
    // Launch time or scheduled time
    if (empty($job['begin'])) {
        if (!empty($job['schedule'])) {
            try {
                $scheduleTime = new \DateTime($job['schedule']);
                $relativeTime = \MultiFlexi\CompanyJobLister::getRelativeTime($scheduleTime);
                $jobRow[] = '💣 <span title="'.htmlspecialchars($job['schedule']).'">'.$relativeTime.'</span>';
            } catch (\Exception $e) {
                $jobRow[] = _('Scheduled');
            }
        } else {
            $jobRow[] = _('Not launched yet');
        }
    } else {
        $jobRow[] = [
            $job['begin'],
            ' ',
            new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))
        ];
    }
    
    // Exit code
    $jobRow[] = new ExitCode($job['exitcode']);
    
    // Launcher
    $jobRow[] = $job['launched_by'] 
        ? new ATag('user.php?id='.$job['launched_by'], $job['login']) 
        : _('Timer');
    
    // RunTemplate
    if (!empty($job['runtemplate_id'])) {
        $jobRow[] = new ATag('runtemplate.php?id='.$job['runtemplate_id'], $job['runtemplate_name'] ?? '#'.$job['runtemplate_id']);
    } else {
        $jobRow[] = '—';
    }
    
    $jobList->addRowColumns($jobRow);
}

$runtemplatesDiv->addItem($jobList);

// Job history link
$runtemplatesDiv->addItem(new LinkButton(
    'joblist.php?app_id='.$application->getMyKey().'&company_id='.$companer->getMyKey(),
    '🏁 '._('View Complete Job History'),
    'info btn-lg btn-block'
));

// Wrap everything in CompanyPanel with CompanyApplicationPanel
// This will show "Aktivní RunTemplates" panel with all RunTemplates for this app across companies
WebPage::singleton()->container->addItem(
    new CompanyPanel(
        $companer,
        new CompanyApplicationPanel($companyApp, $runtemplatesDiv)
    )
);

// Get dynamic object name for JavaScript and CSS (without namespace)
$objectName = \Ease\Functions::baseClassName($engine);

// Add compact table styling similar to main.php
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
    /* Row selection styling */
    #{$objectName}_wrapper table.dataTable tbody tr.selected {
        background-color: #b0bed9 !important;
        color: #000 !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr.selected:hover {
        background-color: #a0aec9 !important;
    }
    #{$objectName}_wrapper table.dataTable tbody tr {
        cursor: pointer;
    }
CSS);

// Add JavaScript for row selection and bulk actions
WebPage::singleton()->addJavaScript(<<<'EOD'

    var selectedRows = [];
    var table = null;
    var appId = 
EOD.$application->getMyKey().<<<'EOD'
;
    var companyId = 
EOD.$companer->getMyKey().<<<'EOD'
;
    
    $(document).ready(function() {
        table = $('#
EOD.$objectName.<<<'EOD'
').DataTable();
        
        // Add custom buttons to DataTables button group
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    text: '☑️ Select All',
                    className: 'btn-info',
                    action: function(e, dt, node, config) {
                        selectAllVisibleRows();
                    }
                },
                {
                    text: '☐ Clear Selection',
                    className: 'btn-secondary',
                    attr: {
                        id: 'clearSelectionBtn'
                    },
                    init: function(dt, node, config) {
                        $(node).hide();
                    },
                    action: function(e, dt, node, config) {
                        clearAllSelection();
                    }
                }
            ]
        });
        
        // Insert buttons at the beginning of the buttons container
        table.buttons(1, null).container().prependTo($('#
EOD.$objectName.<<<'EOD'
_wrapper .dt-buttons'));
        
        // Initialize popover for bulk actions button
        $('#bulkActionsBtn').popover();
        
        // Restore selection after table redraw
        table.on('draw', function() {
            restoreSelection();
        });
        
        // Row click handler for selection
        $('#
EOD.$objectName.<<<'EOD'
 tbody').on('click', 'tr', function(e) {
            // Don't select if clicking on a link
            if ($(e.target).closest('a').length > 0) {
                return;
            }
            
            var $row = $(this);
            var data = table.row(this).data();
            
            if (!data) return;
            
            // Extract RunTemplate ID from the first column (⚗️ #123)
            var idMatch = data.id.match(/#(\d+)/);
            if (!idMatch) return;
            
            var rtId = parseInt(idMatch[1]);
            
            // Toggle selection
            if ($row.hasClass('selected')) {
                $row.removeClass('selected');
                selectedRows = selectedRows.filter(function(id) { return id !== rtId; });
            } else {
                $row.addClass('selected');
                if (selectedRows.indexOf(rtId) === -1) {
                    selectedRows.push(rtId);
                }
            }
            
            // Update bulk actions button state
            updateBulkActionsButton();
        });
        
        // Bulk reconfigure action
        $('.bulk-reconfigure').on('click', function(e) {
            e.preventDefault();
            if (selectedRows.length === 0) return;
            
            showBulkReconfigureModal();
        });
        
        // Bulk execute action
        $('.bulk-execute').on('click', function(e) {
            e.preventDefault();
            if (selectedRows.length === 0) return;
            
            if (confirm('Do you want to execute ' + selectedRows.length + ' selected RunTemplate(s) now?')) {
                bulkExecute();
            }
        });
        
        // Bulk toggle action
        $('.bulk-toggle').on('click', function(e) {
            e.preventDefault();
            if (selectedRows.length === 0) return;
            
            showBulkToggleModal();
        });
    });
    
    function updateBulkActionsButton() {
        var $button = $('#bulkActionsBtn');
        var $clearBtn = $('#clearSelectionBtn');
        
        if (selectedRows.length > 0) {
            $button.prop('disabled', false);
            $button.html('⚙️ ' + selectedRows.length + ' selected');
            // Hide popover when button is active
            $button.popover('disable');
            // Show clear selection button
            $clearBtn.show();
        } else {
            $button.prop('disabled', true);
            $button.html('⚙️ Bulk Actions');
            // Re-enable popover when button is disabled
            $button.popover('enable');
            // Hide clear selection button
            $clearBtn.hide();
        }
    }
    
    function restoreSelection() {
        // Restore selected class on rows that are in selectedRows array
        table.rows().every(function() {
            var data = this.data();
            if (!data) return;
            
            // Extract RunTemplate ID from the first column
            var idMatch = data.id.match(/#(\d+)/);
            if (!idMatch) return;
            
            var rtId = parseInt(idMatch[1]);
            var $row = $(this.node());
            
            // Add selected class if this row's ID is in selectedRows
            if (selectedRows.indexOf(rtId) !== -1) {
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }
        });
    }
    
    function selectAllVisibleRows() {
        // Select all currently visible rows in the DataTable
        table.rows({search: 'applied'}).every(function() {
            var data = this.data();
            if (!data) return;
            
            // Extract RunTemplate ID from the first column
            var idMatch = data.id.match(/#(\d+)/);
            if (!idMatch) return;
            
            var rtId = parseInt(idMatch[1]);
            var $row = $(this.node());
            
            // Add to selection if not already selected
            if (selectedRows.indexOf(rtId) === -1) {
                selectedRows.push(rtId);
            }
            $row.addClass('selected');
        });
        
        updateBulkActionsButton();
    }
    
    function clearAllSelection() {
        // Clear all selected rows
        selectedRows = [];
        
        // Remove selected class from all rows
        table.rows().every(function() {
            $(this.node()).removeClass('selected');
        });
        
        updateBulkActionsButton();
    }
    
    function showBulkReconfigureModal() {
        // Get configuration fields from API
        $.ajax({
            url: 'api/VitexSoftware/MultiFlexi/1.0.0/app/' + appId + '.json',
            method: 'GET',
            dataType: 'json',
            success: function(appData) {
                // API now returns data directly without wrapper object
                if (appData && appData.environment && typeof appData.environment === 'object') {
                    displayReconfigureModal(appData.environment);
                } else {
                    alert('Error: No environment configuration found for this application');
                    console.error('App data:', appData);
                }
            },
            error: function(xhr, status, error) {
                alert('Error loading configuration fields: ' + error);
                console.error('XHR:', xhr.responseText);
            }
        });
    }
    
    function displayReconfigureModal(fields) {
        var fieldOptions = '';
        for (var key in fields) {
            fieldOptions += '<option value="' + key + '">' + fields[key].description + ' (' + key + ')</option>';
        }
        
        var modalHtml = `
            <div class="modal fade" id="bulkReconfigureModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">🔧 Bulk Reconfigure ` + selectedRows.length + ` RunTemplate(s)</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Configuration Key:</label>
                                <select id="configKey" class="form-control">
                                    <option value="">-- Select key --</option>
                                    ` + fieldOptions + `
                                </select>
                            </div>
                            <div class="form-group">
                                <label>New Value:</label>
                                <input type="text" id="configValue" class="form-control" placeholder="Enter new value">
                            </div>
                            <div class="alert alert-info">
                                This will update the selected configuration key in all ` + selectedRows.length + ` selected RunTemplate(s).
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="executeBulkReconfigureBtn">Apply Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#bulkReconfigureModal').remove();
        
        // Add and show modal
        $('body').append(modalHtml);
        $('#bulkReconfigureModal').modal('show');
        
        // Bind button click event
        $('#executeBulkReconfigureBtn').on('click', function() {
            executeBulkReconfigure();
        });
    }
    
    function executeBulkReconfigure() {
        var configKey = $('#configKey').val();
        var configValue = $('#configValue').val();
        
        if (!configKey) {
            alert('Please select a configuration key');
            return;
        }
        
        // TODO: Migrate to API endpoint when POST support is available:
        // url: 'api/VitexSoftware/MultiFlexi/1.0.0/runtemplates/bulk-reconfigure'
        $.ajax({
            url: 'bulk-reconfigure.php',
            method: 'POST',
            data: {
                runtemplate_ids: selectedRows,
                config_key: configKey,
                config_value: configValue
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var message = response.message || 'Successfully updated ' + response.updated + ' RunTemplate(s)';
                    if (response.failed && response.failed.length > 0) {
                        message += '\n\nFailed items remain selected for retry.';
                        // Keep only failed items selected
                        selectedRows = response.failed;
                    } else {
                        // Clear selection if all succeeded
                        selectedRows = [];
                    }
                    alert(message);
                    $('#bulkReconfigureModal').modal('hide');
                    table.ajax.reload();
                    updateBulkActionsButton();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(xhr) {
                var error = 'Error executing bulk reconfigure';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        error = response.error;
                    }
                } catch(e) {}
                alert(error);
            }
        });
    }
    
    function bulkExecute() {
        // TODO: Migrate to API endpoint when POST support is available:
        // url: 'api/VitexSoftware/MultiFlexi/1.0.0/runtemplates/bulk-execute'
        $.ajax({
            url: 'bulk-execute.php',
            method: 'POST',
            data: {
                runtemplate_ids: selectedRows,
                when: 'now',
                executor: 'Native'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var msg = response.message || 'Successfully scheduled ' + response.scheduled + ' job(s)';
                    if (response.errors && response.errors.length > 0) {
                        msg += '\n\nWarnings:\n' + response.errors.join('\n');
                    }
                    if (response.failed && response.failed.length > 0) {
                        msg += '\n\nFailed items remain selected for retry.';
                        // Keep only failed items selected
                        selectedRows = response.failed;
                    } else {
                        // Clear selection if all succeeded
                        selectedRows = [];
                    }
                    alert(msg);
                    table.ajax.reload();
                    updateBulkActionsButton();
                } else {
                    alert('Error: ' + response.error + (response.errors ? '\n' + response.errors.join('\n') : ''));
                }
            },
            error: function(xhr) {
                var error = 'Error executing bulk schedule';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        error = response.error;
                    }
                } catch(e) {}
                alert(error);
            }
        });
    }
    
    function showBulkToggleModal() {
        var modalHtml = `
            <div class="modal fade" id="bulkToggleModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">🔘 Bulk Enable/Disable ` + selectedRows.length + ` RunTemplate(s)</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Action:</label>
                                <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                    <label class="btn btn-success active flex-fill">
                                        <input type="radio" name="toggleAction" value="1" checked> ✅ Enable All
                                    </label>
                                    <label class="btn btn-danger flex-fill">
                                        <input type="radio" name="toggleAction" value="0"> ❌ Disable All
                                    </label>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                This will <span id="actionText">enable</span> ` + selectedRows.length + ` selected RunTemplate(s).
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="executeBulkToggleBtn">Apply Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#bulkToggleModal').remove();
        
        // Add and show modal
        $('body').append(modalHtml);
        $('#bulkToggleModal').modal('show');
        
        // Update action text when radio changes
        $('input[name="toggleAction"]').on('change', function() {
            var action = $(this).val() === '1' ? 'enable' : 'disable';
            $('#actionText').text(action);
        });
        
        // Bind button click event
        $('#executeBulkToggleBtn').on('click', function() {
            var activeValue = parseInt($('input[name="toggleAction"]:checked').val());
            executeBulkToggle(activeValue);
        });
    }
    
    function executeBulkToggle(activeValue) {
        var $button = $('#executeBulkToggleBtn');
        $button.prop('disabled', true).text('Processing...');
        
        var completed = 0;
        var failedIds = [];
        var errors = [];
        var total = selectedRows.length;
        var currentRows = selectedRows.slice(); // Copy array for iteration
        
        // Process each RunTemplate sequentially
        function processNext(index) {
            if (index >= currentRows.length) {
                // All done
                $('#bulkToggleModal').modal('hide');
                
                var message = 'Successfully updated ' + completed + ' of ' + total + ' RunTemplate(s)';
                if (errors.length > 0) {
                    message += '\n\nErrors:\n' + errors.join('\n');
                    if (failedIds.length > 0) {
                        message += '\n\nFailed items remain selected for retry.';
                    }
                }
                alert(message);
                
                // Keep only failed items selected
                selectedRows = failedIds;
                
                // Reload table - restoreSelection() will highlight failed items
                table.ajax.reload();
                updateBulkActionsButton();
                return;
            }
            
            var rtId = currentRows[index];
            
            $.ajax({
                url: 'api/VitexSoftware/MultiFlexi/1.0.0/runtemplate/' + rtId + '.json',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    id: rtId,
                    active: activeValue
                }),
                success: function(response) {
                    completed++;
                    processNext(index + 1);
                },
                error: function(xhr) {
                    var error = 'RunTemplate #' + rtId + ': ';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        error += response.error || 'Unknown error';
                    } catch(e) {
                        error += xhr.statusText || 'Unknown error';
                    }
                    errors.push(error);
                    failedIds.push(rtId); // Keep failed ID for reselection
                    processNext(index + 1);
                }
            });
        }
        
        // Start processing
        processNext(0);
    }

EOD);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
