<?php

/**
 * Multi Flexi - Job Chart
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of JobChart
 *
 * @author vitex
 */
class JobChart extends \Ease\Html\DivTag
{

    private $properties;
    protected \MultiFlexi\Job $engine;

    public function __construct(\MultiFlexi\Job $engine, $properties = [])
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
                case -1:
                    $state = 'waiting';
                    break;
                default:
                    $state = 'fail';
                    break;
            }
            if (array_key_exists($date, $days) === false) {
                $days[$date] = ['success' => 0, 'waiting' => 0, 'fail' => 0];
            }
            $days[$date][$state] += 1;
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
//                'all' => $day['success'] + $day['waiting'] + $day['fail']
            ];
        }

        $settings = [
            'auto_fit' => true,
            'graph_title' => _('Last 30 Days jobs'),
            'back_stroke_width' => 0,
            'back_stroke_colour' => '#eee',
            'structured_data' => true,
            'legend_autohide' => true,
            'legend_draggable' => false,
            'legend_text_side' => 'left',
            'legend_font_size' => 6,
            'legend_entry_height' => 10,
            'legend_title' => 'Legend',
            'legend_entries' => [_('waiting'), _('fail'), _('success')],
            'link_base' => './',
            'link_target' => '_top',
            'data_label_font_size' => 5,
            'axis_text_angle_h' => -90,
            'subdivision_size' => 5,
            'minimum_grid_spacing' => 20,
            'show_subdivisions' => true,
            'show_grid_subdivisions' => true,
            'grid_subdivision_colour' => '#ccc',
            'show_data_labels' => true,
            'data_label_type' => [
                'box', 'linebox'
//                'box','plain', 'bubble', 'line', 'circle', 'square', 'linecircle','linebox', 'linesquare', 'line2'
            ],
            'data_label_space' => 5,
            'data_label_back_colour' => [
                'lightblue','#FF4500', "lightgreen", null, null, null, null, null, null, null
            ],
            'marker_size' => 3,
            'structure' => [
                'key' => 'date',
                'value' => ['waiting', 'fail', 'success'],
                'tooltip' => ['waiting', 'fail', 'success'],
                'axis_text' => 3
            ]
        ];
        $links = ['success' => '?showonly=success', 'fail' => '?showonly=fail', 'waiting' => '?showonly=waiting'];

        $colours = [['lightblue', 'blue'], ['red', 'orange'], ['green', 'chartreuse']];

        $graph = new \Goat1000\SVGGraph\SVGGraph(1024, 212, $settings);
        $graph->values($data);
        $graph->colours($colours);
        $graph->links($links);

        parent::__construct($graph->fetch('StackedBarGraph', false), $properties);
//        $this->addJavaScript($graph->fetchJavascript());
    }

    public function getJobs()
    {
        return $this->engine->listingQuery()->select(['begin', 'exitcode'], 1)->order('begin');
    }
}