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

namespace MultiFlexi\Zabbix;

use MultiFlexi\Zabbix\Exception\ZabbixNetworkException;
use MultiFlexi\Zabbix\Exception\ZabbixResponseException;
use MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;
use MultiFlexi\Zabbix\Response as ZabbixResponse;

/**
 * @no-named-arguments
 */
class ZabbixSender
{
    /**
     *  Zabbix protocol header.
     *
     * @var string
     */
    private const HEADER = 'ZBXD';

    /**
     *  Zabbix protocol version.
     *
     * @var int
     */
    private const VERSION = 1;

    /**
     * Zabbix server response header length
     * https://www.zabbix.com/documentation/3.4/manual/appendix/protocols/header_datalen.
     *
     * @var int
     */
    private const RESPONSE_HEADER_LENGTH = 13;

    /**
     * Instance instances array.
     */
    protected static array $instances = [];
    private string $serverAddress;
    private int $serverPort;
    private ZabbixPacket $packet;

    /**
     * @var bool Disable send operation
     */
    private bool $disable = false;

    public function __construct(
        string $serverAddress,
        int $serverPort = 10051,
    ) {
        $this->serverAddress = $serverAddress;
        $this->serverPort = $serverPort;
    }

    /**
     * Create singletone object.
     *
     * @param string $name Name of object
     *
     * @return ZabbixSender instance
     */
    public static function instance($name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new static($name);
        }

        return self::$instances[$name];
    }

    /**
     * Configure connection parameters to Zabbix server.
     *
     * @param array $options Configuration options
     *
     * @return ZabbixSender Configurated instance
     */
    public function configure(array $options = [])
    {
        if (isset($options['server_address'])) {
            $this->serverAddress = $options['server_address'];
        }

        if (isset($options['server_port'])) {
            $this->serverPort = (int) $options['server_port'];
        }

        if (isset($options['disable'])) {
            $this->disable = (bool) $options['disable'];
        }

        return $this;
    }

    /**
     * Disable sender functionality. It may be necessary if you want
     * switch off send metrics but you don't want remove the code
     * from your project.
     */
    public function disable(): void
    {
        $this->disable = true;
    }

    /**
     * Enable sender functionality. This is reverse operation of `disable()`.
     */
    public function enable(): void
    {
        $this->disable = false;
    }

    /**
     * Send packet of metrics to Zabbix server through network socket.
     *
     * @throws Exception
     * @throws ZabbixNetworkException
     */
    public function send(ZabbixPacket $packet): bool
    {
        if ($this->disable) {
            return false;
        }

        $payload = self::makePayload($packet);
        $payloadLength = \strlen($payload);

        $socket = socket_create(\AF_INET, \SOCK_STREAM, \SOL_TCP);

        if (!$socket) {
            throw new \Exception("can't create TCP socket");
        }

        $socketConnected = socket_connect(
            $socket,
            $this->serverAddress,
            $this->serverPort,
        );

        if (!$socketConnected) {
            throw new ZabbixNetworkException(
                sprintf(
                    "can't connect to %s:%d",
                    $this->serverAddress,
                    $this->serverPort,
                ),
            );
        }

        $bytesCount = socket_send(
            $socket,
            $payload,
            $payloadLength,
            0,
        );

        switch (true) {
            case !$bytesCount:
                throw new ZabbixNetworkException(
                    sprintf(
                        "can't send %d bytes to zabbix server %s:%d",
                        $payloadLength,
                        $this->serverAddress,
                        $this->serverPort,
                    ),
                );

            case $bytesCount !== $payloadLength:
                throw new ZabbixNetworkException(
                    sprintf(
                        'incorrect count of bytes %s sended, expected: %d',
                        $bytesCount,
                        $payloadLength,
                    ),
                );

            default:
                break;
        }

        return self::checkResponse($socket);
    }

    /**
     * Make payload for Zabbix server with special Zabbix header
     * and datalen.
     *
     * https://www.zabbix.com/documentation/3.4/manual/appendix/protocols/header_datalen
     */
    private static function makePayload(ZabbixPacket $packet): string
    {
        $encodedPacket = json_encode($packet);

        return pack(
            'a4CPa*',
            self::HEADER,
            self::VERSION,
            \strlen($encodedPacket),
            $encodedPacket,
        );
    }

    /**
     * Check response from Zabbix server.
     *
     * @param resource $socket
     *
     * @throws ZabbixNetworkException
     * @throws ZabbixResponseException
     */
    private static function checkResponse($socket): bool
    {
        $responseBuffer = '';
        $responseBufferLength = 2048;

        $bytesCount = socket_recv(
            $socket,
            $responseBuffer,
            $responseBufferLength,
            0,
        );

        if (!$bytesCount) {
            throw new ZabbixNetworkException(
                "can't receive response from socket",
            );
        }

        $rspnsWithoutHeader = substr(
            $responseBuffer,
            self::RESPONSE_HEADER_LENGTH,
        );
        $response = json_decode(
            $rspnsWithoutHeader,
            true,
        );

        switch (true) {
            case $response === null:
            case $response === false:
                throw new ZabbixResponseException(
                    sprintf(
                        "can't decode zabbix server response %s, reason: %s",
                        $rspnsWithoutHeader,
                        json_last_error_msg(),
                    ),
                );

            default:
                break;
        }

        $zabbixResponse = new ZabbixResponse($response);

        if (!$zabbixResponse->isSuccess()) {
            throw new ZabbixResponseException(
                'zabbix server returned non-successfull response',
            );
        }

        return true;
    }
}
