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

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

require \dirname(__DIR__).'/vendor/autoload.php';

class JobOutputStreamer implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $token = $queryParams['token'] ?? '';

        if (!$this->isValidToken($token)) {
            $conn->close();

            return;
        }

        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }

    private function isValidToken($token)
    {
        // Implement your token validation logic here
        // For example, check if the token exists in the session or database
        session_start();

        return isset($_SESSION['ws_token']) && $_SESSION['ws_token'] === $token;
    }
}

$loop = Loop::get();
$socketAddress = \Ease\Shared::cfg('LIVE_OUTPUT_SOCKET', '0.0.0.0:8080');
$webSock = new SocketServer($socketAddress, [], $loop);

$server = new IoServer(
    new HttpServer(
        new WsServer(
            new JobOutputStreamer(),
        ),
    ),
    $webSock,
    $loop,
);

$server->run();
