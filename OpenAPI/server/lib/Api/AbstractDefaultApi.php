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
 * AbstractDefaultApi Class Doc Comment
 *
 * @package MultiFlexi\Api
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 */
abstract class AbstractDefaultApi
{
    /**
     * GET getApiIndex
     * Summary: Endpoints listing
     * Notes: Show current API
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     * @param string $suffix force format suffix
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function getApiIndex(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $suffix
    ): ResponseInterface {
        $message = "How about implementing getApiIndex as a GET method in MultiFlexi\Api\DefaultApi class?";
        throw new HttpNotImplementedException($request, $message);
    }

    /**
     * GET loginSuffixGet
     * Summary: Return User&#39;s token
     * Notes: Send login &amp; password to obtain oAuth token
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     * @param string $suffix force format suffix
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function loginSuffixGet(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $suffix
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $username = (key_exists('username', $queryParams)) ? $queryParams['username'] : null;
        $password = (key_exists('password', $queryParams)) ? $queryParams['password'] : null;
        $message = "How about implementing loginSuffixGet as a GET method in MultiFlexi\Api\DefaultApi class?";
        throw new HttpNotImplementedException($request, $message);
    }

    /**
     * POST loginSuffixPost
     * Summary: Return User&#39;s token
     * Notes: Send login &amp; password to obtain oAuth token
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     * @param string $suffix force format suffix
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function loginSuffixPost(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $suffix
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $username = (key_exists('username', $queryParams)) ? $queryParams['username'] : null;
        $password = (key_exists('password', $queryParams)) ? $queryParams['password'] : null;
        $message = "How about implementing loginSuffixPost as a POST method in MultiFlexi\Api\DefaultApi class?";
        throw new HttpNotImplementedException($request, $message);
    }

    /**
     * GET pingSuffixGet
     * Summary: Server heartbeat operation
     * Notes: This operation shows how to override the global security defined above, as we want to open it up for all users.
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     * @param string $suffix force format suffix
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function pingSuffixGet(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $suffix
    ): ResponseInterface {
        $message = "How about implementing pingSuffixGet as a GET method in MultiFlexi\Api\DefaultApi class?";
        throw new HttpNotImplementedException($request, $message);
    }

    /**
     * GET rootGet
     * Summary: Redirect to index
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function rootGet(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $message = "How about implementing rootGet as a GET method in MultiFlexi\Api\DefaultApi class?";
        throw new HttpNotImplementedException($request, $message);
    }
}
