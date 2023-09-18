<?php

declare(strict_types=1);
/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

use \MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;
use \MultiFlexi\Zabbix\Response as ZabbixResponse;
use \MultiFlexi\Zabbix\Exception\ZabbixNetworkException;
use \MultiFlexi\Zabbix\Exception\ZabbixResponseException;

/**
 * Description of ZabbixSender
 *
 * @author vitex
 */
class ZabbixSender extends \MultiFlexi\Zabbix\ZabbixSender
{

    /**
     * Instance instances array 
     *
     * @var array 
     */
    protected static $instances = array();

    /**
     *  Zabbix protocol header
     *
     *  @var string
     */
    private const HEADER = 'ZBXD';

    /**
     *  Zabbix protocol version
     *
     *  @var int
     */
    private const VERSION = 1;

    /**
     * Zabbix server response header length
     * https://www.zabbix.com/documentation/3.4/manual/appendix/protocols/header_datalen
     *
     * @var int
     */
    private const RESPONSE_HEADER_LENGTH = 13;

    /**
     *  @var string
     */
    private $serverAddress;

    /**
     *  @var int
     */
    private $serverPort;

    /**
     * @var ZabbixPacket
     */
    private $packet;

    /**
     * @var bool Disable send operation
     */
    private $disable = false;

    public function __construct(
            string $serverAddress,
            int $serverPort = 10051
    )
    {
        $this->serverAddress = $serverAddress;
        $this->serverPort = $serverPort;
    }

    /**
     * Make payload for Zabbix server with special Zabbix header
     * and datalen
     *
     * https://www.zabbix.com/documentation/current/en/manual/appendix/protocols/header_datalen
     */
    public function preparePayload(ZabbixPacket $packet): string
    {
        $encodedPacket = json_encode($packet);
        return self::zbxCreateHeader(strlen($encodedPacket)) . $encodedPacket;
    }

    /**
     * Zabbix Packet Header 
     *  
     * @param int $plain_data_size
     * @param int $compressed_data_size
     * 
     * @return string
     */
    public static function zbxCreateHeader($plain_data_size, $compressed_data_size = null)
    {
        $flags = self::VERSION;
        if (is_null($compressed_data_size)) {
            $datalen = $plain_data_size;
            $reserved = 0;
        } else {
            $flags |= 0x02;
            $datalen = $compressed_data_size;
            $reserved = $plain_data_size;
        }
        return self::HEADER . chr($flags) . pack("VV", $datalen, $reserved);
    }

    /**
     * Send packet of metrics to Zabbix server through network socket
     *
     *
     * @param ZabbixPacket $packet
     *
     * @return void
     *
     * @throws \Exception
     * @throws ZabbixNetworkException
     */
    public function send(ZabbixPacket $packet)
    {
        if ($this->disable) {
            return;
        }

        $payload = $this->preparePayload($packet);
        $payloadLength = strlen($payload);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            throw new \Exception("can't create TCP socket");
        }

        $socketConnected = socket_connect(
                $socket,
                $this->serverAddress,
                $this->serverPort
        );
        if (!$socketConnected) {
            throw new ZabbixNetworkException(
                            sprintf(
                                    "can't connect to %s:%d",
                                    $this->serverAddress,
                                    $this->serverPort
                            )
            );
        }

        $bytesCount = socket_send(
                $socket,
                $payload,
                $payloadLength,
                0
        );
        switch (true) {
            case!$bytesCount:
                throw new ZabbixNetworkException(
                                sprintf(
                                        "can't send %d bytes to zabbix server %s:%d",
                                        $payloadLength,
                                        $this->serverAddress,
                                        $this->serverPort
                                )
                );
            case $bytesCount != $payloadLength:
                throw new ZabbixNetworkException(
                                sprintf(
                                        "incorrect count of bytes %s sended, expected: %d",
                                        $bytesCount,
                                        $payloadLength
                                )
                );
            default:
                break;
        }

        $this->checkResponse($socket);
    }

    /**
     * Check response from Zabbix server
     *
     * @param resource $socket
     *
     * @return void
     *
     * @throws ZabbixResponseException
     * @throws ZabbixNetworkException
     */
    private function checkResponse($socket)
    {
        $responseBuffer = "";
        $responseBufferLength = 2048;
        $bytesCount = socket_recv(
                $socket,
                $responseBuffer,
                $responseBufferLength,
                0
        );
        if (!$bytesCount) {
            throw new ZabbixNetworkException(
                            "can't receive response from socket"
            );
        }

        $responseWithoutHeader = substr(
                $responseBuffer,
                self::RESPONSE_HEADER_LENGTH
        );
        $response = json_decode(
                $responseWithoutHeader,
                true
        );
        switch (true) {
            case $response === null:
            case $response === false:
                throw new ZabbixResponseException(
                                sprintf(
                                        "can't decode zabbix server response %s, reason: %s",
                                        $responseWithoutHeader,
                                        json_last_error_msg()
                                )
                );
            default:
                break;
        }

        $zabbixResponse = new ZabbixResponse($response);
        if (!$zabbixResponse->isSuccess()) {
            throw new ZabbixResponseException(
                            'zabbix server returned non-successfull response'
            );
        }
    }
}
