#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MultiFlexi\Job;
use MultiFlexi\Company;
use Ease\Anonym;
use Ease\Shared;

class JobCommand extends Command
{
    protected static $defaultName = 'job';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->setDescription('Manage jobs')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Job ID')
            ->addOption('runtemplate_id', null, InputOption::VALUE_REQUIRED, 'Runtemplate ID')
            ->addOption('scheduled', null, InputOption::VALUE_REQUIRED, 'Scheduled datetime')
            ->addOption('executor', null, InputOption::VALUE_REQUIRED, 'Executor')
            ->addOption('schedule_type', null, InputOption::VALUE_REQUIRED, 'Schedule type')
            ->addOption('app_id', null, InputOption::VALUE_REQUIRED, 'App ID')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $job = new Job();
                $jobs = $job->listingQuery()->fetchAll();
                $output->writeln(json_encode($jobs, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for job get</error>');
                    return Command::FAILURE;
                }
                $job = new Job((int)$id);
                $output->writeln(json_encode($job->getData(), JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'create':
                $runtemplateId = $input->getOption('runtemplate_id');
                $scheduled = $input->getOption('scheduled');
                if (empty($runtemplateId) || empty($scheduled)) {
                    $output->writeln('<error>Missing --runtemplate_id or --scheduled for job create</error>');
                    return Command::FAILURE;
                }
                $env = new \MultiFlexi\ConfigFields('Job Env');
                $scheduledDT = new DateTime($scheduled);
                $executor = $input->getOption('executor') ?? 'Native';
                $scheduleType = $input->getOption('schedule_type') ?? 'adhoc';
                $job = new Job();
                $jobId = $job->newJob((int)$runtemplateId, $env, $scheduledDT, $executor, $scheduleType);
                $output->writeln(json_encode(['job_id' => $jobId], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for job update</error>');
                    return Command::FAILURE;
                }
                $job = new Job((int)$id);
                $data = [];
                foreach (['runtemplate_id', 'scheduled', 'executor', 'schedule_type', 'app_id'] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }
                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');
                    return Command::FAILURE;
                }
                $job->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

// Přidání CompanyCommand pro symetrické ovládání jako JobCommand
class CompanyCommand extends Command
{
    protected static $defaultName = 'company';
    public function __construct() { parent::__construct(self::$defaultName); }

    protected function configure()
    {
        $this
            ->setDescription('Manage companies')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Company ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Company name')
            ->addOption('customer', null, InputOption::VALUE_REQUIRED, 'Customer')
            ->addOption('server', null, InputOption::VALUE_REQUIRED, 'Server')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $company = new Company();
                $companies = $company->listingQuery()->fetchAll();
                $output->writeln(json_encode($companies, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company get</error>');
                    return Command::FAILURE;
                }
                $company = new Company((int)$id);
                $output->writeln(json_encode($company->getData(), JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'create':
                $data = [];
                foreach (['name', 'customer', 'server'] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }
                if (empty($data['name'])) {
                    $output->writeln('<error>Missing --name for company create</error>');
                    return Command::FAILURE;
                }
                $company = new Company();
                $company->takeData($data);
                $companyId = $company->saveToSQL();
                $output->writeln(json_encode(['company_id' => $companyId], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for company update</error>');
                    return Command::FAILURE;
                }
                $data = [];
                foreach (['name', 'customer', 'server'] as $field) {
                    $val = $input->getOption($field);
                    if ($val !== null) {
                        $data[$field] = $val;
                    }
                }
                if (empty($data)) {
                    $output->writeln('<error>No fields to update</error>');
                    return Command::FAILURE;
                }
                $company = new Company((int)$id);
                $company->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

// Přidání TokenCommand pro správu tokenů
class TokenCommand extends Command
{
    protected static $defaultName = 'token';
    public function __construct() { parent::__construct(self::$defaultName); }

    protected function configure()
    {
        $this
            ->setDescription('Manage tokens')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|generate|update')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Token ID')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'User ID')
            ->addOption('token', null, InputOption::VALUE_REQUIRED, 'Token value')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $token = new \MultiFlexi\Token();
                $tokens = $token->listingQuery()->fetchAll();
                $output->writeln(json_encode($tokens, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for token get</error>');
                    return Command::FAILURE;
                }
                $token = new \MultiFlexi\Token((int)$id);
                $output->writeln(json_encode($token->getData(), JSON_PRETTY_PRINT));
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
                $output->writeln(json_encode(['token_id' => $tokenId], JSON_PRETTY_PRINT));
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
                $output->writeln(json_encode(['token_id' => $tokenId, 'token' => $token->getDataValue('token')], JSON_PRETTY_PRINT));
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
                $token = new \MultiFlexi\Token((int)$id);
                $token->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

// Přidání RunTemplateCommand pro správu runtemplate
class RunTemplateCommand extends Command
{
    protected static $defaultName = 'runtemplate';
    public function __construct() { parent::__construct(self::$defaultName); }

    protected function configure()
    {
        $this
            ->setDescription('Manage runtemplates')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'RunTemplate ID')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name')
            ->addOption('app_id', null, InputOption::VALUE_REQUIRED, 'App ID')
            ->addOption('company_id', null, InputOption::VALUE_REQUIRED, 'Company ID')
            ->addOption('interv', null, InputOption::VALUE_REQUIRED, 'Interval code')
            ->addOption('active', null, InputOption::VALUE_REQUIRED, 'Active')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $rt = new \MultiFlexi\RunTemplate();
                $rts = $rt->listingQuery()->fetchAll();
                $output->writeln(json_encode($rts, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for runtemplate get</error>');
                    return Command::FAILURE;
                }
                $rt = new \MultiFlexi\RunTemplate((int)$id);
                $output->writeln(json_encode($rt->getData(), JSON_PRETTY_PRINT));
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
                $output->writeln(json_encode(['runtemplate_id' => $rtId], JSON_PRETTY_PRINT));
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
                $rt = new \MultiFlexi\RunTemplate((int)$id);
                $rt->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for runtemplate delete</error>');
                    return Command::FAILURE;
                }
                $rt = new \MultiFlexi\RunTemplate((int)$id);
                $rt->deleteFromSQL();
                $output->writeln(json_encode(['deleted' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

// Přidání UserCommand pro správu uživatelů
class UserCommand extends Command
{
    protected static $defaultName = 'user';
    public function __construct() { parent::__construct(self::$defaultName); }

    protected function configure()
    {
        $this
            ->setDescription('Manage users')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: list|get|create|update|delete')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'User ID')
            ->addOption('login', null, InputOption::VALUE_REQUIRED, 'Login')
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED, 'First name')
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED, 'Last name')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $user = new \MultiFlexi\User();
                $users = $user->listingQuery()->fetchAll();
                $output->writeln(json_encode($users, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user get</error>');
                    return Command::FAILURE;
                }
                $user = new \MultiFlexi\User((int)$id);
                $output->writeln(json_encode($user->getData(), JSON_PRETTY_PRINT));
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
                $output->writeln(json_encode(['user_id' => $userId], JSON_PRETTY_PRINT));
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
                $user = new \MultiFlexi\User((int)$id);
                $user->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for user delete</error>');
                    return Command::FAILURE;
                }
                $user = new \MultiFlexi\User((int)$id);
                $user->deleteFromSQL();
                $output->writeln(json_encode(['deleted' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

// Přidání ApplicationCommand pro správu aplikací
class ApplicationCommand extends Command
{
    protected static $defaultName = 'application';
    public function __construct() { parent::__construct(self::$defaultName); }

    protected function configure()
    {
        $this
            ->setDescription('Manage applications')
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
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'Path to JSON file for import/export/remove')
            // Add more options as needed
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        switch ($action) {
            case 'list':
                $app = new \MultiFlexi\Application();
                $apps = $app->listingQuery()->fetchAll();
                $output->writeln(json_encode($apps, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'get':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for application get</error>');
                    return Command::FAILURE;
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
                $output->writeln(json_encode(['application_id' => $appId], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'update':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for application update</error>');
                    return Command::FAILURE;
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
                $app = new \MultiFlexi\Application((int)$id);
                $app->updateToSQL($data, ['id' => $id]);
                $output->writeln(json_encode(['updated' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'delete':
                $id = $input->getOption('id');
                if (empty($id)) {
                    $output->writeln('<error>Missing --id for application delete</error>');
                    return Command::FAILURE;
                }
                $app = new \MultiFlexi\Application((int)$id);
                $app->deleteFromSQL();
                $output->writeln(json_encode(['deleted' => true], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'import-json':
                $json = $input->getOption('json');
                if (empty($json) || !file_exists($json)) {
                    $output->writeln('<error>Missing or invalid --json file for import-json</error>');
                    return Command::FAILURE;
                }
                $app = new \MultiFlexi\Application();
                $result = $app->importAppJson($json);
                $output->writeln(json_encode(['imported' => $result], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'export-json':
                $id = $input->getOption('id');
                $json = $input->getOption('json');
                if (empty($id) || empty($json)) {
                    $output->writeln('<error>Missing --id or --json for export-json</error>');
                    return Command::FAILURE;
                }
                $app = new \MultiFlexi\Application((int)$id);
                $jsonData = $app->getAppJson();
                file_put_contents($json, $jsonData);
                $output->writeln(json_encode(['exported' => true, 'file' => $json], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            case 'remove-json':
                $json = $input->getOption('json');
                if (empty($json) || !file_exists($json)) {
                    $output->writeln('<error>Missing or invalid --json file for remove-json</error>');
                    return Command::FAILURE;
                }
                $app = new \MultiFlexi\Application();
                $result = $app->jsonAppRemove($json);
                $output->writeln(json_encode(['removed' => $result], JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            default:
                $output->writeln("<error>Unknown action: $action</error>");
                return Command::FAILURE;
        }
    }
}

Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL', 'console'];

if (Shared::cfg('ZABBIX_SERVER') && Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (Shared::cfg('APP_DEBUG') === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexiCLI');

Shared::user(new Anonym());

$application = new Application(Shared::appName(), Shared::appVersion());

$application->add(new JobCommand());
$application->add(new CompanyCommand());
$application->add(new TokenCommand());
$application->add(new RunTemplateCommand());
$application->add(new UserCommand());
$application->add(new ApplicationCommand());
$application->run();
