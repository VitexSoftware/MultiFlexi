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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MultiFlexi\RunTemplate;

/**
 * Description of RunTemplateCommand.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
// Přidání RunTemplateCommand pro správu runtemplate
class RunTemplateCommand extends Command
{
    protected static $defaultName = 'runtemplate';
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Manage runtemplates')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'RunTemplate ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name')
            ->addOption('app_id', null, InputOption::VALUE_REQUIRED, 'App ID')
            ->addOption('company_id', null, InputOption::VALUE_REQUIRED, 'Company ID')
            ->addOption('interv', null, InputOption::VALUE_REQUIRED, 'Interval code')
            ->addOption('active', null, InputOption::VALUE_REQUIRED, 'Active');
        // Add more options as needed
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $rt = new RunTemplate();
                $rts = $rt->listingQuery()->fetchAll();
                if ($format === 'json') {
                    $output->writeln(json_encode($rts, JSON_PRETTY_PRINT));
                } else {
                    foreach ($rts as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for runtemplate get</error>');

                    return Command::FAILURE;
                }

                $rt = new RunTemplate((int) $id);
                $data = $rt->getData();
                if ($format === 'json') {
                    $output->writeln(json_encode($data, JSON_PRETTY_PRINT));
                } else {
                    foreach ($data as $k => $v) {
                        $output->writeln("$k: $v");
                    }
                }

                return Command::SUCCESS;
            case 'create':
                $data = [];

                foreach (['name', 'app_id', 'company_id', 'interv', 'active'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data['name']) || empty($data['app_id']) || empty($data['company_id'])) {
                    $output->writeln('<error>Missing --name, --app_id or --company_id for runtemplate create</error>');

                    return Command::FAILURE;
                }

                $rt = new \MultiFlexi\RunTemplate();
                $rt->takeData($data);
                $rtId = $rt->saveToSQL();
                $output->writeln(json_encode(['runtemplate_id' => $rtId], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for runtemplate update</error>');

                    return Command::FAILURE;
                }

                $data = [];

                foreach (['name', 'app_id', 'company_id', 'interv', 'active'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return Command::FAILURE;
                }

                $rt = new \MultiFlexi\RunTemplate((int) $id);
                $rt->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for runtemplate delete</error>');

                    return Command::FAILURE;
                }

                $rt = new \MultiFlexi\RunTemplate((int) $id);
                $rt->deleteFromSQL();
                $output->writeln(json_encode(['deleted' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return Command::FAILURE;
        }
    }
}
