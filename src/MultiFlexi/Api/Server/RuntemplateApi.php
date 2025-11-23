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
class RuntemplateApi extends AbstractRuntemplateApi
{
    public \MultiFlexi\RunTemplate $engine;

    /**
     * RunTemplate Handler Engine.
     */
    public function __construct()
    {
        $this->engine = new \MultiFlexi\RunTemplate();
        $this->engine->limit = 20;
    }

    /**
     * Runtemplate Info by ID.
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/runtemplate/1
     */
    public function getRunTemplateById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, int $runTemplateId, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $this->engine->loadFromSQL($runTemplateId);
        $runtemplateData = $this->engine->getData();
        $runtemplateData['success'] = empty($runtemplateData['success']) ? '' : unserialize($runtemplateData['success']);
        $runtemplateData['fail'] = empty($runtemplateData['fail']) ? '' : unserialize($runtemplateData['fail']);

        switch ($suffix) {
            case 'html':
                $runtemplateData['id'] = new \Ease\Html\ATag('runtemplate/'.$runtemplateData['id'].'.html', $runtemplateData['id']);
                $runtemplateData['app_id'] = new \Ease\Html\ATag('app/'.$runtemplateData['app_id'].'.html', $runtemplateData['app_id']);
                $runtemplateData['company_id'] = new \Ease\Html\ATag('company/'.$runtemplateData['company_id'].'.html', $runtemplateData['company_id']);

                $runtemplateData['interv'] = \MultiFlexi\RunTemplate::getIntervalEmoji($runtemplateData['interv']).' '.\MultiFlexi\RunTemplate::codeToInterval($runtemplateData['interv']);

                $runtemplateData = [array_keys($runtemplateData), $runtemplateData];

                break;

            default:
                break;
        }

        return DefaultApi::prepareResponse($response, $runtemplateData, $suffix, null, 'runtemplate');
    }

    /**
     * GET runtemplatesGet
     * Summary: List all RunTemplates
     * Notes: List all RunTemplates.
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     */
    public function listRuntemplates(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        $runtemplatesList = [];
        $queryParams = $request->getQueryParams();
        $limit = (\array_key_exists('limit', $queryParams)) ? $queryParams['limit'] : $this->engine->limit;

        foreach ($this->engine->listingQuery()->limit($limit) as $runtemplate) {
            $runtemplateId = $runtemplate['id'];
            $runtemplate['success'] = empty($runtemplate['success']) ? '' : unserialize($runtemplate['success']);
            $runtemplate['fail'] = empty($runtemplate['fail']) ? '' : unserialize($runtemplate['fail']);

            switch ($suffix) {
                case 'html':
                    $runtemplate['id'] = new \Ease\Html\ATag('runtemplate/'.$runtemplate['id'].'.html', $runtemplate['id']);
                    $runtemplate['app_id'] = new \Ease\Html\ATag('app/'.$runtemplate['app_id'].'.html', $runtemplate['app_id']);
                    $runtemplate['company_id'] = new \Ease\Html\ATag('company/'.$runtemplate['company_id'].'.html', $runtemplate['company_id']);

                    break;

                default:
                    break;
            }

            $runtemplatesList[$runtemplateId] = $runtemplate;
        }

        return DefaultApi::prepareResponse($response, $runtemplatesList, $suffix, null, 'runtemplate');
    }

    /**
     * Update RunTemplate by ID.
     *
     * POST /runtemplate/{runTemplateId}.{suffix}
     * Performs partial update - only provided fields are updated.
     *
     * @param ServerRequestInterface $request       Request
     * @param ResponseInterface      $response      Response
     * @param int                    $runTemplateId RunTemplate ID from URL
     * @param string                 $suffix        Response format suffix
     */
    public function updateRunTemplateById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $runTemplateId,
        string $suffix,
    ): ResponseInterface {
        $requestBody = json_decode($request->getBody()->getContents(), true);

        // Validate ID match
        if (!isset($requestBody['id']) || (int) $requestBody['id'] !== $runTemplateId) {
            $error = [
                'error' => 'ID mismatch',
                'expected' => $runTemplateId,
                'received' => $requestBody['id'] ?? null,
            ];

            return DefaultApi::prepareResponse($response->withStatus(400), $error, $suffix);
        }

        // Load existing RunTemplate
        $this->engine->loadFromSQL($runTemplateId);

        if (!$this->engine->getMyKey()) {
            return DefaultApi::prepareResponse($response->withStatus(404), ['error' => 'RunTemplate not found'], $suffix);
        }

        // Update only provided fields (partial update)
        unset($requestBody['id']); // Don't update ID itself

        foreach ($requestBody as $key => $value) {
            $this->engine->setDataValue($key, $value);
        }

        // Save to database
        if ($this->engine->dbsync()) {
            // Return updated data
            $updatedData = $this->engine->getData();
            $updatedData['success'] = empty($updatedData['success']) ? '' : unserialize($updatedData['success']);
            $updatedData['fail'] = empty($updatedData['fail']) ? '' : unserialize($updatedData['fail']);

            return DefaultApi::prepareResponse($response->withStatus(201), $updatedData, $suffix, null, 'runtemplate');
        }

        return DefaultApi::prepareResponse($response->withStatus(500), ['error' => 'Update failed'], $suffix);
    }
}
