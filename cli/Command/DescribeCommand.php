<?php

declare(strict_types=1);

namespace MultiFlexi\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
            if ($name === 'describe') continue; // skip self
            $definition = $command->getDefinition();
            $result[$name] = [
                'description' => $command->getDescription(),
                'arguments' => array_map(function($arg) {
                    return [
                        'name' => $arg->getName(),
                        'is_required' => $arg->isRequired(),
                        'description' => $arg->getDescription(),
                        'default' => $arg->getDefault()
                    ];
                }, $definition->getArguments()),
                'options' => array_map(function($opt) {
                    return [
                        'name' => $opt->getName(),
                        'shortcut' => $opt->getShortcut(),
                        'is_value_required' => $opt->isValueRequired(),
                        'description' => $opt->getDescription(),
                        'default' => $opt->getDefault()
                    ];
                }, $definition->getOptions()),
            ];
        }
        $format = strtolower($input->getOption('format'));
        if ($format === 'yaml') {
            if (!function_exists('yaml_emit')) {
                $output->writeln('<error>YAML extension not available</error>');
                return Command::FAILURE;
            }
            $output->writeln(yaml_emit($result));
        } else {
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
        }
        return Command::SUCCESS;
    }
}
