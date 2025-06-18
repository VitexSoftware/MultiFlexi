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

/**
 * Description of RunTemplate.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RunTemplate extends MultiFlexiCommand
{
    public function listing(): array
    {
        $engine = new self();

        return $engine->listingQuery()->select([
            'id',
            'enabled',
            'name',
            'app_id',
            'company_id',
            'DatCreate',
            'DatUpdate',
            'setup',
            'cmdparams',
            'deploy',
            'homepage',
            'requirements',
        ], true)->fetchAll();
    }
    protected function configure(): void
    {
        $this
            ->setName('runtemplate')
            ->setDescription('Runtemplate operations')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->addArgument('operation', InputArgument::REQUIRED, 'what to do with RunTemplate')
            ->addArgument('id', InputArgument::OPTIONAL, 'which RunTemplate ?')
            ->setHelp(<<<'EOT'
The <info>runtemplate</info> command mangafe runtemplate

<info>multilflexi-cli runtemplate trigger 220</info>
<info>multilflexi-cli runtemplate list -f json</info>

EOT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runTemplate = new \MultiFlexi\RunTemplate(is_numeric($input->getArgument('id')) ? (int) $input->getArgument('id') : $input->getArgument('id'));

        switch ($input->getArgument('operation')) {
            case 'trigger':
                $jobber = new \MultiFlexi\Job();
                $when = new \DateTime();
                $executor = 'Native';
                $customEnv = [];
                $prepared = $jobber->prepareJob($runTemplate->getMyKey(), $customEnv, $when, $executor, 'adhoc');
                $jobber->scheduleJobRun($when);

                break;

            default:
                break;
        }

        return MultiFlexiCommand::SUCCESS;
    }
}
