<?php
/**
 * Multi Flexi - API Base
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022-2023 Vitex Software
 */

namespace MultiFlexi\Api;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of DefaultApi
 *
 * @author vitex
 */
class ServerApi extends AbstractServerApi
{
    /**
     * Api Handler Engine
     * @var \MultiFlexi\Servers
     */
    public $engine = null;

    /**
     * Prepare Servers engine
     */
    public function __construct()
    {
        $this->engine = new \MultiFlexi\Servers();
    }

    /**
     * Server info by ID
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/server/1
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $serverId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getServerById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, int $serverId, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $this->engine->loadFromSQL($serverId);
        return DefaultApi::prepareResponse($response, ['id' => $this->engine->getMyKey(), 'name' => $this->engine->getRecordName(), 'executable' => $this->engine->getDataValue('executable')], $suffix);
    }

    /**
     * GET listServers
     * Summary: Show All Servers
     * Notes: All Server servers registered
     * Output-Formats: [application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     */
    public function listServers(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $serversList = [];
        foreach ($this->engine->getAll() as $server) {
            $serversList[] = [
                'id' => $server['id'],
                'name' => $server['name'],
                'type' => $server['type'],
                'url' => $server['url'],
            ];
        }
        $response->getBody()->write(json_encode($serversList, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * POST setServerById
     * Summary: Create or Update Server record
     * Notes: Create or Upda single Server record
     * Output-Formats: [application/xml, application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     */
    public function setServerById(
            ServerRequestInterface $request,
            ResponseInterface $response
    ): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        if (key_exists('serverId', $queryParams)) {
            $this->engine->setMyKey($queryParams['serverId']);
            $this->engine->updateToSQL($queryParams);
        } else {
            $this->engine->insertToSQL($queryParams);
        }
        return $response;
    }
}
