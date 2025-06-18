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

namespace MultiFlexi\Cli\Command;

use Ease\Shared;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Status.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class AppStatusCommand extends MultiFlexiCommand
{
    protected function configure(): void
    {
        $this
            ->setName('appstatus')
            ->setDescription('Prints App Status')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command prints overall MultiFlexi status');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = $input->getOption('format');

        $engine = new \MultiFlexi\Engine();
        $pdo = $engine->getPdo();

        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $database = $driver.' '.\Ease\Shared::cfg('DB_DATABASE');
        } else {
            $database = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME).' '.
                    $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS).' '.
                    $pdo->getAttribute(\PDO::ATTR_SERVER_INFO).' '.
                    $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        }

        $status = [
            'version' => Shared::appVersion(),
            'php' => \PHP_VERSION,
            'os' => \PHP_OS,
            'memory' => memory_get_usage(),
            'companies' => $engine->getFluentPDO()->from('company')->count(),
            'apps' => $engine->getFluentPDO()->from('apps')->count(),
            'runtemplates' => $engine->getFluentPDO()->from('runtemplate')->count(),
            'topics' => $engine->getFluentPDO()->from('topic')->count(),
            'credentials' => $engine->getFluentPDO()->from('credentials')->count(),
            'credential_types' => $engine->getFluentPDO()->from('credential_type')->count(),
            'database' => $database,
            'status' => \MultiFlexi\Runner::isServiceActive('multiflexi.service') ? 'running' : 'stopped',
            'timestamp' => date('c'),
        ];

        if ($format === 'json') {
            $output->writeln(json_encode($status, \JSON_PRETTY_PRINT));
        } else {
            foreach ($status as $key => $value) {
                $output->writeln(str_replace('_', ' ', $key).': '.$value);
            }
        }

        return MultiFlexiCommand::SUCCESS;
    }
}
