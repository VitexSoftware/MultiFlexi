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

namespace MultiFlexi\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Job.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class Job extends Command
{
    #[\Override]
    public function listing(): array
    {
        $engine = new self();

        return $engine->listingQuery()->select([
            'id',
        ])->fetchAll();
    }
    protected function configure(): void
    {
        $this
            ->setName('job')
            ->setDescription('Job operations')
            ->addOption('--format', '-f', InputOption::VALUE_OPTIONAL, 'The output format: text or json. Defaults to text.', 'text')
            ->setHelp('This command manage Jobs');
    }
}
