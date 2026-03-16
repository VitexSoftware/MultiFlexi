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

namespace MultiFlexi\Api\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Schedule API Handler.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ScheduleApi
{
    public $engine;

    /**
     * Schedule Handler Engine.
     */
    public function __construct()
    {
        $this->engine = new \MultiFlexi\Schedule();
        $this->engine->limit = 100;
    }

    /**
     * GET scheduleGet
     * Summary: List all Schedule entries
     * Notes: List all scheduled jobs.
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     */
    public function listSchedule(ServerRequestInterface $request, ResponseInterface $response, string $suffix): ResponseInterface
    {
        $scheduleList = [];
        $queryParams = $request->getQueryParams();
        $limit = (\array_key_exists('limit', $queryParams)) ? (int) $queryParams['limit'] : $this->engine->limit;
        $offset = (\array_key_exists('offset', $queryParams)) ? (int) $queryParams['offset'] : 0;
        $order = (\array_key_exists('order', $queryParams)) ? $queryParams['order'] : 'after';

        // Determine sort direction
        $orderField = $order;
        $orderDir = 'ASC';

        if (str_starts_with($order, '-')) {
            $orderField = substr($order, 1);
            $orderDir = 'DESC';
        }

        $query = $this->engine->listingQuery()
            ->orderBy($orderField.' '.$orderDir)
            ->limit($limit)
            ->offset($offset);

        foreach ($query as $schedule) {
            $scheduleId = $schedule['id'];

            switch ($suffix) {
                case 'html':
                    $schedule['id'] = new \Ease\Html\ATag('schedule/'.$schedule['id'].'.html', (string) $schedule['id']);

                    if ($schedule['job']) {
                        $schedule['job'] = new \Ease\Html\ATag('job/'.$schedule['job'].'.html', (string) $schedule['job']);
                    }

                    break;

                default:
                    break;
            }

            $scheduleList[$scheduleId] = $schedule;
        }

        return DefaultApi::prepareResponse($response, $scheduleList, $suffix, 'schedule', 'schedule');
    }

    /**
     * Schedule Info by ID.
     *
     * @url http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/schedule/1
     */
    public function getScheduleById(ServerRequestInterface $request, ResponseInterface $response, int $scheduleId, string $suffix): ResponseInterface
    {
        $this->engine->loadFromSQL((int) $scheduleId);

        if (!$this->engine->getMyKey()) {
            return DefaultApi::prepareResponse($response->withStatus(404), ['error' => 'Schedule not found'], $suffix);
        }

        $scheduleData = $this->engine->getData();

        switch ($suffix) {
            case 'html':
                $scheduleData['id'] = new \Ease\Html\ATag('schedule/'.$scheduleData['id'].'.html', (string) $scheduleData['id']);

                if ($scheduleData['job']) {
                    $scheduleData['job'] = new \Ease\Html\ATag('job/'.$scheduleData['job'].'.html', (string) $scheduleData['job']);
                }

                $scheduleData = [array_keys($scheduleData), $scheduleData];

                break;

            default:
                break;
        }

        return DefaultApi::prepareResponse($response, $scheduleData, $suffix, 'schedule', 'schedule');
    }
}
