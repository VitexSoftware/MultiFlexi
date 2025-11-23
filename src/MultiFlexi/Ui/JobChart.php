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
 * Description of JobChart.
 *
 * author vitex
 *
 * @no-named-arguments
 */
class JobChart extends \Ease\Html\DivTag
{
    protected \Ease\SQL\Engine $engine;

    public function __construct(\Ease\SQL\Engine $engine, $properties = [])
    {
        $this->engine = $engine;
        $allJobs = $this->getJobs()->fetchAll();
        $days = [];

        foreach ($allJobs as $job) {
            if (empty($job['begin'])) {
                continue;
            }

            $date = current(explode(' ', $job['begin']));
            $exitCode = $job['exitcode'];

            switch ($exitCode) {
                case 0:
                    $state = 'success';

                    break;
                case 255:
                    $state = 'exception';

                    break;
                case null:
                case -1:
                    $state = 'waiting';

                    break;

                default:
                    $state = 'fail';

                    break;
            }

            if (\array_key_exists($date, $days) === false) {
                $days[$date] = ['success' => 0, 'waiting' => 0, 'fail' => 0, 'exception' => 0];
            }

            ++$days[$date][$state];
        }

        $data = [];
        $count = 0;

        foreach ($days as $date => $day) {
            $data[] = [
                'day' => $count++,
                'date' => substr($date, 2),
                'success' => $day['success'],
                'waiting' => $day['waiting'],
                'fail' => $day['fail'],
                'exception' => $day['exception'],
            ];
        }

        $settings = [
            'auto_fit' => true,
            'graph_title' => _('Last 30 Days jobs'),
            'back_stroke_width' => 0,
            'back_stroke_colour' => '#eee',
            'structured_data' => true,
            'legend_draggable' => false,
            'legend_text_side' => 'left',
            'legend_font_size' => 6,
            'legend_entry_height' => 10,
            'legend_title' => _('Legend'),
            'legend_entries' => [_('waiting'), _('fail'), _('success'), _('exception')],
            'legend_position' => 'bottom left 3 -3',
            'legend_autohide' => true,
            'data_label_popfront' => true,
            'link_base' => './',
            'link_target' => '_top',
            'data_label_font_size' => 5,
            'data_label_back_colour_outside' => '123',
            'axis_text_angle_h' => -90,
            'subdivision_size' => 5,
            'minimum_grid_spacing' => 20,
            'show_subdivisions' => true,
            'show_grid_subdivisions' => true,
            'grid_subdivision_colour' => '#ccc',
            'show_data_labels' => true,
            'data_label_type' => [
                'box', 'linebox',
            ],
            'data_label_space' => 5,
            'data_label_min_space' => 15, // Minimum space between labels to prevent overlap
            'data_label_font_adjust' => 0.8, // Slightly smaller font for better fit
            'data_label_back_colour' => [
                '#E8F4F8', '#FFE5E5', '#E8F5E9', '#F5F5F5', 'white', 'white', 'white', 'white', 'white', 'white',
            ],
            'marker_size' => 3,
            'structure' => [
                'key' => 'date',
                'value' => ['waiting', 'fail', 'success', 'exception'],
                'tooltip' => ['waiting', 'fail', 'success', 'exception'],
                'axis_text' => 3,
            ],
        ];
        $links = ['success' => '?showonly=success', 'fail' => '?showonly=fail', 'waiting' => '?showonly=waiting', 'exception' => '?showonly=exception'];

        // Pastel colors: waiting (light blue), fail (light red), success (light green), exception (light gray)
        $colours = [
            ['#B3E5FC', '#81D4FA'], // waiting - pastel light blue
            ['#FFCDD2', '#EF9A9A'], // fail - pastel light red
            ['#C8E6C9', '#A5D6A7'], // success - pastel light green
            ['#E0E0E0', '#BDBDBD'],  // exception - pastel gray
        ];

        $graph = new \Goat1000\SVGGraph\SVGGraph(1024, 212, $settings);
        $graph->values($data);
        $graph->colours($colours);
        $graph->links($links);

        parent::__construct($graph->fetch('StackedBarGraph', false), $properties);
    }

    /**
     * @return \Envms\FluentPDO\Queries\Select
     */
    public function getJobs()
    {
        return $this->engine->getFluentPDO(true)->from('job')->select(['begin', 'exitcode'], true)->order('begin');
    }
}
