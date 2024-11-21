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
 */
class RunTemplatePanel extends \Ease\TWB4\Panel
{
    private \MultiFlexi\RunTemplate $runtemplate;

    /**
     * Run Template Configuration panel.
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate)
    {
        $this->runtemplate = $runtemplate;
        $runtemplateId = $runtemplate->getMyKey();
        $runtemplateOptions = new \Ease\TWB4\Row();
        $intervalChoosen = $runtemplate->getDataValue('interv') ?? 'n';
        $delayChoosen = $runtemplate->getDataValue('delay') ?? '0';
        $intervalChooser = new \MultiFlexi\Ui\IntervalChooser($runtemplateId.'_interval', $intervalChoosen, ['id' => $runtemplateId.'_interval', 'checked' => 'true', 'data-runtemplate' => $runtemplateId]);
        $delayChooser = new \MultiFlexi\Ui\DelayChooser($runtemplateId.'_delay', $delayChoosen, ['id' => $runtemplateId.'_delay', 'checked' => 'true', 'data-runtemplate' => $runtemplateId]);

        $scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runtemplateId, [_('Manual Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');
        $runtemplateOptions->addColumn(4, '');
        $runtemplateOptions->addColumn(4, $scheduleButton);

        if (WebPage::getRequestValue('delete', 'int') === 1) {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=2&id='.$runtemplateId, _('Delete !!!').'&nbsp;&nbsp;❌', 'danger btn-lg');
        } else {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=1&id='.$runtemplateId, _('Delete ?').'&nbsp;&nbsp;❌', 'warning btn-lg');
        }

        $runtemplateJobs = new \MultiFlexi\Ui\RuntemplateJobsListing($runtemplate);

        $runtemplateOptions->addColumn(4, [_('automatically schedule in an interval').': ', $intervalChooser, '<br/>', _('Startup delay'), $delayChooser]);
        $nameInput = new \Ease\Html\ATag('#', $runtemplate->getRecordName(), ['class' => 'editable', 'style' => 'font-size: xxx-large;', 'id' => 'name', 'data-pk' => $runtemplate->getMyKey(), 'data-url' => 'runtemplatesave.php', 'data-title' => _('Update RunTemplate name')]);

        $runtemplateBottom = new \Ease\TWB4\Row();

        if ($runtemplate->getMyKey()) {
            $runtemplateBottom->addColumn(6, new RuntemplateCloneForm($runtemplate));
            $runtemplateBottom->addColumn(6, $deleteButton);
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
        $this->addJavaScript("$('.editable').editable();", null, true);

        $runtemplateTabs = new \Ease\TWB4\Tabs();
        $runtemplateTabs->addTab(_('Jobs'), [$runtemplateJobs]);
        $runtemplateTabs->addTab(_('Options'), [new RuntemplateConfigForm($runtemplate)]);

        $this->addItem($runtemplateTabs);
    }

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

        parent::finalize();
    }
}
