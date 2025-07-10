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

use MultiFlexi\ScheduleLister;
use MultiFlexi\Scheduler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueCommand extends MultiFlexiCommand
{
    protected static $defaultName = 'queue';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setName('queue')
            ->setDescription('Queue operations (list, truncate)')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|truncate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $lister = new ScheduleLister();
                $rows = $lister->listingQuery()->fetchAll();

                if ($format === 'json') {
                    $output->writeln(json_encode($rows, \JSON_PRETTY_PRINT));
                } else {
                    $this->outputTable($rows);
                }

                return self::SUCCESS;
            case 'truncate':
                $scheduler = new Scheduler();
                $waiting = $scheduler->listingQuery()->count();
                $pdo = $scheduler->getFluentPDO()->getPdo();
                $table = $scheduler->getMyTable();
                // Check the database driver to use the appropriate truncate/delete command
                $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

                if ($driver === 'sqlite') {
                    // For SQLite, use DELETE FROM and reset the sequence if needed
                    $result = $pdo->exec('DELETE FROM '.$table);
                    // Reset AUTOINCREMENT sequence if table has one
                    $pdo->exec('DELETE FROM sqlite_sequence WHERE name="'.$table.'"');
                } else {
                    // For MySQL and others, use TRUNCATE TABLE
                    $result = $pdo->exec('TRUNCATE TABLE '.$table);
                }

                $msg = ($result !== false)
                    ? ("Queue truncated. Previously waiting jobs: {$waiting}.")
                    : 'Failed to truncate queue.';

                if ($format === 'json') {
                    $output->writeln(json_encode(['result' => $msg, 'waiting' => $waiting], \JSON_PRETTY_PRINT));
                } else {
                    $output->writeln("Jobs waiting before truncate: {$waiting}");
                    $output->writeln($msg);
                }

                return ($result !== false) ? self::SUCCESS : self::FAILURE;

            default:
                $output->writeln('<error>Unknown action for queue: '.$action.'</error>');

                return self::FAILURE;
        }
    }
}
