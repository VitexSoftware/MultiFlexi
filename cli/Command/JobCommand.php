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

use MultiFlexi\Job;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Job.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class JobCommand extends Command
{
    protected static $defaultName = 'job';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    #[\Override]
    public function listing(): array
    {
        $engine = new self();

        return $engine->listingQuery()->select([
            'id',
        ])->fetchAll();
    }

    protected function configure(): void
    {
        $this
            ->setName('job')
            ->setDescription('Job operations')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command manage Jobs')
            ->setDescription('Manage jobs')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Job ID')
            ->addOption('runtemplate_id', null, InputOption::VALUE_REQUIRED, 'Runtemplate ID')
            ->addOption('scheduled', null, InputOption::VALUE_REQUIRED, 'Scheduled datetime')
            ->addOption('executor', null, InputOption::VALUE_REQUIRED, 'Executor')
            ->addOption('schedule_type', null, InputOption::VALUE_REQUIRED, 'Schedule type')
            ->addOption('app_id', null, InputOption::VALUE_REQUIRED, 'App ID');
        // Add more options as needed
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $job = new Job();
                $jobs = $job->listingQuery()->fetchAll();

                if ($format === 'json') {
                    $output->writeln(json_encode($jobs, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($jobs as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for job get</error>');

                    return Command::FAILURE;
                }

                $job = new Job((int) $id);
                $data = $job->getData();

                if ($format === 'json') {
                    $output->writeln(json_encode($data, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($data as $k => $v) {
                        $output->writeln("{$k}: {$v}");
                    }
                }

                return Command::SUCCESS;
            case 'create':
                $runtemplateId = $input->getOption('runtemplate_id');
                $scheduled = $input->getOption('scheduled');

                if (empty($runtemplateId) || empty($scheduled)) {
                    $output->writeln('<error>Missing --runtemplate_id or --scheduled for job create</error>');

                    return Command::FAILURE;
                }

                $env = new \MultiFlexi\ConfigFields('Job Env');
                $scheduledDT = new \DateTime($scheduled);
                $executor = $input->getOption('executor') ?? 'Native';
                $scheduleType = $input->getOption('schedule_type') ?? 'adhoc';
                $job = new Job();
                $jobId = $job->newJob((int) $runtemplateId, $env, $scheduledDT, $executor, $scheduleType);
                $output->writeln(json_encode(['job_id' => $jobId], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for job update</error>');

                    return Command::FAILURE;
                }

                $job = new Job((int) $id);
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
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return Command::FAILURE;
        }
    }
}
