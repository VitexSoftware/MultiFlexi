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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DescribeCommand extends Command
{
    protected static $defaultName = 'describe';

    protected function configure(): void
    {
        $this
            ->setDescription('Describe all available commands and their parameters in JSON')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: json or yaml', 'json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $commands = $application->all();
        $result = [];

        foreach ($commands as $name => $command) {
            if ($name === 'describe') {
                continue;
            }

            // skip self
            $definition = $command->getDefinition();
            $result[$name] = [
                'description' => $command->getDescription(),
                'arguments' => array_map(static function ($arg) {
                    return [
                        'name' => $arg->getName(),
                        'is_required' => $arg->isRequired(),
                        'description' => $arg->getDescription(),
                        'default' => $arg->getDefault(),
                    ];
                }, $definition->getArguments()),
                'options' => array_map(static function ($opt) {
                    return [
                        'name' => $opt->getName(),
                        'shortcut' => $opt->getShortcut(),
                        'is_value_required' => $opt->isValueRequired(),
                        'description' => $opt->getDescription(),
                        'default' => $opt->getDefault(),
                    ];
                }, $definition->getOptions()),
            ];
        }

        $format = strtolower($input->getOption('format'));

        if ($format === 'yaml') {
            if (!\function_exists('yaml_emit')) {
                $output->writeln('<error>YAML extension not available</error>');

                return Command::FAILURE;
            }

            $output->writeln(yaml_emit($result));
        } else {
            $output->writeln(json_encode($result, \JSON_PRETTY_PRINT));
        }

        return Command::SUCCESS;
    }
}
