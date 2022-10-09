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
class DefaultApi extends AbstractDefaultApi {

    public function exampleGet(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface {
        return parent::exampleGet($request, $response);
    }

    public function pingGet(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface {
        $payload = ['ping' => 'pong'];

        $response->getBody()->write(
                json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        return $response->withHeader('Content-type', 'application/json');
    }

}
