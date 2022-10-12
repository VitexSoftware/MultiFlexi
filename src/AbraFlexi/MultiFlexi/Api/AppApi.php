<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Api;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of DefaultApi
 * 
 * @author vitex
 */
class AppApi extends AbstractAppApi {

    public $engine = null;

    /**
     * App Handler Engine
     */
    public function __construct() {
        $this->engine = new \AbraFlexi\MultiFlexi\Application();
    }

    /**
     * App Info by ID
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/app/1
     *      
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $appId
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAppById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, int $appId): \Psr\Http\Message\ResponseInterface {
        $this->engine->loadFromSQL($appId);
        $response->getBody()->write(
                json_encode(['id' => $this->engine->getMyKey(), 'name' => $this->engine->getRecordName(), 'executable' => $this->engine->getDataValue('executable')], JSON_UNESCAPED_UNICODE)
        );
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * All Apps 
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/apps
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listApps(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface {
        $apps = new \AbraFlexi\MultiFlexi\Application();
        foreach ($apps->getAll() as $app) {
            $appsList[] = ['id' => $app['id'], 'name' => $app['nazev'], 'executable' => $app['executable']];
        }
        $response->getBody()->write(
                json_encode($appsList, JSON_UNESCAPED_UNICODE)
        );
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * POST setAppById
     * Summary: Create or Update Application
     * Notes: Create or Update App by ID
     * Output-Formats: [application/xml, application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     */
    public function setAppById(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $appId = (key_exists('appId', $queryParams)) ? $queryParams['appId'] : null;
    }

}
