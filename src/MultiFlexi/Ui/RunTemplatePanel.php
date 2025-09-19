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

        $runtemplateBottom = new \Ease\TWB4\Row();

        if ($runtemplate->getMyKey()) {
            $runtemplateBottom->addColumn(4, new RuntemplateCloneForm($runtemplate));
            $runtemplateBottom->addColumn(4, $deleteButton);
            $runtemplateBottom->addColumn(4, new RuntemplatePopulateForm($runtemplate));
        }

        parent::__construct([new \Ease\Html\ATag('companyapp.php?app_id='.$runtemplate->getDataValue('app_id').'&company_id='.$runtemplate->getDataValue('company_id'), '<span style="font-size: xxx-large;">⚗️ </span>'), $nameInput], 'default', $runtemplateOptions, $runtemplateBottom);
        $this->includeJavaScript('js/bootstrap-editable.js');
        $this->includeCss('css/bootstrap-editable.css');
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
        $this->addJavaScript("$('.editable').editable();", '', true);

        $runtemplateTabs = new \Ease\TWB4\Tabs();
        $runtemplateTabs->addTab(_('Jobs'), [$runtemplateJobs, new RunTemplateJobsLastMonthChart($runtemplate)]);
        $runtemplateTabs->addTab(_('Options'), [new RuntemplateConfigForm($runtemplate)]);
        $runtemplateTabs->addTab(_('Actions'), [new \MultiFlexi\Ui\ActionsTab($runtemplate)]);
        $runtemplateTabs->addTab(_('Environment'), [new EnvironmentView($runtemplate->credentialsEnvironment()), new RunTemplateDotEnv($runtemplate)]);

        $this->addItem($runtemplateTabs);
    }

    /**
     * Finalizes the panel by adding JavaScript and Bootstrap integration.
     */
    public function finalize(): void
    {
        \Ease\TWB4\Part::twBootstrapize();
        $this->addJavaScript(<<<'EOD'


$('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_interval').change( function(event, state) {

    $.ajax({
        url: 'rtinterval.php',
        data: {
            runtemplate: $(this).attr("data-runtemplate"),
                interval: $(this).val()
        },
        error: function() {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_interval').after( "⚰️" );
            console.log("not saved");
        },

        success: function(data) {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_interval').after( "💾" );
            console.log("saved");
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<'EOD'

$('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_delay').change( function(event, state) {

$.ajax({
   url: 'rtdelay.php',
        data: {
                runtemplate: $(this).attr("data-runtemplate"),
                delay: $(this).val()
        },
        error: function() {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_delay').after( "⚰️" );
            console.log("not saved");
        },

        success: function(data) {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_delay').after( "💾" );
            console.log("saved");
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<'EOD'

$('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_executor').change( function(event, state) {

$.ajax({
   url: 'rtexecutor.php',
        data: {
                runtemplate: $(this).attr("data-runtemplate"),
                executor: $(this).val()
        },
        error: function() {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_executor').after( "⚰️" );
            console.log("not saved");
        },

        success: function(data) {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_executor').after( "💾" );
            console.log("saved");
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
                active: $(this).val()
        },
        error: function() {
            $('#deactivated').before( "⚰️" );
            console.log("not saved");
        },

        success: function(data) {
            $('#deactivated').before( "💾" );
            console.log("saved");
        },
            type: 'POST'
        });
});

EOD);

        $this->addJavaScript(<<<'EOD'


$('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_cron').change( function(event, state) {

    $.ajax({
        url: 'rtcron.php',
        data: {
            runtemplate: $(this).attr("data-runtemplate"),
                cron: $(".cronInsideInput").val()
        },
        error: function() {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_cron').after( "⚰️" );
            console.log("not saved");
        },

        success: function(data) {
            $('#
EOD.$this->runtemplate->getMyKey().<<<'EOD'
_cron').after( "💾" );
            console.log("saved");
        },
            type: 'POST'
        });
});

EOD);

        parent::finalize();
    }
}
