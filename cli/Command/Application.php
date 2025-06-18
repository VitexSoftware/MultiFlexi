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
 * Description of Application.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Application extends MultiFlexiCommand
{
    #[\Override]
    public function listing(): array
    {
        $engine = new \MultiFlexi\Application();

        return $engine->listingQuery()->select([
            'id',
            'enabled',
            'image not like "" as image',
            'name',
            'description',
            'executable',
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
            ->setName('app')
            ->setDescription('Apps operations')
            ->addArgument('operation', InputArgument::REQUIRED, _('What to do with app'))
            ->addArgument('id', InputArgument::OPTIONAL, 'which RunTemplate ?')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command manage Apps inf');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        switch ($input->getArgument('operation')) {
            case 'list':
                $this->outputTable($this->listing());

                break;
        }

        return MultiFlexiCommand::SUCCESS;
    }
}
