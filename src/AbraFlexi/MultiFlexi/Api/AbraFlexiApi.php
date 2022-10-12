<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Api;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of DefaultApi
 * 
 * @author vitex
 */
class AbraFlexiApi extends AbstractAbraFlexiApi {

    /**
     * Api Handler Engine
     * @var \AbraFlexi\MultiFlexi\AbraFlexis
     */
    public $enfine = null;

    /**
     * Prepare AbraFlexis engine
     */
    public function __construct() {
        $this->engine = new \AbraFlexi\MultiFlexi\AbraFlexis();
    }

    /**
     * AbraFlexi info by ID
     * 
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/abraflexi/1
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $abraflexiId
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAbraFlexiById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, int $abraflexiId): \Psr\Http\Message\ResponseInterface {
        $this->engine->loadFromSQL($abraflexiId);
        $response->getBody()->write(
                json_encode(['id' => $this->engine->getMyKey(), 'name' => $this->engine->getRecordName(), 'executable' => $this->engine->getDataValue('executable')], JSON_UNESCAPED_UNICODE)
        );
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * GET listAbraFlexis
     * Summary: Show All AbraFlexis
     * Notes: All AbraFlexi servers registered
     * Output-Formats: [application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     */
    public function listAbraFlexis(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {
        foreach ($this->engine->getAll() as $abraflexi) {
            $abraflexisList[] = ['id' => $abraflexi['id'], 'name' => $abraflexi['nazev'], 'executable' => $abraflexi['executable']];
        }
        $response->getBody()->write(json_encode($abraflexisList, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * POST setAbraFlexiById
     * Summary: Create or Update AbraFlexi record
     * Notes: Create or Upda single AbraFlexi record
     * Output-Formats: [application/xml, application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     */
    public function setAbraFlexiById(
            ServerRequestInterface $request,
            ResponseInterface $response
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        if (key_exists('abraflexiId', $queryParams)) {
            $this->engine->setMyKey($queryParams['abraflexiId']);
            $this->engine->updateToSQL($queryParams);
        } else {
            $this->engine->insertIntoSQL($queryParams);
        }
    }

}
