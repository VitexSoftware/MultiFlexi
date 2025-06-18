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

use MultiFlexi\Application;
use MultiFlexi\RunTemplate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyAppCommand extends MultiFlexiCommand
{
    protected static $defaultName = 'companyapp';

    protected function configure(): void
    {
        $this
            ->setDescription('Manage company-application relations')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Relation ID')
            ->addOption('company_id', null, InputOption::VALUE_REQUIRED, 'Company ID')
            ->addOption('app_id', null, InputOption::VALUE_REQUIRED, 'Application ID')
            ->addOption('app_uuid', null, InputOption::VALUE_REQUIRED, 'Application UUID')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        if ($action === 'list') {
            $companyId = $input->getOption('company_id');
            $appId = $input->getOption('app_id');
            $appUuid = $input->getOption('app_uuid');

            if (empty($companyId) || (empty($appId) && empty($appUuid))) {
                $output->writeln('<error>--company_id and either --app_id or --app_uuid are required for listing runtemplates.</error>');

                return Command::FAILURE;
            }

            if (!empty($appUuid)) {
                $app = new Application();
                $found = $app->listingQuery()->where(['uuid' => $appUuid])->fetch();

                if (!$found) {
                    $output->writeln('<error>No application found with given UUID</error>');

                    return Command::FAILURE;
                }

                $appId = $found['id'];
            }

            $runTemplate = new RunTemplate();
            $query = $runTemplate->listingQuery()->where([
                'company_id' => $companyId,
                'app_id' => $appId,
            ]);
            $runtemplates = $query->fetchAll();

            if ($format === 'json') {
                $output->writeln(json_encode($runtemplates, \JSON_PRETTY_PRINT));
            } else {
                foreach ($runtemplates as $row) {
                    $output->writeln(implode(' | ', $row));
                }
            }

            return Command::SUCCESS;
        }

        // TODO: Implement logic for get, create, update, delete
        $output->writeln('<info>companyapp command is not yet implemented for this action.</info>');

        return Command::SUCCESS;
    }
}
