<?php

declare(strict_types=1);

/**
 * Multi Flexi - Run Tasks in container
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Excutor;

/**
 * Description of Podman
 *
 * @author vitex
 */
class Podman extends \Ease\Sand implements \MultiFlexi\executor
{
    use \Ease\Logger\Logging;

    const PULLCOMMAND = 'podman pull docker.io/vitexsoftware/debian:bookworm';

    public $job;

    public function __construct($job)
    {
        $this->job = $job;
    }

    public function launch()
    {
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
}
