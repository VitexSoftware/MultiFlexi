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
     * Notes: Send login &amp; password to obtain oAuth token.
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
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

        return self::prepareResponse($response, $payload, '');
    }

    /**
     * GET getApiIndex
     * Summary: Endpoints listing
     * Notes: Show current API.
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     *
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function getApiIndex(ServerRequestInterface $request, ResponseInterface $response, string $suffix): ResponseInterface
    {
        $data[] = ['path' => 'ping'];
        $data[] = ['path' => 'status'];
        $data[] = ['path' => 'apps'];
        $data[] = ['path' => 'companies'];
        $data[] = ['path' => 'runtemplates'];
        $data[] = ['path' => 'jobs'];
        $data[] = ['path' => 'jobs/status'];
        $data[] = ['path' => 'topics'];
        $data[] = ['path' => 'users'];
        $data[] = ['path' => 'credentials'];
        $data[] = ['path' => 'credential_types'];
        $data[] = ['path' => 'login'];

        foreach ($data as $id => $row) {
            switch ($suffix) {
                case 'html':
                    $data[$id]['path'] = new \Ease\Html\ATag($data[$id]['path'].'.html', $data[$id]['path']);

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
     * @throws HttpNotImplementedException to force implementation class to override this method
     */
    public function pingsuffixGet(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $suffix): \Psr\Http\Message\ResponseInterface
    {
        return self::prepareResponse($response, [['ping' => 'pong']], $suffix, 'ping');
    }

    /**
     * Prepared response by suffix.
     *
     * @param \Psr\Http\Message\ResponseInterface $response data to return
     * @param array                               $data     to print
     * @param string                              $suffix   required format
     * @param string                              $evidence data subject
     * @param mixed                               $subitem
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function prepareResponse($response, $data, $suffix, $evidence = 'data', $subitem = 'item')
    {
        switch ($suffix) {
            case 'json':
                $response->getBody()->write(json_encode([$evidence => $data], \JSON_UNESCAPED_UNICODE));
                $responseFinal = $response->withHeader('Content-type', 'application/json');

                break;
            case 'yaml':
                $response->getBody()->write(\yaml_emit([$evidence => $data], \JSON_UNESCAPED_UNICODE));
                $responseFinal = $response->withHeader('Content-type', 'text/yaml');

                break;
            case 'xml':
                foreach ($data as $id => $row) {
                    $xmlData[is_numeric($id) ? '__'.$id.'__' : $id] = $row;
                }

                $xmlRaw = self::arrayToXml($xmlData, '<'.$evidence.'/>');
                $response->getBody()->write(preg_replace('/__\d+__/', $subitem, $xmlRaw));

                $responseFinal = $response->withHeader('Content-type', 'application/xml');

                break;
            case 'html':
            default:
                $response->getBody()->write(<<<'EOD'
<head><style>
table, th, td {
  border: 1px solid lightgray;
  border-collapse: collapse;
  padding: 4px;
}
</style></head>
EOD);
                $response->getBody()->write((new \Ease\Html\H1Tag($evidence))->getRendered());
                $response->getBody()->write((new \Ease\Html\TableTag(null))->populate($data)->getRendered());
                $response->getBody()->write((string) new \Ease\Html\HrTag());

                $urlParts = pathinfo(\Ease\WebPage::getUri());
                $baseUrl = $urlParts['dirname'].'/'.$urlParts['filename'];

                $formatLinks[] = new \Ease\Html\ATag($baseUrl.'.json', 'json');
                $formatLinks[] = new \Ease\Html\ATag($baseUrl.'.xml', 'xml');
                $formatLinks[] = new \Ease\Html\ATag($baseUrl.'.yaml', 'yaml');
                $formatLinks[] = new \Ease\Html\ATag($baseUrl.'.csv', 'csv');

                $response->getBody()->write('<a href="index.html">🏠</a>&nbsp;');
                $response->getBody()->write('[ '.implode(' | ', $formatLinks).' ]');
                $responseFinal = $response->withHeader('Content-type', 'text/html');

                break;
        }

        return $responseFinal;
    }

    /**
     * Array to XML convertor.
     *
     * @param type $rootElement
     * @param type $xml
     *
     * @return bool|string
     */
    public static function arrayToXml(array $array, string $rootElement = '', ?\SimpleXMLElement $xml = null)
    {
        $_xml = $xml;

        if ($_xml === null) {
            $_xml = new \SimpleXMLElement($rootElement ?: '<root/>');
        }

        foreach ($array as $k => $v) {
            if (\is_array($v)) {
                self::arrayToXml($v, $k, $_xml->addChild($k));
            } else {
                $_xml->addChild((string) $k, (string) $v);
            }
        }

        return $_xml->asXML();
    }

    public function statusSuffixGet(ServerRequestInterface $request, ResponseInterface $response, string $suffix): ResponseInterface
    {
        $engine = new \MultiFlexi\Engine();
        $status = [];
        $pdo = $engine->getPdo();
        $database = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME).' '.
                    $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS).' '.
                    $pdo->getAttribute(\PDO::ATTR_SERVER_INFO).' '.
                    $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

        if ($suffix === 'html') {
            $status = [
                'status' => ['status', 'ok?'],
                ['version', \Ease\Shared::appVersion()],
                ['php', \PHP_VERSION],
                ['os', \PHP_OS],
                ['memory', memory_get_usage()],
                ['companies', $engine->getFluentPDO()->from('company')->count()],
                ['apps', $engine->getFluentPDO()->from('apps')->count()],
                ['runtemplates', $engine->getFluentPDO()->from('runtemplate')->count()],
                ['topics', $engine->getFluentPDO()->from('topic')->count()],
                ['credentials', $engine->getFluentPDO()->from('credentials')->count()],
                ['credential_types', $engine->getFluentPDO()->from('credential_type')->count()],
                ['database', $database],
                ['daemon', \MultiFlexi\Runner::isServiceActive('multiflexi.service') ? 'running' : 'stopped'],
                ['timestamp', date('c')],
            ];
        } else {
            $status['version'] = \Ease\Shared::appVersion();
            $status['php'] = \PHP_VERSION;
            $status['os'] = \PHP_OS;
            $status['memory'] = memory_get_usage();
            $status['companies'] = $engine->getFluentPDO()->from('company')->count();
            $status['apps'] = $engine->getFluentPDO()->from('apps')->count();
            $status['runtemplates'] = $engine->getFluentPDO()->from('runtemplate')->count();
            $status['topics'] = $engine->getFluentPDO()->from('topic')->count();
            $status['credentials'] = $engine->getFluentPDO()->from('credentials')->count();
            $status['credential_types'] = $engine->getFluentPDO()->from('credential_type')->count();
            $status['database'] = $database;
            $status['daemon'] = \MultiFlexi\Runner::isServiceActive('multiflexi.service') ? 'running' : 'stopped';
            $status['timestamp'] = date('c');
        }

        return self::prepareResponse($response, $status, $suffix, 'status');
    }

    public function getJobsStatus(ServerRequestInterface $request, ResponseInterface $response, string $suffix): ResponseInterface
    {
        $engine = new \MultiFlexi\Engine();
        $pdo = $engine->getPdo();

        // Query to get job status information
        $query = <<<'EOD'

            SELECT
                COUNT(*) AS total_jobs,
                SUM(CASE WHEN exitcode = 0 THEN 1 ELSE 0 END) AS successful_jobs,
                SUM(CASE WHEN exitcode != 0 THEN 1 ELSE 0 END) AS failed_jobs,
                SUM(CASE WHEN exitcode IS NULL THEN 1 ELSE 0 END) AS incomplete_jobs,
                COUNT(DISTINCT app_id) AS total_applications,
                SUM(CASE WHEN schedule IS NOT NULL THEN 1 ELSE 0 END) AS repeated_jobs
            FROM job

EOD;

        $stmt = $pdo->query($query);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($suffix === 'html') {
            $status = [
                ['status', 'ok?'],
                ['timestamp', date('c')],
                ['successful_jobs', (int) $result['successful_jobs']],
                ['failed_jobs', (int) $result['failed_jobs']],
                ['incomplete_jobs', (int) $result['incomplete_jobs']],
                ['total_applications', (int) $result['total_applications']],
                ['repeated_jobs', (int) $result['repeated_jobs']],
                ['total_jobs', (int) $result['total_jobs']],
            ];
        } else {
            $status = [
                'timestamp' => date('c'),
                'successful_jobs' => (int) $result['successful_jobs'],
                'failed_jobs' => (int) $result['failed_jobs'],
                'incomplete_jobs' => (int) $result['incomplete_jobs'],
                'total_applications' => (int) $result['total_applications'],
                'repeated_jobs' => (int) $result['repeated_jobs'],
                'total_jobs' => (int) $result['total_jobs'],
            ];
        }

        return self::prepareResponse($response, $status, $suffix, 'jobsStatus');
    }
}
