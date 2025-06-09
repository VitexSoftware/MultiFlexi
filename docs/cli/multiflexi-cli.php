#!/usr/bin/env php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MultiFlexi\Job;
use MultiFlexi\Company;

class JobCommand extends Command
{
    protected static $defaultName = 'job';

    protected function configure()
    {
        $this
            ->setDescription('Manage jobs')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Job ID')
            ->addOption('runtemplate_id', null, InputOption::VALUE_REQUIRED, 'Runtemplate ID')
            ->addOption('scheduled', null, InputOption::VALUE_REQUIRED, 'Scheduled datetime')
            ->addOption('executor', null, InputOption::VALUE_REQUIRED, 'Executor')
            ->addOption('schedule_type', null, InputOption::VALUE_REQUIRED, 'Schedule type')
            ->addOption('app_id', null, InputOption::VALUE_REQUIRED, 'App ID')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $job = new Job();
                $jobs = $job->listingQuery()->fetchAll();
                $output->writeln(json_encode($jobs, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for job get</error>');
                    return Command::FAILURE;
                }
                $job = new Job((int)$id);
                $output->writeln(json_encode($job->getData(), JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'create':
                $runtemplateId = $input->getOption('runtemplate_id');
                $scheduled = $input->getOption('scheduled');
                if (empty($runtemplateId) || empty($scheduled)) {
                    $output->writeln('<error>Missing --runtemplate_id or --scheduled for job create</error>');
                    return Command::FAILURE;
                }
                $env = new \MultiFlexi\ConfigFields('Job Env');
                $scheduledDT = new DateTime($scheduled);
                $executor = $input->getOption('executor') ?? 'Native';
                $scheduleType = $input->getOption('schedule_type') ?? 'adhoc';
                $job = new Job();
                $jobId = $job->newJob((int)$runtemplateId, $env, $scheduledDT, $executor, $scheduleType);
                $output->writeln(json_encode(['job_id' => $jobId], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for job update</error>');
                    return Command::FAILURE;
                }
                $job = new Job((int)$id);
                $data = [];
                foreach (['runtemplate_id', 'scheduled', 'executor', 'schedule_type', 'app_id'] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }
                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');
                    return Command::FAILURE;
                }
                $job->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

// Přidání CompanyCommand pro symetrické ovládání jako JobCommand
class CompanyCommand extends Command
{
    protected static $defaultName = 'company';

    protected function configure()
    {
        $this
            ->setDescription('Manage companies')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Company ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Company name')
            ->addOption('customer', null, InputOption::VALUE_REQUIRED, 'Customer')
            ->addOption('server', null, InputOption::VALUE_REQUIRED, 'Server')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $company = new Company();
                $companies = $company->listingQuery()->fetchAll();
                $output->writeln(json_encode($companies, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company get</error>');
                    return Command::FAILURE;
                }
                $company = new Company((int)$id);
                $output->writeln(json_encode($company->getData(), JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'create':
                $data = [];
                foreach (['name', 'customer', 'server'] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }
                if (empty($data['name'])) {
                    $output->writeln('<error>Missing --name for company create</error>');
                    return Command::FAILURE;
                }
                $company = new Company();
                $company->takeData($data);
                $companyId = $company->saveToSQL();
                $output->writeln(json_encode(['company_id' => $companyId], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company update</error>');
                    return Command::FAILURE;
                }
                $data = [];
                foreach (['name', 'customer', 'server'] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }
                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');
                    return Command::FAILURE;
                }
                $company = new Company((int)$id);
                $company->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

$application = new Application('MultiFlexi CLI');
$application->add(new JobCommand());
$application->add(new CompanyCommand());
$application->run();
