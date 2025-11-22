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
 * Description of DefaultApi.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class AppApi extends \MultiFlexi\Api\Server\AbstractAppApi
{
    public $engine;

    /**
     * App Handler Engine.
     */
    public function __construct()
    {
        $this->engine = new \MultiFlexi\Application();
    }

    /**
     * App Info by ID.
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/app/1
     */
    public function getAppById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, int $appId, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $this->engine->loadFromSQL($appId);
        $appData = $this->engine->getData();
        
        // Add environment configuration fields
        $conffield = new \MultiFlexi\Conffield();
        $envFields = $conffield->appConfigs($appId);
        $appData['environment'] = [];
        foreach ($envFields as $keyname => $fieldData) {
            $appData['environment'][$keyname] = [
                'type' => $fieldData['type'],
                'description' => $fieldData['description'],
                'defval' => $fieldData['defval'],
                'required' => (bool) $fieldData['required'],
            ];
        }
        
        // Add exit codes by querying directly
        $appData['exitCodes'] = [];
        $exitCodesEngine = new \MultiFlexi\DBEngine();
        $exitCodesEngine->myTable = 'app_exit_codes';
        $exitCodesData = $exitCodesEngine->listingQuery()
            ->where('app_id', $appId)
            ->orderBy('exit_code')
            ->orderBy('lang')
            ->fetchAll();
        
        foreach ($exitCodesData as $exitCodeRow) {
            $code = (string) $exitCodeRow['exit_code'];
            $lang = $exitCodeRow['lang'];
            
            if (!isset($appData['exitCodes'][$code])) {
                $appData['exitCodes'][$code] = [
                    'severity' => $exitCodeRow['severity'],
                    'retry' => (bool) $exitCodeRow['retry'],
                    'description' => [],
                ];
            }
            
            $appData['exitCodes'][$code]['description'][$lang] = $exitCodeRow['description'];
        }

        switch ($suffix) {
            case 'html':
                //                $appData['name'] = new \Ease\Html\ATag($appData['id'] . '.html', $appData['name']);
                $appData['image'] = new \Ease\Html\ATag($appData['id'].'.html', new \Ease\Html\ImgTag($appData['image'], $appData['name'], ['width' => '64']));
                $appData = [array_keys($appData), $appData];

                break;

            default:
                break;
        }

        return DefaultApi::prepareResponse($response, $appData, $suffix, 'apps', 'application');
    }

    /**
     * All Apps.
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/apps
     */
    public function listApps(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $appsList = [];

        foreach ($this->engine->getAll() as $app) {
            $appsList[$app['id']] = $app;

            switch ($suffix) {
                case 'html':
                    $appsList[$app['id']]['name'] = new \Ease\Html\ATag('app/'.$app['id'].'.html', $app['name']);

                    if (file_exists('../images/'.$app['uuid'].'.svg')) {
                        $appIcon = \Ease\Html\ImgTag::fileBase64src('../images/'.$app['uuid'].'.svg');
                    } else {
                        $appIcon = $app['image'];
                    }

                    $appsList[$app['id']]['image'] = new \Ease\Html\ATag('app/'.$app['id'].'.html', new \Ease\Html\ImgTag($appIcon, $app['name'], ['width' => '64']));

                    break;

                default:
                    break;
            }
        }

        return DefaultApi::prepareResponse($response, $appsList, $suffix, 'apps', 'application');
    }

    /**
     * POST setAppById
     * Summary: Create or Update Application
     * Notes: Create or Update App by ID
     * Output-Formats: [application/xml, application/json].
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     */
    public function setAppById(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $appId = (\array_key_exists('appId', $queryParams)) ? $queryParams['appId'] : null;
        $appInfo = ['id' => $appId, 'success' => $this->engine->dbsync($queryParams)];

        return DefaultApi::prepareResponse($response, $appInfo, $suffix, 'app'.$appId);
    }
}
