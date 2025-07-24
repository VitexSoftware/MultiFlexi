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

/**
 * Metric class - represents a Zabbix item (key and value).
 *
 * @no-named-arguments
 */
class Metric implements \JsonSerializable
{
    private string $itemKey;
    private string $itemValue;
    private string $hostname;
    private int $timestamp;

    public function __construct(
        string $itemKey,
        string $itemValue,
    ) {
        $this->itemKey = $itemKey;
        $this->itemValue = $itemValue;
        $this->hostname = gethostname();
        $this->timestamp = time();
    }

    /**
     * Add custom HostName to metric.
     */
    public function withHostname(string $hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Add custom timestamp to metric.
     */
    public function withTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'host' => $this->hostname,
            'key' => $this->itemKey,
            'value' => $this->itemValue,
            'clock' => $this->timestamp,
        ];
    }
}
