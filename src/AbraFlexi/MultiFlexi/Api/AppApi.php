<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Api;

/**
 * Description of DefaultApi
 * 
 * @author vitex
 */
class AppApi extends AbstractAppApi {

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
        $app = new \AbraFlexi\MultiFlexi\Application($appId);
        $response->getBody()->write(
                json_encode(['id' => $app->getMyKey(), 'name' => $app->getRecordName(), 'executable' => $app->getDataValue('executable')], JSON_UNESCAPED_UNICODE)
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

}
