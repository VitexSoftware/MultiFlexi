<?php

/**
 * Multi Flexi - Misc API functions
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
class DefaultApi extends AbstractDefaultApi {

    /**
     * GET loginGet
     * Summary: Return User&#39;s token
     * Notes: Send login &amp; password to obtain oAuth token
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     */
    public function loginGet(
            ServerRequestInterface $request,
            ResponseInterface $response
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $payload = [];

        $user = new \AbraFlexi\MultiFlexi\User($queryParams['username']);
        if ($user->getMyKey()) {
            if ($user->passwordValidation($queryParams['password'], $user->getDataValue($user->passwordColumn))) {
                if ($user->isAccountEnabled()) {
                    $token = new \AbraFlexi\MultiFlexi\Token();
                    $token->setDataValue('user_id', $user->getDataValue('id'));
                    $token->generate()->dbSync();
                    $payload['token'] = $token->getRecordName();
                    $payload['message'] = _('Token generated');
                    $payload['satatus'] = 'success';
                } else {
                    $payload['message'] = _('Account is disabled');
                    $payload['satatus'] = 'error';
                }
            } else {
                if (!empty($user->getData())) {
                    $payload['message'] = _('invalid password');
                    $payload['satatus'] = 'error';
                }
            }
        } else {
            $payload['message'] = sprintf(_('user %s does not exist'), $queryParams['username']);
            $payload['satatus'] = 'error';
        }
        $response->getBody()->write(
                json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * GET getApiIndex
     * Summary: Endpoints listing
     * Notes: Show current API
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function getApiIndex(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $payload = ['api' => '/'];
        $response->getBody()->write(
                json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
        return $response->withHeader('Content-type', 'application/json');
    }

    /**
     * GET pingGet
     * Summary: Server heartbeat operation
     * Notes: This operation shows how to override the global security defined above, as we want to open it up for all users.
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @return ResponseInterface
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function pingGet(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface {
        $payload = ['ping' => 'pong'];

        $response->getBody()->write(
                json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        return $response->withHeader('Content-type', 'application/json');
    }

}
