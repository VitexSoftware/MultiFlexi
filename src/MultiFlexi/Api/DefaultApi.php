<?php

/**
 * Multi Flexi - Misc API functions
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2022 Vitex Software
 */

namespace MultiFlexi\Api;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of DefaultApi
 *
 * @author vitex
 */
class DefaultApi extends AbstractDefaultApi
{
    public function rootGet(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
         return $response->withHeader('Location', 'index.html')->withStatus(302);
    }

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

        $user = new \MultiFlexi\User($queryParams['username']);
        if ($user->getMyKey()) {
            if ($user->passwordValidation($queryParams['password'], $user->getDataValue($user->passwordColumn))) {
                if ($user->isAccountEnabled()) {
                    $token = new \MultiFlexi\Token();
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
        return self::prepareResponse($response, $payload, /* $suffix */);
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
    public function getApiIndex(ServerRequestInterface $request, ResponseInterface $response, string $suffix): ResponseInterface
    {
        $data[] = ['path' => 'apps'];
        $data[] = ['path' => 'abrafleixs'];
        $data[] = ['path' => 'users'];

        foreach ($data as $id => $row) {
            switch ($suffix) {
                case 'html':
                    $data[$id]['path'] = new \Ease\Html\ATag($data[$id]['path'] . '.html', $data[$id]['path']);
                    break;
                default:
                    break;
            }
        }

        return self::prepareResponse($response, $data, $suffix, 'index');
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
    public function pingsuffixGet(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        return self::prepareResponse($response, [['ping' => 'pong']], $suffix, 'ping');
    }

    /**
     * Prepared response by suffix
     *
     * @param \Psr\Http\Message\ResponseInterface $response data to return
     * @param array                               $data     to print
     * @param string                              $suffix   reqired format
     * @param string                              $evidence data subject
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function prepareResponse($response, $data, $suffix, $evidence = 'data')
    {
        switch ($suffix) {
            case 'json':
                $response->getBody()->write(json_encode([$evidence => $data], JSON_UNESCAPED_UNICODE));
                $responseFinal = $response->withHeader('Content-type', 'application/json');
                break;
            case 'yaml':
                $response->getBody()->write(yaml_emit([$evidence => $data], JSON_UNESCAPED_UNICODE));
                $responseFinal = $response->withHeader('Content-type', 'text/yaml');
                break;
            case 'xml':
                foreach ($data as $id => $row) {
                    $xmlData['id' . $id] = $row;
                }
                $response->getBody()->write(self::arrayToXml($xmlData, '<' . $evidence . '/>'));
                $responseFinal = $response->withHeader('Content-type', 'application/xml');
                break;
            case 'html':
            default:
                $response->getBody()->write((new \Ease\Html\H1Tag($evidence))->getRendered());
                $response->getBody()->write((new \Ease\Html\TableTag())->populate($data)->getRendered());
                $response->getBody()->write((string)new \Ease\Html\HrTag());

                $urlParts = pathinfo(\Ease\WebPage::getUri());
                $baseUrl = $urlParts['dirname'] . '/' . $urlParts['filename'];

                $formatLinks[] = new \Ease\Html\ATag($baseUrl . '.json', 'json');
                $formatLinks[] = new \Ease\Html\ATag($baseUrl . '.xml', 'xml');
                $formatLinks[] = new \Ease\Html\ATag($baseUrl . '.yaml', 'yaml');

                $response->getBody()->write('[ ' . implode(' | ', $formatLinks) . ' ]');
                $responseFinal = $response->withHeader('Content-type', 'text/html');
                break;
        }
        return $responseFinal;
    }

    /**
     * Array to XML convertor
     *
     * @param type $array
     * @param type $rootElement
     * @param type $xml
     *
     * @return type
     */
    public static function arrayToXml($array, $rootElement = null, $xml = null)
    {
        $_xml = $xml;
        if ($_xml === null) {
            $_xml = new \SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
        }
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                self::arrayToXml($v, $k, $_xml->addChild($k));
            } else {
                $_xml->addChild($k, $v);
            }
        }
        return $_xml->asXML();
    }
}
