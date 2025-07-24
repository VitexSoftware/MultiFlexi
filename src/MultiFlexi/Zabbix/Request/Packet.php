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

namespace MultiFlexi\Zabbix\Request;

use MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;

/**
 * Packet class - represents a set of Metrics.
 *
 * @no-named-arguments
 */
class Packet implements \JsonSerializable
{
    private array $packet = [];

    public function __construct(string $request = 'sender data')
    {
        $this->packet['request'] = $request;
    }

    public function addMetric(ZabbixMetric $metric): void
    {
        $this->packet['data'][] = $metric;
    }

    public function getPacket(): array
    {
        return $this->packet;
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->packet;
    }
}
