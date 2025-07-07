<?php
namespace MultiFlexi\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ease\Shared;
use Ease\Logger;

class PruneCommand extends Command
{
    protected static $defaultName = 'prune';
    protected static $defaultDescription = 'Prune logs and jobs, keeping only the latest N records (default: 1000)';

    protected function configure(): void
    {
        $this
            ->addOption('logs', null, InputOption::VALUE_NONE, 'Prune logs table')
            ->addOption('jobs', null, InputOption::VALUE_NONE, 'Prune jobs table')
            ->addOption('keep', null, InputOption::VALUE_OPTIONAL, 'Number of records to keep', 1000);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keep = (int)$input->getOption('keep');
        $pruned = false;

        if ($input->getOption('logs')) {
            $pruned = true;
            $output->writeln("Pruning logs, keeping latest $keep records...");
            $this->pruneTable('log', 'id', $keep, $output);
        }
        if ($input->getOption('jobs')) {
            $pruned = true;
            $output->writeln("Pruning jobs, keeping latest $keep records...");
            $this->pruneTable('job', 'id', $keep, $output);
        }
        if (!$pruned) {
            $output->writeln('<error>No table specified. Use --logs and/or --jobs.</error>');
            return Command::FAILURE;
        }
        $output->writeln('<info>Prune completed.</info>');
        return Command::SUCCESS;
    }

    private function pruneTable(string $table, string $idField, int $keep, OutputInterface $output): void
    {
        $db = new \Ease\SQL\Engine();
        $sql = "DELETE FROM $table WHERE $idField NOT IN (SELECT $idField FROM (SELECT $idField FROM $table ORDER BY $idField DESC LIMIT $keep) as t)";
        $deleted = $db->getPdo( )->exec($sql);
        $output->writeln("Deleted $deleted records from $table.");
    }
}