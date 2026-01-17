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
 * Dashboard run templates by interval chart widget.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2026 Vitex Software
 */
class DashboardIntervalChart extends \Ease\Html\DivTag
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addItem(new \Ease\Html\H4Tag(_('Run Templates by Interval')));

        try {
            $runtempler = new \MultiFlexi\RunTemplate();

            // Data pro graf intervalů
            $intervalData = $runtempler->getFluentPDO()
                ->from('runtemplate')
                ->select('interv, COUNT(*) as template_count')
                ->groupBy('interv')
                ->orderBy('template_count DESC')
                ->fetchAll();

            if (!empty($intervalData)) {
                $chartData = [];
                $intervalNames = [
                    'n' => _('Disabled'),
                    'i' => _('Minutely'),
                    'h' => _('Hourly'),
                    'd' => _('Daily'),
                    'w' => _('Weekly'),
                    'm' => _('Monthly'),
                    'y' => _('Yearly'),
                ];

                foreach ($intervalData as $row) {
                    $intervalCode = $row['interv'] ?? 'n';
                    $intervalName = $intervalNames[$intervalCode] ?? $intervalCode;
                    $chartData[$intervalName] = (int) $row['template_count'];
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
                    'bar_space' => 10,
                    'show_data_labels' => true,
                    'data_label_font_size' => 11,
                ]);

                $graph->colours(['#9b59b6', '#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#1abc9c', '#34495e']);
                $graph->values($chartData);
                $this->addItem(new \Ease\Html\DivTag($graph->fetch('BarGraph'), ['class' => 'chart-container']));
            } else {
                $this->addItem(new \Ease\TWB4\Badge('info', _('No run templates available')));
            }
        } catch (\Exception $e) {
            $this->addItem(new \Ease\TWB4\Badge('danger', _('Error loading interval chart: ').$e->getMessage()));
        }
    }
}
