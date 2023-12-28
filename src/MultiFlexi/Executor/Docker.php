<?php

declare(strict_types=1);

/**
 * Multi Flexi - Run Tasks in container
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Executor;

use Docker\Docker as DockerClient;
use Docker\DockerClientFactory;
use Docker\API\Model\ContainersCreatePostBody;

/**
 * Description of Podman
 *
 * @author vitex
 */
class Docker extends \MultiFlexi\CommonExecutor implements \MultiFlexi\executor
{
    use \Ease\Logger\Logging;

    const PULLCOMMAND = 'docker pull docker.io/vitexsoftware/debian:bookworm';

    public $job;

    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     *
     * @return string
     */
    public static function name(): string
    {
        return _('Docker');
    }

    /**
     *
     * @return string
     */
    public static function description(): string
    {
        return _('Execute jobs in container using Docker');
    }

    /**
     * @see https://docker-php.readthedocs.io/en/latest/cookbook/container-run/
     */
    public function launch()
    {

        $docker = DockerClient::

        $containerConfig = new ContainersCreatePostBody();
        $containerConfig->setImage('busybox:latest');
        $containerConfig->setCmd(['echo', 'I am running a command']);
// You need to attach stream of the container to docker
        $containerConfig->setAttachStdin(true);
        $containerConfig->setAttachStdout(true);
        $containerConfig->setAttachStderr(true);

        $docker->containerCreate($containerConfig, ['name' => 'my-container-unique-name']);

// You also need to set stream to true to get the logs, and tell which stream you want to attach
        $attachStream = $docker->containerAttach('my-container-unique-name', [
            'stream' => true,
            'stdin' => true,
            'stdout' => true,
            'stderr' => true
        ]);
        $docker->containerStart('my-container-unique-name');

        $attachStream->onStdout(function ($stdout) {
            $this->stdout .= $stdout;
            echo $stdout;
        });
        $attachStream->onStderr(function ($stderr) {
            $this->stderr .= $stderr;
            echo $stderr;
        });

        $attachStream->wait();

        $this->pullImage();
        $this->launchContainer();
        $this->updateContainer();
        $this->deployApp();
        $this->runApp();
        $this->storeLogs();
        $this->stopContainer();
    }

    public function pullImage()
    {
    }

    public function launchContainer()
    {
    }

    public function updateContainer()
    {
    }

    public function deployApp()
    {
    }

    public function runApp()
    {
    }

    public function storeLogs()
    {
    }

    public function stopContainer()
    {
    }

    /**
     * Can this Executor execute given application ?
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return empty($app->getDataValue('ociimage')) === false; // Container Image must be present
    }

    public static function logo()
    {
        return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz'
                . '0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gVXBsb2FkZWQgdG86IFNWRyBSZXBvL'
                . 'CB3d3cuc3ZncmVwby5jb20sIEdlbmVyYXRvcjogU1ZHIFJlcG8gTWl4ZXIgVG9vbHMgLS0+'
                . 'Cgo8c3ZnCiAgIGFyaWEtbGFiZWw9IkRvY2tlciIKICAgcm9sZT0iaW1nIgogICB2aWV3Qm94'
                . 'PSIwIDAgNTEyIDUxMiIKICAgdmVyc2lvbj0iMS4xIgogICBpZD0ic3ZnNTkiCiAgIHNvZGlwb'
                . '2RpOmRvY25hbWU9ImRvY2tlci1zdmdyZXBvLWNvbS5zdmciCiAgIGlua3NjYXBlOnZlcnNpb2'
                . '49IjEuMi4yIChiMGE4NDg2NTQxLCAyMDIyLTEyLTAxKSIKICAgeG1sbnM6aW5rc2NhcGU9Imh0'
                . 'dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiCiAgIHhtbG5zOnNvZGl'
                . 'wb2RpPSJodHRwOi8vc29kaXBvZGkuc291cmNlZm9yZ2UubmV0L0RURC9zb2Rp'
                . 'cG9kaS0wLmR0ZCIKICAgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc'
                . '3ZnIgogICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj'
                . '4KICA8ZGVmcwogICAgIGlkPSJkZWZzNjMiIC8+CiAgPHNvZGlwb2RpOm5hbWV'
                . 'kdmlldwogICAgIGlkPSJuYW1lZHZpZXc2MSIKICAgICBwYWdlY29sb3I9IiNm'
                . 'ZmZmZmYiCiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiCiAgICAgYm9yZGVyb'
                . '3BhY2l0eT0iMS4wIgogICAgIGlua3NjYXBlOnNob3dwYWdlc2hhZG93PSIyIg'
                . 'ogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwLjAiCiAgICAgaW5rc2NhcGU'
                . '6cGFnZWNoZWNrZXJib2FyZD0iMCIKICAgICBpbmtzY2FwZTpkZXNrY29sb3I9'
                . 'IiNkMWQxZDEiCiAgICAgc2hvd2dyaWQ9ImZhbHNlIgogICAgIGlua3NjYXBlO'
                . 'npvb209IjAuODU4Mzk4NDQiCiAgICAgaW5rc2NhcGU6Y3g9Ii02My40OTAzMy'
                . 'IKICAgICBpbmtzY2FwZTpjeT0iMjM3LjA2OTQiCiAgICAgaW5rc2NhcGU6d2l'
                . 'uZG93LXdpZHRoPSIxOTIwIgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9'
                . 'IjExMzAiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjE5MjAiCiAgICAgaW5rc'
                . '2NhcGU6d2luZG93LXk9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LW1heGltaX'
                . 'plZD0iMSIKICAgICBpbmtzY2FwZTpjdXJyZW50LWxheWVyPSJzdmc1OSIgLz4'
                . 'KICA8cGF0aAogICAgIHN0cm9rZT0iIzA2NmRhNSIKICAgICBzdHJva2Utd2lk'
                . 'dGg9IjM4IgogICAgIGQ9Ik0yOTYgMjI2aDQybS05MiAwaDQybS05MSAwaDQyb'
                . 'S05MSAwaDQxbS05MSAwaDQybTgtNDZoNDFtOCAwaDQybTcgMGg0Mm0tNDItND'
                . 'ZoNDIiCiAgICAgaWQ9InBhdGg1NSIgLz4KICA8cGF0aAogICAgIGZpbGw9IiM'
                . 'wNjZkYTUiCiAgICAgZD0ibTQ3MiAyMjhzLTE4LTE3LTU1LTExYy00LTI5LTM1'
                . 'LTQ2LTM1LTQ2cy0yOSAzNS04IDc0Yy02IDMtMTYgNy0zMSA3SDY4Yy01IDE5L'
                . 'TUgMTQ1IDEzMyAxNDUgOTkgMCAxNzMtNDYgMjA4LTEzMCA1MiA0IDYzLTM5ID'
                . 'YzLTM5IgogICAgIGlkPSJwYXRoNTciIC8+Cjwvc3ZnPgo=';
    }

    public function getErrorOutput()
    {
    }

    public function getExitCode()
    {
    }

    public function getOutput()
    {
    }
}
