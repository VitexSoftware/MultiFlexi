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
    private \MultiFlexi\Job $engine;

    public function __construct(\MultiFlexi\Job $engine, $properties = [])
    {
        $allJobs = $engine->listingQuery()->select(['begin', 'exitcode'], 1)->where('begin BETWEEN (CURDATE() - INTERVAL 30 DAY) AND CURDATE()')->order('begin')->fetchAll();
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
                'date' => $date,
                'success' => $day['success'],
                'waiting' => $day['waiting'],
                'fail' => $day['fail'],
//                'all' => $day['success'] + $day['waiting'] + $day['fail']
            ];
        }

        $settings = [
            'auto_fit' => true,
            'graph_title' => _('Last 30 Days jobs'),
            'structured_data' => true,
            'legend_autohide' => true,
            'legend_draggable' => false,
            'legend_text_side' => 'left',
            'legend_entry_height' => 10,
            'legend_title' => 'Legend',
            'legend_entries' => [_('waiting'), _('fail'), _('success')],
            'structure' => [
                'key' => 'date',
                'value' => ['waiting', 'fail', 'success'],
                'tooltip' => ['waiting', 'fail', 'success'],
                'axis_text' => 3
            ]
        ];

        $colours = [['green', 'chartreuse'], ['red', 'orange'], ['#7FFF00', 'green']];

        $graph = new \Goat1000\SVGGraph\SVGGraph(1024, 512, $settings);
        $graph->values($data);
        $graph->colours($colours);

        parent::__construct($graph->fetch('StackedBarGraph', false), $properties);
//        $this->addJavaScript($graph->fetchJavascript());
    }
}
