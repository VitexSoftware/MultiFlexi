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

use MultiFlexi\Company;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of CompanyCommand.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */

// Přidání CompanyCommand pro symetrické ovládání jako JobCommand
class CompanyCommand extends MultiFlexiCommand
{
    protected static $defaultName = 'company';
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Manage companies')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|remove')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Company ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Company name')
            ->addOption('customer', null, InputOption::VALUE_OPTIONAL, 'Customer')
            ->addOption('enabled', null, InputOption::VALUE_OPTIONAL, 'Enabled (true/false)')
            ->addOption('settings', null, InputOption::VALUE_OPTIONAL, 'Settings')
            ->addOption('logo', null, InputOption::VALUE_OPTIONAL, 'Logo')
            ->addOption('ic', null, InputOption::VALUE_OPTIONAL, 'IC')
            ->addOption('DatCreate', null, InputOption::VALUE_REQUIRED, 'Created date (date-time)')
            ->addOption('DatUpdate', null, InputOption::VALUE_REQUIRED, 'Updated date (date-time)')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED, 'Company Slug')
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of fields to display')
            ->addOption('zabbix_host', null, InputOption::VALUE_OPTIONAL, 'Zabbix Host'); // Add the zabbix_host option
        // Add more options as needed
    }

    /**
     * Convert string option to boolean if needed.
     *
     * @param mixed $val
     */
    protected function parseBoolOption($val)
    {
        if (\is_bool($val)) {
            return $val;
        }

        if (null === $val) {
            return null;
        }

        $val = strtolower((string) $val);

        return \in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        // Default action logic: if no id, show list; if id, show record
        if (!\in_array($action, ['create', 'update', 'remove', 'get', 'list'], true)) {
            $id = $input->getOption('id');

            if (empty($id)) {
                $action = 'list';
            } else {
                $action = 'get';
            }
        }

        switch ($action) {
            case 'list':
                $company = new Company();
                $companies = $company->listingQuery()->fetchAll();

                if ($format === 'json') {
                    $output->writeln(json_encode($companies, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($companies as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return MultiFlexiCommand::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                $ic = $input->getOption('ic');
                $name = $input->getOption('name');
                $slug = $input->getOption('slug');

                if (!empty($id)) {
                    $company = new Company((int) $id);
                } elseif (!empty($ic)) {
                    $companyObj = new Company();
                    $found = $companyObj->listingQuery()->where(['ic' => $ic])->fetch();
                    $company = $found ? new Company($found['id']) : null;
                } elseif (!empty($name)) {
                    $companyObj = new Company();
                    $found = $companyObj->listingQuery()->where(['name' => $name])->fetch();
                    $company = $found ? new Company($found['id']) : null;
                } elseif (!empty($slug)) {
                    $companyObj = new Company();
                    $found = $companyObj->listingQuery()->where(['slug' => $slug])->fetch();
                    $company = $found ? new Company($found['id']) : null;
                } else {
                    if ($format === 'json') {
                        $output->writeln(json_encode([
                            'status' => 'error',
                            'message' => 'Missing --id, --ic, --name or --slug for company get',
                        ], \JSON_PRETTY_PRINT));
                    } else {
                        $output->writeln('<error>Missing --id, --ic, --name or --slug for company get</error>');
                    }

                    return MultiFlexiCommand::FAILURE;
                }

                if (empty($company) || empty($company->getData())) {
                    if ($format === 'json') {
                        $output->writeln(json_encode([
                            'status' => 'not found',
                            'message' => 'No company found with given identifier',
                        ], \JSON_PRETTY_PRINT));
                    } else {
                        $output->writeln('<error>No company found with given identifier</error>');
                    }

                    return MultiFlexiCommand::FAILURE;
                }

                $fields = $input->getOption('fields');

                if ($fields) {
                    $fieldsArray = explode(',', $fields);
                    $filteredData = array_filter(
                        $company->getData(),
                        static fn ($key) => \in_array($key, $fieldsArray, true),
                        \ARRAY_FILTER_USE_KEY,
                    );

                    if ($format === 'json') {
                        $output->writeln(json_encode($filteredData, \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($filteredData as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                } else {
                    if ($format === 'json') {
                        $output->writeln(json_encode($company->getData(), \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($company->getData() as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                }

                return MultiFlexiCommand::SUCCESS;
            case 'create':
                $data = [];

                foreach ([
                    'name', 'customer', 'enabled', 'settings', 'logo', 'ic', 'slug', 'DatCreate', 'DatUpdate', 'email',
                ] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        if (\in_array($field, ['enabled'], true)) {
                            $data[$field] = $this->parseBoolOption($val);
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                // Ensure 'slug' field is set: use --slug if given, else slug from name
                if (empty($data['slug']) && !empty($data['name'])) {
                    $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/', '_', $data['name']));
                }

                if (empty($data['name'])) {
                    $output->writeln('<error>Missing --name for company create</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $company = new Company();
                $company->takeData($data);
                $companyId = $company->saveToSQL();

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $full = (new Company((int) $companyId))->getData();

                    if ($format === 'json') {
                        $output->writeln(json_encode($full, \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($full as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                } else {
                    $output->writeln(json_encode(['company_id' => $companyId], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $data = [];

                foreach ([
                    'name', 'customer', 'enabled', 'settings', 'logo', 'ic', 'DatCreate', 'DatUpdate', 'email',
                ] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        if (\in_array($field, ['enabled'], true)) {
                            $data[$field] = $this->parseBoolOption($val);
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $company = new Company((int) $id);
                $current = $company->getData();
                $changed = false;

                foreach ($data as $k => $v) {
                    if (!\array_key_exists($k, $current) || $current[$k] !== $v) {
                        $changed = true;

                        break;
                    }
                }

                if (!$changed) {
                    $output->writeln(json_encode(['updated' => false, 'company_id' => $id, 'message' => 'No changes detected'], \JSON_PRETTY_PRINT));

                    return MultiFlexiCommand::SUCCESS;
                }

                $company->updateToSQL($data, ['id' => $id]);

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL || $input->getParameterOption(['--verbose', '-v'], false)) {
                    $full = $company->getData();

                    if ($format === 'json') {
                        $output->writeln(json_encode($full, \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($full as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                } else {
                    $output->writeln(json_encode(['updated' => true, 'company_id' => $id], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'remove':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company remove</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $company = new Company((int) $id);
                $company->deleteFromSQL();

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln("Company removed: ID={$id}");
                } else {
                    $output->writeln(json_encode(['company_id' => $id, 'removed' => true], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return MultiFlexiCommand::FAILURE;
        }
    }
}
