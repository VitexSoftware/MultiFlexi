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

use Goat1000\SVGGraph\SVGGraph;

/**
 * Description of TodaysJobSuccesGraph.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class JobSuccessGraph extends \Ease\Html\DivTag
{
    public function __construct($todaysJobs, $properties = [])
    {
        $successCount = 0;
        $failedCount = 0;

        foreach ($todaysJobs as $job) {
            if ($job['exitcode'] === 0) {
                ++$successCount;
            } else {
                ++$failedCount;
            }
        }

        $values = [
            _('Success') => $successCount,
            _('Failed') => $failedCount,
        ];

        $settings = [
            'auto_fit' => true,
            'back_colour' => '#fff',
            'back_stroke_width' => 0,
            'back_stroke_colour' => '#eee',
            'stroke_colour' => '#000',
            'label_colour' => '#0f0',
            'pad_right' => 20,
            'pad_left' => 20,
            'link_base' => '/',
            'link_target' => '_top',
            'sort' => false,
            'show_labels' => true,
            'show_label_amount' => true,
            'label_font' => 'Arial',
            'label_font_size' => '11',
            'units_before_label' => '',
        ];

        $width = 300;
        $height = 200;
        $type = 'SemiDonutGraph';
        $colours = ['#286d14', '#a11b1b', '#181691'];

        $graph = new SVGGraph($width, $height, $settings);
        $graph->colours($colours);
        $graph->values($values);

        parent::__construct($graph->fetch($type), $properties);
    }
}
