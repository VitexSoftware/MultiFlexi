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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ApplicationCommand.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
// Přidání ApplicationCommand pro správu aplikací
class ApplicationCommand extends Command
{
    protected static $defaultName = 'application';
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Manage applications')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete|import-json|export-json|remove-json')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Application ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name')
            ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Description')
            ->addOption('appversion', null, InputOption::VALUE_REQUIRED, 'Application Version')
            ->addOption('topics', null, InputOption::VALUE_REQUIRED, 'Topics')
            ->addOption('executable', null, InputOption::VALUE_REQUIRED, 'Executable')
            ->addOption('uuid', null, InputOption::VALUE_REQUIRED, 'UUID')
            ->addOption('ociimage', null, InputOption::VALUE_REQUIRED, 'OCI Image')
            ->addOption('requirements', null, InputOption::VALUE_REQUIRED, 'Requirements')
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'Path to JSON file for import/export/remove');
        // Add more options as needed
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $app = new Application();
                $apps = $app->listingQuery()->fetchAll();

                if ($format === 'json') {
                    $output->writeln(json_encode($apps, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($apps as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');
                if (empty($id) && empty($uuid)) {
                    $output->writeln('<error>Missing --id or --uuid for application get</error>');
                    return Command::FAILURE;
                }
                if (!empty($uuid)) {
                    $app = new \MultiFlexi\Application();
                    $found = $app->listingQuery()->where(['uuid' => $uuid])->fetch();
                    if (!$found) {
                        $output->writeln('<error>No application found with given UUID</error>');
                        return Command::FAILURE;
                    }
                    $id = $found['id'];
                }
                $app = new \MultiFlexi\Application((int)$id);
                $output->writeln(json_encode($app->getData(), JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'create':
                $data = [];

                foreach (['name', 'description', 'appversion', 'topics', 'executable', 'uuid', 'ociimage', 'requirements'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data['name']) || empty($data['uuid'])) {
                    $output->writeln('<error>Missing --name or --uuid for application create</error>');

                    return Command::FAILURE;
                }

                $app = new \MultiFlexi\Application();
                $app->takeData($data);
                $appId = $app->saveToSQL();
                $output->writeln(json_encode(['application_id' => $appId], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');
                if (empty($id) && empty($uuid)) {
                    $output->writeln('<error>Missing --id or --uuid for application update</error>');
                    return Command::FAILURE;
                }
                if (!empty($uuid)) {
                    $app = new \MultiFlexi\Application();
                    $found = $app->listingQuery()->where(['uuid' => $uuid])->fetch();
                    if (!$found) {
                        $output->writeln('<error>No application found with given UUID</error>');
                        return Command::FAILURE;
                    }
                    $id = $found['id'];
                }

                $data = [];

                foreach (['name', 'description', 'appversion', 'topics', 'executable', 'uuid', 'ociimage', 'requirements'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return Command::FAILURE;
                }

                $app = new \MultiFlexi\Application((int) $id);
                $app->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for application delete</error>');

                    return Command::FAILURE;
                }

                $app = new \MultiFlexi\Application((int) $id);
                $app->deleteFromSQL();
                $output->writeln(json_encode(['deleted' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'import-json':
                $json = $input->getOption('json');

                if (empty($json) || !file_exists($json)) {
                    $output->writeln('<error>Missing or invalid --json file for import-json</error>');

                    return Command::FAILURE;
                }

                $app = new \MultiFlexi\Application();
                $result = $app->importAppJson($json);
                $output->writeln(json_encode(['imported' => $result], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'export-json':
                $id = $input->getOption('id');
                $json = $input->getOption('json');

                if (empty($id) || empty($json)) {
                    $output->writeln('<error>Missing --id or --json for export-json</error>');

                    return Command::FAILURE;
                }

                $app = new \MultiFlexi\Application((int) $id);
                $jsonData = $app->getAppJson();
                file_put_contents($json, $jsonData);
                $output->writeln(json_encode(['exported' => true, 'file' => $json], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'remove-json':
                $json = $input->getOption('json');

                if (empty($json) || !file_exists($json)) {
                    $output->writeln('<error>Missing or invalid --json file for remove-json</error>');

                    return Command::FAILURE;
                }

                $app = new \MultiFlexi\Application();
                $result = $app->jsonAppRemove($json);
                $output->writeln(json_encode(['removed' => $result], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return Command::FAILURE;
        }
    }
}
