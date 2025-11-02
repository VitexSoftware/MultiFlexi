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
 * Dashboard timeline chart widget.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2024 Vitex Software
 */
class DashboardTimelineChart extends \Ease\Html\DivTag
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addItem(new \Ease\Html\H4Tag(_('Jobs Execution Timeline (Last 7 Days)')));

        try {
            $jobber = new \MultiFlexi\Job();

            // Data pro timeline - joby za posledních 7 dní
            $sevenDaysAgo = (new \DateTime())->modify('-7 days')->format('Y-m-d');

            $timelineData = $jobber->getFluentPDO()
                ->from('job')
                ->select('DATE(begin) as job_date, COUNT(*) as total_jobs, SUM(CASE WHEN exitcode = 0 THEN 1 ELSE 0 END) as success_jobs, SUM(CASE WHEN exitcode != 0 AND exitcode IS NOT NULL THEN 1 ELSE 0 END) as failed_jobs')
                ->where('begin >= ?', $sevenDaysAgo)
                ->where('begin IS NOT NULL')
                ->groupBy('DATE(begin)')
                ->orderBy('job_date ASC')
                ->fetchAll();

            if (!empty($timelineData)) {
                $dates = [];
                $successData = [];
                $failData = [];

                foreach ($timelineData as $row) {
                    $dates[] = $row['job_date'];
                    $successData[] = (int) $row['success_jobs'];
                    $failData[] = (int) $row['failed_jobs'];
                }

                $graph = new \Goat1000\SVGGraph\SVGGraph(1200, 400, [
                    'back_colour' => '#ffffff',
                    'stroke_colour' => '#000',
                    'back_stroke_width' => 0,
                    'back_stroke_colour' => '#eee',
                    'axis_colour' => '#333',
                    'axis_overlap' => 2,
                    'axis_font' => 'Arial',
                    'axis_font_size' => 11,
                    'grid_colour' => '#ddd',
                    'label_colour' => '#000',
                    'pad_right' => 20,
                    'pad_left' => 50,
                    'pad_bottom' => 30,
                    'show_data_labels' => true,
                    'data_label_font_size' => 10,
                    'legend_entries' => [_('Successful'), _('Failed')],
                    'legend_font_size' => 12,
                    'legend_position' => 'top right 10 10',
                    'legend_colour' => '#000',
                ]);

                $graph->colours(['#2ecc71', '#e74c3c']);
                $graph->values(['success' => array_combine($dates, $successData), 'fail' => array_combine($dates, $failData)]);
                $this->addItem(new \Ease\Html\DivTag($graph->fetch('MultiLineGraph'), ['class' => 'chart-container']));
            } else {
                $this->addItem(new \Ease\TWB4\Badge('info', _('No execution data for the last 7 days')));
            }
        } catch (\Exception $e) {
            $this->addItem(new \Ease\TWB4\Badge('danger', _('Error loading timeline: ').$e->getMessage()));
        }
    }
}
