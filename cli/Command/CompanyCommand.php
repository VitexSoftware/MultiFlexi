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
class CompanyCommand extends Command
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
            ->addOption('customer', null, InputOption::VALUE_REQUIRED, 'Customer')
            ->addOption('server', null, InputOption::VALUE_REQUIRED, 'Server')
            ->addOption('enabled', null, InputOption::VALUE_REQUIRED, 'Enabled (true/false)')
            ->addOption('settings', null, InputOption::VALUE_REQUIRED, 'Settings')
            ->addOption('logo', null, InputOption::VALUE_REQUIRED, 'Logo')
            ->addOption('ic', null, InputOption::VALUE_REQUIRED, 'IC')
            ->addOption('company', null, InputOption::VALUE_REQUIRED, 'Company Code')
            ->addOption('rw', null, InputOption::VALUE_REQUIRED, 'Write permissions (true/false)')
            ->addOption('setup', null, InputOption::VALUE_REQUIRED, 'Setup (true/false)')
            ->addOption('webhook', null, InputOption::VALUE_REQUIRED, 'Webhook ready (true/false)')
            ->addOption('DatCreate', null, InputOption::VALUE_REQUIRED, 'Created date (date-time)')
            ->addOption('DatUpdate', null, InputOption::VALUE_REQUIRED, 'Updated date (date-time)')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('code', null, InputOption::VALUE_REQUIRED, 'Code');
        // Add more options as needed
    }

    /**
     * Convert string option to boolean if needed
     */
    protected function parseBoolOption($val)
    {
        if (is_bool($val)) return $val;
        if (is_null($val)) return null;
        $val = strtolower((string)$val);
        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

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

                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company get</error>');

                    return Command::FAILURE;
                }

                $company = new Company((int) $id);
                $data = $company->getData();

                if ($format === 'json') {
                    $output->writeln(json_encode($data, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($data as $k => $v) {
                        $output->writeln("{$k}: {$v}");
                    }
                }

                return Command::SUCCESS;
            case 'create':
                $data = [];

                foreach ([
                    'name', 'customer', 'server', 'enabled', 'settings', 'logo', 'ic', 'company', 'rw', 'setup', 'webhook', 'DatCreate', 'DatUpdate', 'email', 'code'
                ] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        if (in_array($field, ['enabled', 'rw', 'setup', 'webhook'])) {
                            $data[$field] = $this->parseBoolOption($val);
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                if (empty($data['name'])) {
                    $output->writeln('<error>Missing --name for company create</error>');

                    return Command::FAILURE;
                }

                $company = new Company();
                $company->takeData($data);
                $companyId = $company->saveToSQL();
                $output->writeln(json_encode(['company_id' => $companyId], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company update</error>');

                    return Command::FAILURE;
                }

                $data = [];

                foreach ([
                    'name', 'customer', 'server', 'enabled', 'settings', 'logo', 'ic', 'company', 'rw', 'setup', 'webhook', 'DatCreate', 'DatUpdate', 'email', 'code'
                ] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        if (in_array($field, ['enabled', 'rw', 'setup', 'webhook'])) {
                            $data[$field] = $this->parseBoolOption($val);
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return Command::FAILURE;
                }

                $company = new Company((int) $id);
                $company->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'remove':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company remove</error>');
                    return Command::FAILURE;
                }
                $company = new Company((int) $id);
                $company->deleteFromSQL();
                $output->writeln(json_encode(['company_id' => $id, 'removed' => true], \JSON_PRETTY_PRINT));
                return Command::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return Command::FAILURE;
        }
    }
}
