<?php

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Api;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of DefaultApi
 *
 * @author vitex
 */
class AppApi extends AbstractAppApi
{
    public $engine = null;

    /**
     * App Handler Engine
     */
    public function __construct()
    {
        $this->engine = new \MultiFlexi\Application();
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
    public function getAppById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $appId, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $this->engine->loadFromSQL(intval($appId));
        $appData = $this->engine->getData();
        switch ($suffix) {
            case 'html':
                //                $appData['nazev'] = new \Ease\Html\ATag($appData['id'] . '.html', $appData['nazev']);
                $appData['image'] = new \Ease\Html\ATag($appData['id'] . '.html', new \Ease\Html\ImgTag($appData['image'], $appData['nazev'], ['width' => '64']));

                break;
            default:
                break;
        }
        return DefaultApi::prepareResponse($response, [$appData], $suffix, $appData['nazev']);
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
    public function listApps(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $appsList = [];
        foreach ($this->engine->getAll() as $app) {
            $appsList[$app['id']] = $app;
            switch ($suffix) {
                case 'html':
                    $appsList[$app['id']]['nazev'] = new \Ease\Html\ATag('app/' . $app['id'] . '.html', $app['nazev']);
                    $appsList[$app['id']]['image'] = new \Ease\Html\ATag('app/' . $app['id'] . '.html', new \Ease\Html\ImgTag($app['image'], $app['nazev'], ['width' => '64']));
                    break;
                default:
                    break;
            }
        }
        return DefaultApi::prepareResponse($response, $appsList, $suffix, 'apps');
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
    public function setAppById(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $appId = (key_exists('appId', $queryParams)) ? $queryParams['appId'] : null;
        $appInfo = ['id' => $appId, 'success' => $this->engine->dbsync($queryParams)];
        return DefaultApi::prepareResponse($response, $appInfo, $suffix, 'app' . $appId);
    }
}
