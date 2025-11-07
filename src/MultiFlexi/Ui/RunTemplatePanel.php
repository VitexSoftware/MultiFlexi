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
        $crontabInput = new \MultiFlexi\Ui\CrontabInput($runtemplateId.'_cron', $crontab, ['data-runtemplate' => $runtemplateId]);

        $delayChooser = new \MultiFlexi\Ui\DelayChooser($runtemplateId.'_delay', $delayChoosen, ['id' => $runtemplateId.'_delay', 'checked' => 'true', 'data-runtemplate' => $runtemplateId]);
        $executorChooser = new AppExecutorSelect($runtemplate->getApplication(), [], (string) $runtemplate->getDataValue('executor'), ['id' => $runtemplateId.'_executor', 'data-runtemplate' => $runtemplateId]);

        $scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runtemplateId, [_('Schedule Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/multiflexi-schedule.svg', _('Launch'), ['height' => '30px'])], 'secondary btn-lg');
        $launchButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runtemplateId.'&when=now&executor=Native', [_('Launch now').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/multiflexi-execute.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');
        $runtemplateOptions->addColumn(4, [_('Status'), new \Ease\TWB4\Widgets\Toggle('active', $runtemplate->getDataValue('active') ? true : false, $runtemplate->getDataValue('active') ? 'false' : 'true', ['title' => $runtemplate->getDataValue('active') ? _('Enabled') : _('Disabled'), 'data-runtemplate' => $runtemplateId, 'id' => 'enabler', 'data-on' => _('Enabled'), 'data-off' => _('Disabled')]), new \Ease\Html\SpanTag('', ['id' => 'deactivated'])]);
        $runtemplateOptions->addColumn(4, [$launchButton, $scheduleButton, '<br>', empty($runtemplate->getDataValue('last_schedule')) ? _('It has never been planned before') : _('Last schedule').' '.$runtemplate->getDataValue('last_schedule').'&nbsp;('.(new \Ease\Html\Widgets\LiveAge(new \DateTime($runtemplate->getDataValue('last_schedule')))).' )']);

        if (WebPage::getRequestValue('delete', 'int') === 1) {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=2&id='.$runtemplateId, _('Delete !!!').'&nbsp;&nbsp;❌', 'danger btn-lg');
        } else {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=1&id='.$runtemplateId, _('Delete ?').'&nbsp;&nbsp;❌', 'warning btn-lg');
        }

        $runtemplateJobs = new \MultiFlexi\Ui\RuntemplateJobsListing($runtemplate);

        $runtemplateOptions->addColumn(4, [_('automatically schedule in an interval').': ', $crontabInput, '<br/>', $intervalChooser, '<br/>', _('Startup delay'), $delayChooser, '<br/>', _('Executor'), $executorChooser]);
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
        $runtemplateTabs->addTab(_('Jobs'), [$runtemplateJobs, new RunTemplateJobsLastMonthChart($runtemplate)]);
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

    $.ajax({
        url: 'rtinterval.php',
        data: {
            runtemplate: $(this).attr("data-runtemplate"),
            interval: $(this).val(),
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

        $this->addJavaScript(<<<EOD

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


$('#{$this->runtemplate->getMyKey()}_cron').change( function(event, state) {

    $.ajax({
        url: 'rtcron.php',
        data: {
            runtemplate: $(this).attr("data-runtemplate"),
            cron: $(".cronInsideInput").val(),
            csrf_token: currentCsrfToken
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 400) {
                // CSRF token might be expired, try to refresh and retry
                refreshCsrfToken().then(function(newToken) {
                    $.ajax({
                        url: 'rtcron.php',
                        data: {
                            runtemplate: $('#{$this->runtemplate->getMyKey()}_cron').attr("data-runtemplate"),
                            cron: $(".cronInsideInput").val(),
                            csrf_token: newToken
                        },
                        success: function(data) {
                            $('#{$this->runtemplate->getMyKey()}_cron').after( "💾" );
                            console.log("saved after retry");
                        },
                        error: function() {
                            $('#{$this->runtemplate->getMyKey()}_cron').after( "⚰️" );
                            console.log("not saved even after retry");
                        },
                        type: 'POST'
                    });
                }).catch(function() {
                    $('#{$this->runtemplate->getMyKey()}_cron').after( "⚰️" );
                    console.log("CSRF token refresh failed");
                });
            } else {
                $('#{$this->runtemplate->getMyKey()}_cron').after( "⚰️" );
                console.log("not saved");
            }
        },

        success: function(data) {
            $('#{$this->runtemplate->getMyKey()}_cron').after( "💾" );
            console.log("saved");
            // Refresh CSRF token for subsequent requests
            refreshCsrfToken();
        },
            type: 'POST'
        });
});

EOD);

        parent::finalize();
    }
}
