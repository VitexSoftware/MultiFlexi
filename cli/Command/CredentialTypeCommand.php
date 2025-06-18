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

use MultiFlexi\CredentialType as CredentialTypeModel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of CredentialType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialTypeCommand extends MultiFlexiCommand
{
    protected static $defaultName = 'credtype';
    protected function configure(): void
    {
        $this
            ->setName('credtype')
            ->setDescription('Credential type operations')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Credential Type ID')
            ->addOption('uuid', null, InputOption::VALUE_REQUIRED, 'Credential Type UUID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command manages Credential Types');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $credType = new CredentialTypeModel();
                $types = $credType->listingQuery()->fetchAll();

                if ($format === 'json') {
                    $output->writeln(json_encode($types, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($types as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return MultiFlexiCommand::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');

                if (empty($id) && empty($uuid)) {
                    $output->writeln('<error>Missing --id or --uuid for credtype get</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if (!empty($uuid)) {
                    $credType = new CredentialTypeModel();
                    $found = $credType->listingQuery()->where(['uuid' => $uuid])->fetch();

                    if (!$found) {
                        $output->writeln('<error>No credential type found with given UUID</error>');

                        return MultiFlexiCommand::FAILURE;
                    }

                    $id = $found['id'];
                }

                $credType = new CredentialTypeModel((int) $id);
                $data = $credType->getData();

                if ($format === 'json') {
                    $output->writeln(json_encode($data, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($data as $k => $v) {
                        $output->writeln("{$k}: {$v}");
                    }
                }

                return MultiFlexiCommand::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                $uuid = $input->getOption('uuid');

                if (empty($id) && empty($uuid)) {
                    $output->writeln('<error>Missing --id or --uuid for credtype update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if (!empty($uuid)) {
                    $credType = new CredentialTypeModel();
                    $found = $credType->listingQuery()->where(['uuid' => $uuid])->fetch();

                    if (!$found) {
                        $output->writeln('<error>No credential type found with given UUID</error>');

                        return MultiFlexiCommand::FAILURE;
                    }

                    $id = $found['id'];
                }

                $data = [];

                foreach (['name'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $credType = new CredentialTypeModel((int) $id);
                $credType->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return MultiFlexiCommand::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return MultiFlexiCommand::FAILURE;
        }
    }
}
