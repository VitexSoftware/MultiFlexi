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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Status.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class JobStatus extends MultiFlexiCommand
{
    protected function configure(): void
    {
        $this
            ->setName('jobstatus')
            ->setDescription('Prints Jobs Status')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command prints status of jobs and its schedule');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $engine = new \MultiFlexi\Engine();
        $pdo = $engine->getPdo();
        $database = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME).' '.
                $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS).' '.
                $pdo->getAttribute(\PDO::ATTR_SERVER_INFO).' '.
                $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

        $queeLength = (new \MultiFlexi\Scheduler())->listingQuery()->count();

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

        $status = [
            'successful_jobs' => (int) $result['successful_jobs'],
            'failed_jobs' => (int) $result['failed_jobs'],
            'incomplete_jobs' => (int) $result['incomplete_jobs'],
            'total_applications' => (int) $result['total_applications'],
            'repeated_jobs' => (int) $result['repeated_jobs'],
            'total_jobs' => (int) $result['total_jobs'],
            'queue_length' => (int) $queeLength,
        ];

        $format = $input->getOption('format');

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
