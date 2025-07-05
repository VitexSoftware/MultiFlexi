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

use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Company.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Company extends MultiFlexiCommand
{
    #[\Override]
    public function listing(): array
    {
        $engine = new self();

        return $engine->listingQuery()->select([
            'id',
            'enabled',
            'settings',
            'logo  not like "" as logo',
            'slug',
            'name',
            'DatCreate',
            'DatUpdate',
            'customer',
            'email',
        ])->fetchAll();
    }
    protected function configure(): void
    {
        $this
            ->setName('company')
            ->setDescription('Company operations')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command manage Company');
    }
}
