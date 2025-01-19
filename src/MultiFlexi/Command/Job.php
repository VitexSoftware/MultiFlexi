<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Job
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
class Job  extends Command {
    protected function configure(): void
    {
        $this
            ->setName('job')
            ->setDescription('Job operations')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command manage Jobs');
    }
}
