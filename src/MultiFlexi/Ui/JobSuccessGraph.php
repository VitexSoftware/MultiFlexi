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
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class JobSuccessGraph extends \Ease\Html\DivTag
{
    public function __construct($todaysJobs, $properties = [])
    {
        $successCount = 0;
        $failedCount = 0;
        $waitingCount = 0;
        $exceptionCount = 0;
        $noExecutableCount = 0;

        foreach ($todaysJobs as $job) {
            if ($job['exitcode'] === 0) {
                ++$successCount;
            } elseif ($job['exitcode'] === 255) {
                ++$exceptionCount;
            } elseif ($job['exitcode'] === 127) {
                ++$noExecutableCount;
            } elseif (null === $job['exitcode'] || $job['exitcode'] === -1) {
                ++$waitingCount;
            } else {
                ++$failedCount;
            }
        }

        $values = [
            _('Success') => $successCount,
            _('Failed') => $failedCount,
            _('Waiting') => $waitingCount,
            _('Exception') => $exceptionCount,
            _('No Executable') => $noExecutableCount,
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
            'label_font_size' => '8', // Smaller font size for captions
            'units_before_label' => '',
        ];

        $width = 300;
        $height = 200;
        $type = 'SemiDonutGraph';
        // Pastel colors: success (light green), failed (light red), waiting (light blue), exception (light gray), no executable (light yellow)
        $colours = ['#A5D6A7', '#FFCDD2', '#B3E5FC', '#E0E0E0', '#FFF9C4'];

        $graph = new SVGGraph($width, $height, $settings);
        $graph->colours($colours);
        $graph->values($values);

        parent::__construct($graph->fetch($type), $properties);
    }
}
