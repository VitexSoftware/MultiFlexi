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

    public function __construct(\MultiFlexi\Job $engine, $properties = [])
    {
        $allJobs = $engine->listingQuery()->fetchAll();
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
                case -1 :
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
                'all' => $day['success'] + $day['waiting'] + $day['fail']
            ];
        }


        parent::__construct(null, $properties);

        $this->includeJavaScript('js/d3.v7.js');

        $javaScript = '

const aapl = ' . json_encode($data) . ';

  // Declare the chart dimensions and margins.
  const width = 928;
  const height = 500;
  const marginTop = 20;
  const marginRight = 30;
  const marginBottom = 30;
  const marginLeft = 40;

  // Declare the x (horizontal position) scale.
  const x = d3.scaleUtc(d3.extent(aapl, d => d.date), [marginLeft, width - marginRight]);

  // Declare the y (vertical position) scale.
  const y = d3.scaleLinear([0, d3.max(aapl, d => d.all)], [height - marginBottom, marginTop]);

  // Declare the line generator.
  const line = d3.line()
      .x(d => x(d.date))
      .y(d => y(d.all));

  // Create the SVG container.
  const svg = d3.create("svg")
      .attr("width", width)
      .attr("height", height)
      .attr("viewBox", [0, 0, width, height])
      .attr("style", "max-width: 100%; height: auto; height: intrinsic;");

  // Add the x-axis.
  svg.append("g")
      .attr("transform", `translate(0,${height - marginBottom})`)
      .call(d3.axisBottom(x).ticks(width / 80).tickSizeOuter(0));

  // Add the y-axis, remove the domain line, add grid lines and a label.
  svg.append("g")
      .attr("transform", `translate(${marginLeft},0)`)
      .call(d3.axisLeft(y).ticks(height / 40))
      .call(g => g.select(".domain").remove())
      .call(g => g.selectAll(".tick line").clone()
          .attr("x2", width - marginLeft - marginRight)
          .attr("stroke-opacity", 0.1))
      .call(g => g.append("text")
          .attr("x", -marginLeft)
          .attr("y", 10)
          .attr("fill", "currentColor")
          .attr("text-anchor", "start")
          .text("↑ Jobs"));

  // Append a path for the line.
  svg.append("path")
      .attr("fill", "none")
      .attr("stroke", "steelblue")
      .attr("stroke-width", 1.5)
      .attr("d", line(aapl));

// Append the SVG element.
container.append(svg.node());
';
        $this->webPage()->addItem(new \Ease\Html\JavaScript($javaScript, ['type' => 'module']));
    }
}
