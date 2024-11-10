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

namespace MultiFlexi\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of DefaultApi.
 *
 * @author vitex
 */
class JobApi extends AbstractJobApi {

    public $engine;

    /**
     * Job Handler Engine.
     */
    public function __construct() {
        $this->engine = new \MultiFlexi\Job();
        $this->engine->limit = 20;
    }

    /**
     * Job Info by ID.
     *
     * @url http://localhost/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/job/1
     *
     * @param int $jobId
     */
    public function getJobById(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, int $jobId, string $suffix): \Psr\Http\Message\ResponseInterface {
        $this->engine->loadFromSQL((int) $jobId);
        $jobData = $this->engine->getData();
        $jobData['env'] = unserialize($jobData['env']);

        switch ($suffix) {
            case 'html':
                $jobData['id'] = new \Ease\Html\ATag('job/' . $jobData['id'] . '.html', $jobData['id']);
                $jobData['env'] = new \MultiFlexi\Ui\EnvironmentView($jobData['env']);
                $jobData['stdout'] = new \Ease\Html\PreTag((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert((string) $jobData['stdout']));
                $jobData['stderr'] = new \Ease\Html\PreTag((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert((string) $jobData['stderr']));
                $jobData['app_id'] = new \Ease\Html\ATag('app/' . $jobData['app_id'] . '.html', $jobData['app_id']);
                $jobData['company_id'] = new \Ease\Html\ATag('company/' . $jobData['company_id'] . '.html', $jobData['company_id']);
                $jobData['runtemplate_id'] = new \Ease\Html\ATag('runtemplate/' . $jobData['runtemplate_id'] . '.html', $jobData['runtemplate_id']);
                $jobData = [array_keys($jobData), $jobData];
                break;

            default:
                break;
        }

        return DefaultApi::prepareResponse($response, $jobData , $suffix, 'jobs', 'job');
    }

    /**
     * GET jobsGet
     * Summary: List all Jobs
     * Notes: List all Jobs
     *
     * @param ServerRequestInterface $request  Request
     * @param ResponseInterface      $response Response
     */
    public function listJobs(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, string $suffix): \Psr\Http\Message\ResponseInterface {
        $jobsList = [];
        $queryParams = $request->getQueryParams();
        $limit = (key_exists('limit', $queryParams)) ? $queryParams['limit'] : $this->engine->limit;

        foreach ($this->engine->listingQuery()->limit($limit) as $job) {
            $jobId = $job['id'];
            $job['env'] = unserialize($job['env']);
            switch ($suffix) {
                case 'html':
                    $job['id'] = new \Ease\Html\ATag('job/' . $job['id'] . '.html', $job['id']);
                    $job['env'] = new \MultiFlexi\Ui\EnvironmentView($job['env']);
                    $job['stdout'] = new \Ease\Html\PreTag((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert((string) $job['stdout']));
                    $job['stderr'] = new \Ease\Html\PreTag((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert((string) $job['stderr']));
                    $job['app_id'] = new \Ease\Html\ATag('app/' . $job['app_id'] . '.html', $job['app_id']);
                    $job['company_id'] = new \Ease\Html\ATag('company/' . $job['company_id'] . '.html', $job['company_id']);
                    $job['runtemplate_id'] = new \Ease\Html\ATag('runtemplate/' . $job['runtemplate_id'] . '.html', $job['runtemplate_id']);
                    break;

                default:
                    break;
            }
            $jobsList[$jobId] = $job;
        }

        return DefaultApi::prepareResponse($response, $jobsList, $suffix, 'jobs', 'job');
    }
}
