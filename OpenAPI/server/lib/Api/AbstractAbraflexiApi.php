<?php

/**
 * MultiFlexi API
 * PHP version 7.4
 *
 * @package AbraFlexi\MultiFlexi
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
namespace AbraFlexi\MultiFlexi\Api;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotImplementedException;

/**
 * AbstractAbraflexiApi Class Doc Comment
 *
 * @package AbraFlexi\MultiFlexi\Api
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 */
abstract class AbstractAbraflexiApi
{
    /**
     * GET getAbraFlexiById
     * Summary: Get AbraFlexi by ID
     * Notes: Returns a single AbraFlexi
     * Output-Formats: [application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     * @param int $abraflexiId ID of app to return
     * @param string $suffix force format suffix
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function getAbraFlexiById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $abraflexiId,
        string $suffix
    ): ResponseInterface {
        $message = "How about implementing getAbraFlexiById as a GET method in AbraFlexi\MultiFlexi\Api\AbraflexiApi class?";
        throw new HttpNotImplementedException($request, $message);
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
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function listAbraFlexis(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $message = "How about implementing listAbraFlexis as a GET method in AbraFlexi\MultiFlexi\Api\AbraflexiApi class?";
        throw new HttpNotImplementedException($request, $message);
    }

    /**
     * POST setAbraFlexiById
     * Summary: Create or Update AbraFlexi record
     * Notes: Create or Upda single AbraFlexi record
     * Output-Formats: [application/json]
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function setAbraFlexiById(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $abraflexiId = (key_exists('abraflexiId', $queryParams)) ? $queryParams['abraflexiId'] : null;
        $message = "How about implementing setAbraFlexiById as a POST method in AbraFlexi\MultiFlexi\Api\AbraflexiApi class?";
        throw new HttpNotImplementedException($request, $message);
    }
}