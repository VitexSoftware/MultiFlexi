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
class UserCommand extends MultiFlexiCommand
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
            ->addOption('enabled', null, InputOption::VALUE_OPTIONAL, 'Enabled (true/false)')
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

                return MultiFlexiCommand::SUCCESS;
            case 'show':
            case 'get':
                $id = $input->getOption('id');
                $login = $input->getOption('login');
                $email = $input->getOption('email');

                if (!empty($id)) {
                    $user = new User((int) $id);
                } elseif (!empty($login)) {
                    $userObj = new User();
                    $found = $userObj->listingQuery()->where(['login' => $login])->fetch();
                    $user = $found ? new User($found['id']) : null;
                } elseif (!empty($email)) {
                    $userObj = new User();
                    $found = $userObj->listingQuery()->where(['email' => $email])->fetch();
                    $user = $found ? new User($found['id']) : null;
                } else {
                    $output->writeln('<error>Missing --id, --login or --email for user get</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if (empty($user) || empty($user->getData())) {
                    if ($format === 'json') {
                        $output->writeln(json_encode([
                            'status' => 'not found',
                            'message' => 'No user found with given identifier',
                        ], \JSON_PRETTY_PRINT));
                    } else {
                        $output->writeln('<error>No user found with given identifier</error>');
                    }

                    return MultiFlexiCommand::FAILURE;
                }

                if ($format === 'json') {
                    $output->writeln(json_encode($user->getData(), \JSON_PRETTY_PRINT));
                } else {
                    foreach ($user->getData() as $k => $v) {
                        $output->writeln("{$k}: {$v}");
                    }
                }

                return MultiFlexiCommand::SUCCESS;
            case 'create':
                $data = [];

                foreach (['login', 'firstname', 'lastname', 'email', 'enabled'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        if ($field === 'enabled') {
                            $data[$field] = $this->parseBoolOption($val);
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                if (empty($data['login']) || empty($data['email'])) {
                    $output->writeln('<error>Missing --login or --email for user create</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                if ($input->getOption('password')) {
                    $data['password'] = \MultiFlexi\User::encryptPassword($input->getOption('password'));
                }

                $user = new \MultiFlexi\User();
                $user->takeData($data);
                $userId = $user->saveToSQL();

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $full = (new \MultiFlexi\User((int) $userId))->getData();

                    if ($format === 'json') {
                        $output->writeln(json_encode($full, \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($full as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                } else {
                    $output->writeln(json_encode(['user_id' => $userId], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'update':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $data = [];

                foreach (['login', 'firstname', 'lastname', 'email', 'enabled'] as $field) {
                    $val = $input->getOption($field);

                    if ($val !== null) {
                        if ($field === 'enabled') {
                            $data[$field] = $this->parseBoolOption($val);
                        } else {
                            $data[$field] = $val;
                        }
                    }
                }

                if ($input->getOption('password')) {
                    $data['password'] = \MultiFlexi\User::encryptPassword($input->getOption('password'));
                }

                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $user = new \MultiFlexi\User((int) $id);
                $user->updateToSQL($data, ['id' => $id]);

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $full = $user->getData();

                    if ($format === 'json') {
                        $output->writeln(json_encode($full, \JSON_PRETTY_PRINT));
                    } else {
                        foreach ($full as $k => $v) {
                            $output->writeln("{$k}: {$v}");
                        }
                    }
                } else {
                    $output->writeln(json_encode(['updated' => true, 'user_id' => $id], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');

                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user delete</error>');

                    return MultiFlexiCommand::FAILURE;
                }

                $user = new \MultiFlexi\User((int) $id);
                $user->deleteFromSQL();

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln("User deleted: ID={$id}");
                } else {
                    $output->writeln(json_encode(['deleted' => true, 'user_id' => $id], \JSON_PRETTY_PRINT));
                }

                return MultiFlexiCommand::SUCCESS;

            default:
                $output->writeln("<error>Unknown action: {$action}</error>");

                return MultiFlexiCommand::FAILURE;
        }
    }
}
