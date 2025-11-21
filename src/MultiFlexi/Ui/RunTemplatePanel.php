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

/**
 * Description of RunTemplatePanel.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
/**
 * Class RunTemplatePanel.
 *
 * Panel for configuring and displaying RunTemplate options in MultiFlexi.
 */
class RunTemplatePanel extends \Ease\TWB4\Panel
{
    /**
     * @var \MultiFlexi\RunTemplate runTemplate instance for this panel
     */
    private \MultiFlexi\RunTemplate $runtemplate;

    /**
     * Run Template Configuration panel.
     */
    /**
     * RunTemplatePanel constructor.
     *
     * @param \MultiFlexi\RunTemplate $runtemplate runTemplate instance
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate)
    {
        $this->runtemplate = $runtemplate;
        $runtemplateId = $runtemplate->getMyKey();
        $runtemplateOptions = new \Ease\TWB4\Row();
        $intervalChoosen = $runtemplate->getDataValue('interv') ?? 'n';
        $crontab = $runtemplate->getDataValue('cron') ?? '';
        $delayChoosen = (int) $runtemplate->getDataValue('delay');
        $intervalChooser = new \MultiFlexi\Ui\IntervalChooser($runtemplateId.'_interval', $intervalChoosen, ['id' => $runtemplateId.'_interval', 'checked' => 'true', 'data-runtemplate' => $runtemplateId]);
        \MultiFlexi\Ui\CrontabInput::includeAssets();

        // Set cron input as disabled by default unless interval is set to 'c' (Custom)
        $cronInputAttribs = ['data-runtemplate' => $runtemplateId];

        if ($intervalChoosen !== 'c') {
            $cronInputAttribs['disabled'] = 'disabled';
            $cronInputAttribs['style'] = 'opacity: 0.5; pointer-events: none;';
        }

        $crontabInput = new \MultiFlexi\Ui\CrontabInput($runtemplateId.'_cron', $crontab, $cronInputAttribs);

        $delayChooser = new \MultiFlexi\Ui\DelayChooser($runtemplateId.'_delay', $delayChoosen, ['id' => $runtemplateId.'_delay', 'checked' => 'true', 'data-runtemplate' => $runtemplateId]);
        $executorChooser = new AppExecutorSelect($runtemplate->getApplication(), [], (string) $runtemplate->getDataValue('executor'), ['id' => $runtemplateId.'_executor', 'data-runtemplate' => $runtemplateId]);

        $scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runtemplateId, [_('Schedule Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/multiflexi-schedule.svg', _('Launch'), ['height' => '30px'])], 'secondary btn-lg w-100');
        $launchButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runtemplateId.'&when=now&executor=Native', [_('Launch now').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/multiflexi-execute.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg w-100');
        
        // Left column: Statistics and visualizations
        $leftColumn = new \Ease\Html\DivTag();
        
        // Add statistics cards
        $statsCards = new \MultiFlexi\Ui\RunTemplateStatsCards($runtemplate);
        $leftColumn->addItem($statsCards);
        
        // Add job visualization row
        $visualizationRow = new \Ease\TWB4\Row();
        $visualizationRow->addTagClass('mt-3');
        $visualizationRow->addColumn(8, new \MultiFlexi\Ui\RunTemplateJobsLastMonthChart($runtemplate));
        $visualizationRow->addColumn(4, new \MultiFlexi\Ui\JobGraphWidget($runtemplate, 10, 10));
        $leftColumn->addItem($visualizationRow);
        
        // Add action buttons below the chart
        $actionsRow = new \Ease\TWB4\Row();
        $actionsRow->addTagClass('mt-3');
        $actionsRow->addColumn(6, $launchButton);
        $actionsRow->addColumn(6, $scheduleButton);
        $leftColumn->addItem($actionsRow);
        
        $runtemplateOptions->addColumn(8, $leftColumn);
        
        // Right column: Status and Configuration
        $rightColumn = new \Ease\Html\DivTag();
        
        // Status section
        $rightColumn->addItem(new \Ease\Html\H5Tag(_('Status')));
        $rightColumn->addItem([
            _('Status').' ',
            new \Ease\TWB4\Widgets\Toggle('active', $runtemplate->getDataValue('active') ? true : false, $runtemplate->getDataValue('active') ? 'false' : 'true', ['title' => $runtemplate->getDataValue('active') ? _('Enabled') : _('Disabled'), 'data-runtemplate' => $runtemplateId, 'id' => 'enabler', 'data-on' => _('Enabled'), 'data-off' => _('Disabled')]), 
            new \Ease\Html\SpanTag('', ['id' => 'deactivated']),
            '<hr class="my-2">',
        ]);
        
        // Scheduling Configuration section
        $rightColumn->addItem('<hr>');
        $rightColumn->addItem(new \Ease\Html\H5Tag(_('Scheduling Configuration')));
        $rightColumn->addItem([
            new \Ease\Html\StrongTag(_('Interval')),
            '<br/>',
            $intervalChooser,
            '<br/>',
            new \Ease\Html\StrongTag(_('Cron Expression')),
            '<br/>',
            $crontabInput,
            '<br/>',
            new \Ease\Html\StrongTag(_('Startup Delay')),
            '<br/>',
            $delayChooser,
            '<hr>',
            new \Ease\Html\StrongTag(_('Executor')),
            '<br/>',
            $executorChooser,
        ]);
        
        $runtemplateOptions->addColumn(4, $rightColumn);

        if (WebPage::getRequestValue('delete', 'int') === 1) {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=2&id='.$runtemplateId, _('Delete !!!').'&nbsp;&nbsp;❌', 'danger btn-lg');
        } else {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=1&id='.$runtemplateId, _('Delete ?').'&nbsp;&nbsp;❌', 'warning btn-lg');
        }

        $runtemplateJobs = new \MultiFlexi\Ui\RuntemplateJobsListing($runtemplate);

        $nameInput = new \Ease\Html\ATag('#', $runtemplate->getRecordName(), ['class' => 'editable', 'style' => 'font-size: xxx-large;', 'id' => 'name', 'data-pk' => $runtemplate->getMyKey(), 'data-url' => 'runtemplatesave.php', 'data-title' => _('Update RunTemplate name')]);

        // Add note field as WYSIWYG editable textarea
        $noteValue = $runtemplate->getDataValue('note') ?: _('Click to add notes...');
        $noteInput = new \Ease\Html\ATag('#', $noteValue, [
            'class' => 'editable',
            'id' => 'note',
            'data-pk' => $runtemplate->getMyKey(),
            'data-url' => 'runtemplatesave.php',
            'data-type' => 'textarea',
            'data-title' => _('Update RunTemplate notes'),
            'data-wysiwyg' => 'summernote',
            'style' => 'display: block; margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 60px; background: #f9f9f9;',
        ]);

        $runtemplateBottom = new \Ease\TWB4\Row();

        if ($runtemplate->getMyKey()) {
            $runtemplateBottom->addColumn(4, new RuntemplateCloneForm($runtemplate));
            $runtemplateBottom->addColumn(4, $deleteButton);
            $runtemplateBottom->addColumn(4, new RuntemplatePopulateForm($runtemplate));
        }

        parent::__construct([new \Ease\Html\ATag('companyapp.php?app_id='.$runtemplate->getDataValue('app_id').'&company_id='.$runtemplate->getDataValue('company_id'), '<span style="font-size: xxx-large;">⚗️ </span>'), $nameInput, $noteInput], 'default', $runtemplateOptions, $runtemplateBottom);
        $this->includeJavaScript('js/bootstrap-editable.js');
        $this->includeCss('css/bootstrap-editable.css');
        // Include WYSIWYG assets for note field
        $this->includeJavaScript('js/summernote-bs4.min.js');
        $this->includeCss('css/summernote-bs4.min.css');
        $this->addJavaScript("$.fn.editable.defaults.mode = 'inline';");
        //        $this->addJavaScript("$.fn.editable.options.savenochange = true;");
        $this->addJavaScript(<<<'EOD'
      $.fn.editableform.buttons =
        '<button type="submit" class="btn btn-primary btn-sm editable-submit">' +
        '<i class="fa fa-fw fa-check"></i>' +
        '</button>' +
        '<button type="button" class="btn btn-inverse btn-sm editable-cancel">' +
        '<i class="fa fa-fw fa-times"></i>' +
        '</button>'

EOD);
        // Note: editable initialization moved to finalize() method where CSRF token is available
        // Both name and note fields will be initialized there with proper CSRF configuration

        $runtemplateTabs = new \Ease\TWB4\Tabs();
        $runtemplateTabs->addTab(_('Jobs'), $runtemplateJobs);
        $runtemplateTabs->addTab(_('Options'), [new RuntemplateConfigForm($runtemplate)]);
        // TODO:   $runtemplateTabs->addTab(_('Actions'), [new \MultiFlexi\Ui\ActionsTab($runtemplate)]);
        $runtemplateTabs->addTab(_('Environment'), [new EnvironmentView($runtemplate->credentialsEnvironment()), new RunTemplateDotEnv($runtemplate)]);

        $this->addItem($runtemplateTabs);
    }

    /**
     * Finalizes the panel by adding JavaScript and Bootstrap integration.
     */
    public function finalize(): void
    {
        \Ease\TWB4\Part::twBootstrapize();
        $csrfToken = isset($GLOBALS['csrfProtection']) ? $GLOBALS['csrfProtection']->generateToken() : '';

        // Configure editable to send CSRF token
        $this->addJavaScript(<<<EOD
// Configure editable to send CSRF token
$.fn.editable.defaults.ajaxOptions = {
    beforeSend: function(xhr) {
        xhr.setRequestHeader('X-CSRF-Token', '{$csrfToken}');
    }
};

// Also add CSRF token to POST data for editable
$.fn.editable.defaults.params = function(params) {
    params.csrf_token = '{$csrfToken}';
    return params;
};

// Initialize all editable fields with CSRF token configuration
$(document).ready(function() {
    // Initialize regular editable fields (name field)
    $('.editable').not('#note').editable();

    // Configure WYSIWYG editor for note field with CSRF token
    $("#note").editable({
        type: "textarea",
        display: function(value, sourceData) {
            $(this).html(value);
        },
        success: function(response, newValue) {
            // Handle successful save
        },
        ajaxOptions: {
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-Token', '{$csrfToken}');
            }
        },
        params: function(params) {
            params.csrf_token = '{$csrfToken}';
            return params;
        }
    });

    // Initialize Summernote when editing starts
    $("#note").on("shown.editable", function(e, editable) {
        editable.input.\$input.summernote({
            height: 150,
            toolbar: [
                ["style", ["style"]],
                ["font", ["bold", "italic", "underline", "clear"]],
                ["fontname", ["fontname"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["table", ["table"]],
                ["insert", ["link"]],
                ["view", ["codeview", "help"]]
            ]
        });
    });

    // Cleanup Summernote when editing ends
    $("#note").on("hidden.editable", function(e, reason) {
        if (reason === "save" || reason === "nochange") {
            var summernoteInstance = $(this).find(".note-editable");
            if (summernoteInstance.length) {
                summernoteInstance.summernote("destroy");
            }
        }
    });

    // Initialize cron input state based on current interval selection
    var currentInterval = $('#{$this->runtemplate->getMyKey()}_interval').val();
    var cronElement = $('#{$this->runtemplate->getMyKey()}_cron');

    if (currentInterval === 'c') {
        // Enable cron input for Custom interval
        cronElement.prop('disabled', false);
        cronElement.css({
            'opacity': '1',
            'pointer-events': 'auto'
        });
        console.log('Cron input initialized as enabled for custom interval');
    } else {
        // Disable cron input for other intervals
        cronElement.prop('disabled', true);
        cronElement.css({
            'opacity': '0.5',
            'pointer-events': 'none'
        });
        console.log('Cron input initialized as disabled for interval:', currentInterval);
    }
});
EOD);

        $this->addJavaScript(<<<EOD

// Global variable to store current CSRF token
var currentCsrfToken = '{$csrfToken}';

// Function to refresh CSRF token
function refreshCsrfToken() {
    return new Promise(function(resolve, reject) {
        \$.get('getcsrf.php', function(data) {
            if (data && data.csrf_token) {
                currentCsrfToken = data.csrf_token;
                resolve(data.csrf_token);
            } else {
                reject('Failed to get CSRF token');
            }
        }).fail(function() {
            reject('CSRF token request failed');
        });
    });
}

$('#{$this->runtemplate->getMyKey()}_interval').change( function(event, state) {

    var intervalValue = $(this).val();
    var cronElement = $('#{$this->runtemplate->getMyKey()}_cron');

    // Enable/disable cron input based on interval selection
    if (intervalValue === 'c') {
        // Enable cron input for Custom interval
        cronElement.prop('disabled', false);
        cronElement.css({
            'opacity': '1',
            'pointer-events': 'auto'
        });
        console.log('Cron input enabled for custom interval');
    } else {
        // Disable cron input for other intervals
        cronElement.prop('disabled', true);
        cronElement.css({
            'opacity': '0.5',
            'pointer-events': 'none'
        });
        console.log('Cron input disabled for interval:', intervalValue);
    }

    $.ajax({
        url: 'rtinterval.php',
        data: {
            runtemplate: $(this).attr("data-runtemplate"),
            interval: intervalValue,
            csrf_token: currentCsrfToken
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 400) {
                // CSRF token might be expired, try to refresh and retry
                refreshCsrfToken().then(function(newToken) {
                    $.ajax({
                        url: 'rtinterval.php',
                        data: {
                            runtemplate: $('#{$this->runtemplate->getMyKey()}_interval').attr("data-runtemplate"),
                            interval: $('#{$this->runtemplate->getMyKey()}_interval').val(),
                            csrf_token: newToken
                        },
                        success: function(data) {
                            $('#{$this->runtemplate->getMyKey()}_interval').after( "💾" );
                            console.log("saved after retry");
                        },
                        error: function() {
                            $('#{$this->runtemplate->getMyKey()}_interval').after( "⚰️" );
                            console.log("not saved even after retry");
                        },
                        type: 'POST'
                    });
                }).catch(function() {
                    $('#{$this->runtemplate->getMyKey()}_interval').after( "⚰️" );
                    console.log("CSRF token refresh failed");
                });
            } else {
                $('#{$this->runtemplate->getMyKey()}_interval').after( "⚰️" );
                console.log("not saved");
            }
        },

        success: function(data) {
            $('#{$this->runtemplate->getMyKey()}_interval').after( "💾" );
            console.log("saved");
            // Refresh CSRF token for subsequent requests
            refreshCsrfToken();
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<EOD

$('#{$this->runtemplate->getMyKey()}_delay').change( function(event, state) {

$.ajax({
   url: 'rtdelay.php',
        data: {
                runtemplate: $(this).attr("data-runtemplate"),
                delay: $(this).val(),
                csrf_token: currentCsrfToken
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 400) {
                // CSRF token might be expired, try to refresh and retry
                refreshCsrfToken().then(function(newToken) {
                    $.ajax({
                        url: 'rtdelay.php',
                        data: {
                            runtemplate: $('#{$this->runtemplate->getMyKey()}_delay').attr("data-runtemplate"),
                            delay: $('#{$this->runtemplate->getMyKey()}_delay').val(),
                            csrf_token: newToken
                        },
                        success: function(data) {
                            $('#{$this->runtemplate->getMyKey()}_delay').after( "💾" );
                            console.log("saved after retry");
                        },
                        error: function() {
                            $('#{$this->runtemplate->getMyKey()}_delay').after( "⚰️" );
                            console.log("not saved even after retry");
                        },
                        type: 'POST'
                    });
                }).catch(function() {
                    $('#{$this->runtemplate->getMyKey()}_delay').after( "⚰️" );
                    console.log("CSRF token refresh failed");
                });
            } else {
                $('#{$this->runtemplate->getMyKey()}_delay').after( "⚰️" );
                console.log("not saved");
            }
        },

        success: function(data) {
            $('#{$this->runtemplate->getMyKey()}_delay').after( "💾" );
            console.log("saved");
            // Refresh CSRF token for subsequent requests
            refreshCsrfToken();
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<EOD

$('#{$this->runtemplate->getMyKey()}_executor').change( function(event, state) {

$.ajax({
   url: 'rtexecutor.php',
        data: {
                runtemplate: $(this).attr("data-runtemplate"),
                executor: $(this).val(),
                csrf_token: currentCsrfToken
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 400) {
                // CSRF token might be expired, try to refresh and retry
                refreshCsrfToken().then(function(newToken) {
                    $.ajax({
                        url: 'rtexecutor.php',
                        data: {
                            runtemplate: $('#{$this->runtemplate->getMyKey()}_executor').attr("data-runtemplate"),
                            executor: $('#{$this->runtemplate->getMyKey()}_executor').val(),
                            csrf_token: newToken
                        },
                        success: function(data) {
                            $('#{$this->runtemplate->getMyKey()}_executor').after( "💾" );
                            console.log("saved after retry");
                        },
                        error: function() {
                            $('#{$this->runtemplate->getMyKey()}_executor').after( "⚰️" );
                            console.log("not saved even after retry");
                        },
                        type: 'POST'
                    });
                }).catch(function() {
                    $('#{$this->runtemplate->getMyKey()}_executor').after( "⚰️" );
                    console.log("CSRF token refresh failed");
                });
            } else {
                $('#{$this->runtemplate->getMyKey()}_executor').after( "⚰️" );
                console.log("not saved");
            }
        },

        success: function(data) {
            $('#{$this->runtemplate->getMyKey()}_executor').after( "💾" );
            console.log("saved");
            // Refresh CSRF token for subsequent requests
            refreshCsrfToken();
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<'EOD'

$('#enabler').change( function(event, state) {

$.ajax({
   url: 'rtactive.php',
        data: {
                runtemplate: $(this).attr("data-runtemplate"),
                active: $(this).val(),
                csrf_token: currentCsrfToken
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 400) {
                // CSRF token might be expired, try to refresh and retry
                refreshCsrfToken().then(function(newToken) {
                    $.ajax({
                        url: 'rtactive.php',
                        data: {
                            runtemplate: $('#enabler').attr("data-runtemplate"),
                            active: $('#enabler').val(),
                            csrf_token: newToken
                        },
                        success: function(data) {
                            $('#deactivated').before( "💾" );
                            console.log("saved after retry");
                        },
                        error: function() {
                            $('#deactivated').before( "⚰️" );
                            console.log("not saved even after retry");
                        },
                        type: 'POST'
                    });
                }).catch(function() {
                    $('#deactivated').before( "⚰️" );
                    console.log("CSRF token refresh failed");
                });
            } else {
                $('#deactivated').before( "⚰️" );
                console.log("not saved");
            }
        },

        success: function(data) {
            $('#deactivated').before( "💾" );
            console.log("saved");
            // Refresh CSRF token for subsequent requests
            refreshCsrfToken();
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<EOD


// Debouncing variables for cron save
var cronSaveTimeout;
var lastSavedCronValue = '';
var isSaving = false;

// Function to get cron value from the custom component
function getCronValue() {
    var cronElement = $('#{$this->runtemplate->getMyKey()}_cron')[0];
    if (cronElement) {
        // For the cron-expression-input web component, get value from the input field inside
        var inputElement = cronElement.querySelector('.cronInsideInput');
        if (inputElement) {
            return inputElement.value || '';
        }

        // Fallback: Try multiple ways to get the value
        return cronElement.value ||
               $(cronElement).attr('value') ||
               $(cronElement).find('input').val() ||
               $(cronElement).data('value') ||
               '';
    }
    return '';
}

// Function to save cron value with debouncing to prevent multiple requests
function saveCronValue() {
    var cronElement = $('#{$this->runtemplate->getMyKey()}_cron');

    // Don't save if the cron input is disabled
    if (cronElement.prop('disabled')) {
        console.log('Skipping save: cron input is disabled');
        return;
    }

    var cronValue = getCronValue();

    console.log('Attempting to save cron value:', cronValue);

    // Don't save if value is empty or just asterisks (default empty cron)
    if (!cronValue || cronValue === '* * * * *' || cronValue.trim() === '') {
        console.log('Skipping save: empty or default cron value');
        return;
    }

    // Don't save if value hasn't changed or we're already saving
    if (cronValue === lastSavedCronValue || isSaving) {
        console.log('Skipping save: unchanged value or save in progress');
        return;
    }

    isSaving = true;
    lastSavedCronValue = cronValue;

    $.ajax({
        url: 'rtcron.php',
        data: {
            runtemplate: $('#{$this->runtemplate->getMyKey()}_cron').attr("data-runtemplate"),
            cron: cronValue,
            csrf_token: currentCsrfToken
        },
        error: function(xhr) {
            console.log('Cron save error:', xhr.status, xhr.responseText);
            isSaving = false;

            if (xhr.status === 403 || xhr.status === 400) {
                // CSRF token might be expired, try to refresh and retry
                refreshCsrfToken().then(function(newToken) {
                    var retryValue = getCronValue();
                    console.log('Retrying with new token, cron value:', retryValue);
                    $.ajax({
                        url: 'rtcron.php',
                        data: {
                            runtemplate: $('#{$this->runtemplate->getMyKey()}_cron').attr("data-runtemplate"),
                            cron: retryValue,
                            csrf_token: newToken
                        },
                        success: function(data) {
                            $('#{$this->runtemplate->getMyKey()}_cron').after( "💾" );
                            console.log("cron saved after retry", retryValue);
                            isSaving = false;
                        },
                        error: function() {
                            $('#{$this->runtemplate->getMyKey()}_cron').after( "⚰️" );
                            console.log("cron not saved even after retry");
                            isSaving = false;
                            // Reset last saved value so it can be retried
                            lastSavedCronValue = '';
                        },
                        type: 'POST'
                    });
                }).catch(function() {
                    $('#{$this->runtemplate->getMyKey()}_cron').after( "⚰️" );
                    console.log("CSRF token refresh failed");
                    isSaving = false;
                    lastSavedCronValue = '';
                });
            } else {
                $('#{$this->runtemplate->getMyKey()}_cron').after( "⚰️" );
                console.log("cron not saved", xhr.status, xhr.responseText);
                lastSavedCronValue = '';
            }
        },

        success: function(data) {
            $('#{$this->runtemplate->getMyKey()}_cron').after( "💾" );
            console.log("cron saved successfully", cronValue);
            isSaving = false;
            // Refresh CSRF token for subsequent requests
            refreshCsrfToken();
        },
            type: 'POST'
        });
}

// Debounced version of save function - prevents multiple rapid saves
function debouncedSaveCronValue() {
    // Clear any pending save
    if (cronSaveTimeout) {
        clearTimeout(cronSaveTimeout);
    }

    // Schedule a new save after a short delay
    cronSaveTimeout = setTimeout(function() {
        saveCronValue();
    }, 500); // Wait 500ms before saving
}

// Set up single event listener with debouncing
$('#{$this->runtemplate->getMyKey()}_cron').on('change input', function(e) {
    console.log('Cron component event detected:', e.type);
    debouncedSaveCronValue();
});

// Listen for custom events that the cron component might emit
$('#{$this->runtemplate->getMyKey()}_cron').on('cron-changed value-changed', function(e) {
    console.log('Cron component custom event:', e.type);
    debouncedSaveCronValue();
});

// Listen to the internal input field of the cron component when it becomes available
setTimeout(function() {
    var cronElement = $('#{$this->runtemplate->getMyKey()}_cron')[0];
    if (cronElement) {
        // Wait for the web component to be fully initialized
        var inputElement = cronElement.querySelector('.cronInsideInput');
        if (inputElement) {
            console.log('Setting up cron internal input listener');
            $(inputElement).on('input change blur', function() {
                console.log('Internal cron input changed:', this.value);
                debouncedSaveCronValue();
            });
        } else {
            // If not available yet, try again in another second
            setTimeout(arguments.callee, 1000);
        }
    }
}, 1000);

EOD);

        parent::finalize();
    }
}
