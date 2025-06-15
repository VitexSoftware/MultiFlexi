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

/**
 * Description of UserCommand.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
use MultiFlexi\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Přidání UserCommand pro správu uživatelů
class UserCommand extends Command
{
    protected static $defaultName = 'user';
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    #[\Override]
    public function listing(): array
    {
        $engine = new \MultiFlexi\User();

        return $engine->listingQuery()->select([
            'id',
            'enabled',
            'login',
            'email',
            'firstname',
            'lastname',
        ], true)->fetchAll();
    }

    protected function configure(): void
    {
        $this
            ->setName('user')
            ->setDescription('Manage users')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'User ID')
            ->addOption('login', null, InputOption::VALUE_REQUIRED, 'Login')
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED, 'First name')
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED, 'Last name')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password')
            ->setHelp('This command manage Jobs');
        // Add more options as needed
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = strtolower($input->getOption('format'));
        $action = strtolower($input->getArgument('action'));

        switch ($action) {
            case 'list':
                $user = new User();
                $users = $user->listingQuery()->fetchAll();

                if ($format === 'json') {
                    $output->writeln(json_encode($users, \JSON_PRETTY_PRINT));
                } else {
                    foreach ($users as $row) {
                        $output->writeln(implode(' | ', $row));
                    }
                }

                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user get</error>');

                    return Command::FAILURE;
                }

                $user = new User((int) $id);
                $data = $user->getData();

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

                foreach (['login', 'firstname', 'lastname', 'email'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if (empty($data['login']) || empty($data['email'])) {
                    $output->writeln('<error>Missing --login or --email for user create</error>');

                    return Command::FAILURE;
                }

                if ($input->getOption('password')) {
                    $data['password'] = \MultiFlexi\User::encryptPassword($input->getOption('password'));
                }

                $user = new \MultiFlexi\User();
                $user->takeData($data);
                $userId = $user->saveToSQL();
                $output->writeln(json_encode(['user_id' => $userId], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user update</error>');

                    return Command::FAILURE;
                }

                $data = [];

                foreach (['login', 'firstname', 'lastname', 'email'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }

                if ($input->getOption('password')) {
                    $data['password'] = \MultiFlexi\User::encryptPassword($input->getOption('password'));
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return Command::FAILURE;
                }

                $user = new \MultiFlexi\User((int) $id);
                $user->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user delete</error>');

                    return Command::FAILURE;
                }

                $user = new \MultiFlexi\User((int) $id);
                $user->deleteFromSQL();
                $output->writeln(json_encode(['deleted' => true], \JSON_PRETTY_PRINT));

                return Command::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return Command::FAILURE;
        }
    }
}
