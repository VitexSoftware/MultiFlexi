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
 * Job Graph Widget Component
 *
 * Displays a visual grid of recent job exit codes for a RunTemplate
 *
 * @author vitex
 */
class JobGraphWidget extends \Ease\Html\DivTag
{
    private \MultiFlexi\RunTemplate $runtemplate;
    private int $width;
    private int $height;

    /**
     * Constructor
     *
     * @param \MultiFlexi\RunTemplate $runtemplate RunTemplate instance
     * @param int $width Grid width in cells (default: 20)
     * @param int $height Grid height in cells (default: 10)
     * @param array $properties HTML element properties
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate, int $width = 20, int $height = 10, array $properties = [])
    {
        $this->runtemplate = $runtemplate;
        $this->width = $width;
        $this->height = $height;
        
        parent::__construct(null, $properties);
        
        $this->addItem(new \Ease\Html\H4Tag(_('Recent Jobs Visualization'), ['class' => 'mb-2']));
        
        // Create image tag pointing to jobgraph.php
        $graphUrl = 'jobgraph.php?runtemplate_id=' . $runtemplate->getMyKey() 
                  . '&width=' . $width 
                  . '&height=' . $height;
        
        $imgTag = new \Ease\Html\ImgTag(
            $graphUrl, 
            _('Job History Graph'),
            [
                'class' => 'img-fluid border',
                'style' => 'max-width: 100%; height: auto;',
                'title' => _('Visual representation of recent job executions. Each cell represents a job, colored by exit code.')
            ]
        );
        
        $this->addItem($imgTag);
        
        // Add legend
        $this->addItem($this->createLegend());
    }
    
    /**
     * Create legend explaining the color codes
     *
     * @return \Ease\Html\DivTag
     */
    private function createLegend(): \Ease\Html\DivTag
    {
        $legend = new \Ease\Html\DivTag(null, ['class' => 'mt-2 small text-muted']);
        
        $legend->addItem(new \Ease\Html\StrongTag(_('Legend') . ': '));
        $legend->addItem(new \Ease\Html\SpanTag('🟩 ' . _('Success (0)'), ['class' => 'mr-2']));
        $legend->addItem(new \Ease\Html\SpanTag('🟥 ' . _('Failed'), ['class' => 'mr-2']));
        $legend->addItem(new \Ease\Html\SpanTag('⬜ ' . _('Not executed'), ['class' => 'mr-2']));
        
        return $legend;
    }
}
