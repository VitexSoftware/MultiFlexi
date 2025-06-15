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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use MultiFlexi\Token;

/**
 * Description of TokenCommand.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
// Přidání TokenCommand pro správu tokenů
class TokenCommand extends Command
{
    protected static $defaultName = 'token';
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Manage tokens')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|generate|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Token ID')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'User ID')
            ->addOption('token', null, InputOption::VALUE_REQUIRED, 'Token value');
        // Add more options as needed
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $token = new Token();
                $tokens = $token->listingQuery()->fetchAll();
                if ($format === 'json') {
                    $output->writeln(json_encode($tokens, JSON_PRETTY_PRINT));
                } else {
                    foreach ($tokens as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for token get</error>');

                    return Command::FAILURE;
                }

                $token = new Token((int) $id);
                $data = $token->getData();
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

                foreach (['user', 'token'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data['user'])) {
                    $output->writeln('<error>Missing --user for token create</error>');

                    return Command::FAILURE;
                }

                $token = new \MultiFlexi\Token();
                $token->takeData($data);
                $tokenId = $token->saveToSQL();
                $output->writeln(json_encode(['token_id' => $tokenId], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'generate':
                $user = $input->getOption('user');

                if (empty($user)) {
                    $output->writeln('<error>Missing --user for token generate</error>');

                    return Command::FAILURE;
                }

                $token = new \MultiFlexi\Token();
                $token->setDataValue('user', $user);
                $token->generate();
                $tokenId = $token->saveToSQL();
                $output->writeln(json_encode(['token_id' => $tokenId, 'token' => $token->getDataValue('token')], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for token update</error>');

                    return Command::FAILURE;
                }

                $data = [];

                foreach (['user', 'token'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return Command::FAILURE;
                }

                $token = new \MultiFlexi\Token((int) $id);
                $token->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return Command::FAILURE;
        }
    }
}
