<?php

/**
 * MultiFlexi API
 * PHP version 7.4
 *
 * @package MultiFlexi
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 */

/**
 * This is an example of using OAuth2 Application Flow in a specification to describe security to your API.
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://github.com/openapitools/openapi-generator.git
 */

/**
 * NOTE: This class is auto generated by the openapi generator program.
 * https://github.com/openapitools/openapi-generator
 * Do not edit the class manually.
 * Extend this class with your controller. You can inject dependencies via class constructor,
 * @see https://github.com/PHP-DI/Slim-Bridge basic example.
 */
namespace MultiFlexi\Api;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotImplementedException;

/**
 * AbstractServerApi Class Doc Comment
 *
 * @package MultiFlexi\Api
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 */
abstract class AbstractServerApi
{
    /**
     * GET getServerById
     * Summary: Get Server by ID
     * Notes: Returns a single Server
     * Output-Formats: [application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     * @param int $serverId ID of app to return
     * @param string $suffix force format suffix
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function getServerById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $serverId,
        string $suffix
    ): ResponseInterface {
        $message = "How about implementing getServerById as a GET method in MultiFlexi\Api\ServerApi class?";
        throw new HttpNotImplementedException($request, $message);
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
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function listServers(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $message = "How about implementing listServers as a GET method in MultiFlexi\Api\ServerApi class?";
        throw new HttpNotImplementedException($request, $message);
    }

    /**
     * POST setServerById
     * Summary: Create or Update Server record
     * Notes: Create or Update single Server record
     * Output-Formats: [application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function setServerById(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $serverId = (key_exists('serverId', $queryParams)) ? $queryParams['serverId'] : null;
        $message = "How about implementing setServerById as a POST method in MultiFlexi\Api\ServerApi class?";
        throw new HttpNotImplementedException($request, $message);
    }
}