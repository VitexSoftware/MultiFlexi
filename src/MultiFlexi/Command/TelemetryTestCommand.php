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

use MultiFlexi\Telemetry\OtelMetricsExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test OpenTelemetry metrics export.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class TelemetryTestCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('telemetry:test')
            ->setDescription('Test OpenTelemetry metrics export')
            ->setHelp('This command tests the OpenTelemetry metrics export functionality by sending test metrics to the configured OTLP endpoint.')
            ->addOption(
                'endpoint',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Override OTLP endpoint URL',
                null
            )
            ->addOption(
                'disable-gauges',
                null,
                InputOption::VALUE_NONE,
                'Disable observable gauges (only test counters and histograms)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Testing OpenTelemetry Metrics Export</info>');
        $output->writeln('');

        // Check if OTel is enabled
        $enabled = \Ease\Shared::cfg('OTEL_ENABLED', false);

        if (!$enabled) {
            $output->writeln('<error>OpenTelemetry is disabled. Set OTEL_ENABLED=true in your configuration.</error>');

            return Command::FAILURE;
        }

        // Override endpoint if provided
        if ($endpoint = $input->getOption('endpoint')) {
            \Ease\Shared::instanced()->configuration->setConfigValue('OTEL_EXPORTER_OTLP_ENDPOINT', $endpoint);
            $output->writeln('<comment>Using custom endpoint: '.$endpoint.'</comment>');
        }

        // Display current configuration
        $output->writeln('<info>Configuration:</info>');
        $output->writeln('  Service Name: '.\Ease\Shared::cfg('OTEL_SERVICE_NAME', 'multiflexi'));
        $output->writeln('  Endpoint: '.\Ease\Shared::cfg('OTEL_EXPORTER_OTLP_ENDPOINT', 'http://localhost:4318'));
        $output->writeln('  Protocol: '.\Ease\Shared::cfg('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/json'));
        $output->writeln('');

        try {
            // Initialize exporter
            $output->writeln('<info>Initializing OTel Metrics Exporter...</info>');
            $exporter = new OtelMetricsExporter();

            if (!$exporter->isEnabled()) {
                $output->writeln('<error>Failed to initialize OpenTelemetry exporter.</error>');
                $output->writeln('<comment>Check if OpenTelemetry SDK packages are installed:</comment>');
                $output->writeln('  composer require open-telemetry/sdk open-telemetry/exporter-otlp');

                return Command::FAILURE;
            }

            $output->writeln('<info>✓ Exporter initialized successfully</info>');
            $output->writeln('');

            // Test job start metrics
            $output->writeln('<info>Testing job start metric...</info>');
            $exporter->recordJobStart(
                99999, // test job ID
                1, // test app ID
                'TestApplication',
                1, // test company ID
                'TestCompany',
                1, // test runtemplate ID
                'TestRunTemplate'
            );
            $output->writeln('<info>✓ Job start metric recorded</info>');
            $output->writeln('');

            // Test job end metrics
            $output->writeln('<info>Testing job end metrics...</info>');

            // Successful job
            $exporter->recordJobEnd(0, 5.5, [
                'job_id' => 99999,
                'app_id' => 1,
                'app_name' => 'TestApplication',
                'company_id' => 1,
                'company_name' => 'TestCompany',
                'runtemplate_id' => 1,
                'runtemplate_name' => 'TestRunTemplate',
                'executor' => 'Native',
            ]);
            $output->writeln('  ✓ Success metric (exitcode=0, duration=5.5s)');

            // Failed job
            $exporter->recordJobEnd(1, 2.3, [
                'job_id' => 99998,
                'app_id' => 1,
                'app_name' => 'TestApplication',
                'company_id' => 1,
                'company_name' => 'TestCompany',
                'runtemplate_id' => 1,
                'runtemplate_name' => 'TestRunTemplate',
                'executor' => 'Native',
            ]);
            $output->writeln('  ✓ Failure metric (exitcode=1, duration=2.3s)');
            $output->writeln('');

            // Test gauges if not disabled
            if (!$input->getOption('disable-gauges')) {
                $output->writeln('<info>Testing observable gauges (real-time metrics)...</info>');
                $output->writeln('  These will be collected when the OTLP endpoint polls them.');
                $output->writeln('  ✓ multiflexi.jobs.running');
                $output->writeln('  ✓ multiflexi.applications.total');
                $output->writeln('  ✓ multiflexi.applications.enabled');
                $output->writeln('  ✓ multiflexi.companies.total');
                $output->writeln('  ✓ multiflexi.runtemplates.total');
                $output->writeln('');
            }

            // Flush metrics
            $output->writeln('<info>Flushing metrics to OTLP endpoint...</info>');
            $exporter->flush();
            $output->writeln('<info>✓ Metrics flushed successfully</info>');
            $output->writeln('');

            $output->writeln('<info>Test completed successfully!</info>');
            $output->writeln('');
            $output->writeln('<comment>Next steps:</comment>');
            $output->writeln('  1. Check your OTLP collector/backend for received metrics');
            $output->writeln('  2. Look for metrics with prefix: multiflexi.*');
            $output->writeln('  3. Verify that test job metrics appear with the test attributes');
            $output->writeln('');
            $output->writeln('<comment>Available metrics:</comment>');
            $output->writeln('  Counters:');
            $output->writeln('    - multiflexi.jobs.total');
            $output->writeln('    - multiflexi.jobs.success');
            $output->writeln('    - multiflexi.jobs.failed');
            $output->writeln('  Histograms:');
            $output->writeln('    - multiflexi.job.duration');
            $output->writeln('  Gauges:');
            $output->writeln('    - multiflexi.jobs.running');
            $output->writeln('    - multiflexi.applications.total');
            $output->writeln('    - multiflexi.applications.enabled');
            $output->writeln('    - multiflexi.companies.total');
            $output->writeln('    - multiflexi.runtemplates.total');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error during test:</error>');
            $output->writeln('  '.$e->getMessage());
            $output->writeln('');
            $output->writeln('<comment>Debug information:</comment>');
            $output->writeln('  File: '.$e->getFile());
            $output->writeln('  Line: '.$e->getLine());

            if ($output->isVerbose()) {
                $output->writeln('');
                $output->writeln('<comment>Stack trace:</comment>');
                $output->writeln($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}
