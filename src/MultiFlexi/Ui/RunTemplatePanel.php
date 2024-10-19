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
        $runtemplateOptions->addColumn(6, new RuntemplateConfigForm($runtemplate));
        $intervalChoosen = $runtemplate->getDataValue('interv');
        $intervalChooser = new \MultiFlexi\Ui\IntervalChooser($runtemplateId.'_interval', $intervalChoosen, ['id' => $runtemplateId.'_interval', 'checked' => 'true', 'data-runtemplate' => $runtemplateId]);

        $scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runtemplateId, [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');

        if (WebPage::getRequestValue('delete', 'int') === 1) {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=2&id='.$runtemplateId, _('Delete !!!').'&nbsp;&nbsp;❌', 'danger btn-lg');
        } else {
            $deleteButton = new \Ease\TWB4\LinkButton('runtemplate.php?delete=1&id='.$runtemplateId, _('Delete ?').'&nbsp;&nbsp;❌', 'warning btn-lg');
        }

        $runtemplateJobs = new \MultiFlexi\Ui\RuntemplateJobsListing($runtemplate);

        $runtemplateOptions->addColumn(6, [$intervalChooser, $scheduleButton, $runtemplateJobs, $deleteButton]);
        $nameInput = new \Ease\Html\ATag('#', $runtemplate->getRecordName(), ['class' => 'editable', 'id' => 'name', 'data-pk' => $runtemplate->getMyKey(), 'data-url' => 'runtemplatesave.php', 'data-title' => _('Update RunTemplate name')]);
        parent::__construct([new \Ease\Html\ATag('companyapp.php?app_id='.$runtemplate->getDataValue('app_id').'&company_id='.$runtemplate->getDataValue('company_id'), '⚗️ '), $nameInput], 'inverse', $runtemplateOptions, new RuntemplateCloneForm($runtemplate));
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
        parent::finalize();
    }
}
