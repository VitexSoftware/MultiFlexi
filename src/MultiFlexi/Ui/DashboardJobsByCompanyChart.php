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
 * Dashboard jobs by company chart widget.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2026 Vitex Software
 */
class DashboardJobsByCompanyChart extends \Ease\Html\DivTag
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addItem(new \Ease\Html\H4Tag(_('Jobs by Company')));

        try {
            $jobber = new \MultiFlexi\Job();

            // Data pro graf - top 10 firem podle počtu jobů
            $companyJobsData = $jobber->getFluentPDO()
                ->from('job')
                ->select('company.name, COUNT(job.id) as job_count')
                ->leftJoin('company ON company.id = job.company_id')
                ->groupBy('company.id')
                ->orderBy('job_count DESC')
                ->limit(10)
                ->fetchAll();

            if (!empty($companyJobsData)) {
                $chartData = [];

                foreach ($companyJobsData as $row) {
                    $chartData[$row['name'] ?? _('Unknown')] = (int) $row['job_count'];
                }

                $graph = new \Goat1000\SVGGraph\SVGGraph(600, 400, [
                    'back_colour' => '#ffffff',
                    'stroke_colour' => '#000',
                    'back_stroke_width' => 0,
                    'back_stroke_colour' => '#eee',
                    'axis_colour' => '#333',
                    'axis_overlap' => 2,
                    'axis_font' => 'Arial',
                    'axis_font_size' => 10,
                    'grid_colour' => '#ddd',
                    'label_colour' => '#000',
                    'pad_right' => 20,
                    'pad_left' => 20,
                    'show_data_labels' => true,
                    'data_label_font_size' => 10,
                ]);

                $graph->colours(['#2ecc71', '#3498db', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c', '#34495e', '#16a085', '#27ae60', '#2980b9']);
                $graph->values($chartData);
                $this->addItem(new \Ease\Html\DivTag($graph->fetch('PieGraph'), ['class' => 'chart-container']));
            } else {
                $this->addItem(new \Ease\TWB4\Badge('info', _('No data available')));
            }
        } catch (\Exception $e) {
            $this->addItem(new \Ease\TWB4\Badge('danger', _('Error loading chart: ').$e->getMessage()));
        }
    }
}
