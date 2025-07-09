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
class ApplicationCommand extends MultiFlexiCommand
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
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete|import-json|export-json|remove-json|showconfig')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Application ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name')
            ->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Description')
            ->addOption('topics', null, InputOption::VALUE_REQUIRED, 'Topics')
            ->addOption('executable', null, InputOption::VALUE_REQUIRED, 'Executable')
            ->addOption('uuid', null, InputOption::VALUE_REQUIRED, 'UUID')
            ->addOption('ociimage', null, InputOption::VALUE_OPTIONAL, 'OCI Image')
            ->addOption('requirements', null, InputOption::VALUE_OPTIONAL, 'Requirements')
            ->addOption('homepage', null, InputOption::VALUE_OPTIONAL, 'Homepage URL')
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'Path to JSON file for import/export/remove')
            ->addOption('appversion', null, InputOption::VALUE_OPTIONAL, 'Application Version');
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

                return MultiFlexiCommand::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');
                $name = $input->getOption('name');

                if (!empty($id)) {
                    $application = new Application((int) $id);
                } elseif (!empty($uuid)) {
                    $appObj = new Application();
                    $found = $appObj->listingQuery()->where(['uuid' => $uuid])->fetch();
                    $application = $found ? new Application($found['id']) : null;
                } elseif (!empty($name)) {
                    $appObj = new Application();
                    $found = $appObj->listingQuery()->where(['name' => $name])->fetch();
                    $application = $found ? new Application($found['id']) : null;
                } else {
                    $output->writeln('<error>Missing --id, --uuid or --name for application get</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if (empty($application) || empty($application->getData())) {
                    if ($format === 'json') {
                        $output->writeln(json_encode([
                            'status' => 'not found',
                            'message' => 'No application found with given identifier',
                        ], \JSON_PRETTY_PRINT));
                    } else {
                        $output->writeln('<error>No application found with given identifier</error>');
                    }

                    return MultiFlexiCommand::FAILURE;
                }

                if ($format === 'json') {
                    $output->writeln(json_encode($application->getData(), \JSON_PRETTY_PRINT));
                } else {
                    foreach ($application->getData() as $k => $v) {
                        $output->writeln("{$k}: {$v}");
                    }
                }

                return MultiFlexiCommand::SUCCESS;
            case 'create':
                $data = [];

                foreach ([
                    'name', 'description', 'appversion', 'topics', 'executable', 'uuid', 'ociimage', 'requirements', 'homepage',
                ] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        // Map appversion CLI option to version DB field
                        if ($field === 'appversion') {
                            $data['version'] = $val;
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                // Set default value for 'image' if not provided
                if (!isset($data['image'])) {
                    $data['image'] = '';
                }

                if (empty($data['name']) || empty($data['uuid']) || empty($data['executable'])) {
                    $output->writeln('<error>Missing --name, --uuid or --executable for application create</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                // Check if application already exists (by uuid or name)
                $appCheck = new \MultiFlexi\Application();
                $exists = $appCheck->listingQuery()->where(['uuid' => $data['uuid']])->fetch();

                if ($exists) {
                    $warningMsg = 'Application with this UUID already exists.';

                    if ($format === 'json') {
                        $output->writeln(json_encode(['status' => 'warning', 'message' => $warningMsg], \JSON_PRETTY_PRINT));
                    } else {
                        $output->writeln('<comment>'.$warningMsg.'</comment>');
                    }

                    return MultiFlexiCommand::FAILURE;
                }

                $app = new \MultiFlexi\Application();
                $app->takeData($data);
                $appId = $app->saveToSQL();

                // Print info about application created if --format json and --verbose
                if ($format === 'json' && $output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $full = (new \MultiFlexi\Application((int) $appId))->getData();
                    $output->writeln(json_encode(['status' => 'created', 'application' => $full], \JSON_PRETTY_PRINT));
                } elseif ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $full = (new \MultiFlexi\Application((int) $appId))->getData();

                    foreach ($full as $k => $v) {
                        $output->writeln("{$k}: {$v}");
                    }
                } else {
                    $output->writeln(json_encode(['application_id' => $appId], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');

                if (empty($id) && empty($uuid)) {
                    $output->writeln('<error>Missing --id or --uuid for application update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if (!empty($uuid)) {
                    $app = new \MultiFlexi\Application();
                    $found = $app->listingQuery()->where(['uuid' => $uuid])->fetch();

                    if (!$found) {
                        $output->writeln('<error>No application found with given UUID</error>');

                        return MultiFlexiCommand::FAILURE;
                    }

                    $id = $found['id'];
                }

                $data = [];

                foreach ([
                    'name', 'description', 'appversion', 'topics', 'executable', 'uuid', 'ociimage', 'requirements', 'homepage',
                ] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        if ($field === 'appversion') {
                            $data['version'] = $val;
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $app = new \MultiFlexi\Application((int) $id);
                $app->updateToSQL($data, ['id' => $id]);

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $full = $app->getData();

                    if ($format === 'json') {
                        $output->writeln(json_encode($full, \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($full as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                } else {
                    $output->writeln(json_encode(['updated' => true, 'application_id' => $id], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for application delete</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $app = new \MultiFlexi\Application((int) $id);
                $app->deleteFromSQL();

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln("Application deleted: ID={$id}");
                } else {
                    $output->writeln(json_encode(['deleted' => true, 'application_id' => $id], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'import-json':
                $json = $input->getOption('json');

                if (empty($json) || !file_exists($json)) {
                    $output->writeln('<error>Missing or invalid --json file for import-json</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $app = new \MultiFlexi\Application();
                $result = $app->importAppJson($json);
                $output->writeln(json_encode(['imported' => $result], \JSON_PRETTY_PRINT));

                return MultiFlexiCommand::SUCCESS;
            case 'export-json':
                $id = $input->getOption('id');
                $json = $input->getOption('json');

                if (empty($id) || empty($json)) {
                    $output->writeln('<error>Missing --id or --json for export-json</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $app = new \MultiFlexi\Application((int) $id);
                $jsonData = $app->getAppJson();
                file_put_contents($json, $jsonData);
                $output->writeln(json_encode(['exported' => true, 'file' => $json], \JSON_PRETTY_PRINT));

                return MultiFlexiCommand::SUCCESS;
            case 'remove-json':
                $json = $input->getOption('json');

                if (empty($json) || !file_exists($json)) {
                    $output->writeln('<error>Missing or invalid --json file for remove-json</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $app = new \MultiFlexi\Application();
                $result = $app->jsonAppRemove($json);
                $output->writeln(json_encode(['removed' => $result], \JSON_PRETTY_PRINT));

                return MultiFlexiCommand::SUCCESS;
            case 'showconfig':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');

                if (empty($id) && empty($uuid)) {
                    $output->writeln('<error>Missing --id or --uuid for application showconfig</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if (!empty($uuid)) {
                    $app = new \MultiFlexi\Application();
                    $found = $app->listingQuery()->where(['uuid' => $uuid])->fetch();

                    if (!$found) {
                        $output->writeln('<error>No application found with given UUID</error>');

                        return MultiFlexiCommand::FAILURE;
                    }

                    $id = $found['id'];
                }

                $app = new \MultiFlexi\Application((int) $id);
                $fields = $app->getAppEnvironmentFields();
                $result = [];

                foreach ($fields as $field) {
                    $result[] = [
                        'code' => $field->getCode(),
                        'name' => $field->getName(),
                        'type' => $field->getType(),
                        'required' => $field->isRequired() ? 'yes' : 'no',
                        'default' => $field->getDefaultValue(),
                        'description' => $field->getDescription(),
                    ];
                }

                if ($format === 'json') {
                    $output->writeln(json_encode($result, \JSON_PRETTY_PRINT));
                } else {
                    if (empty($result)) {
                        $output->writeln('<info>No configuration fields defined for this application.</info>');
                    } else {
                        $this->outputTable($result);
                    }
                }

                return MultiFlexiCommand::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return MultiFlexiCommand::FAILURE;
        }
    }
}
