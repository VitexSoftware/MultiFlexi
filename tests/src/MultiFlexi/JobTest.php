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

namespace Test\MultiFlexi;

use MultiFlexi\Job;

/**
 * Tests for MultiFlexi\Job.
 */
class JobTest extends \PHPUnit\Framework\TestCase
{
    protected Job $object;
    protected \MultiFlexi\ConfigFields $env;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new Job();
        $this->env = new \MultiFlexi\ConfigFields();
    }

    /**
     * @covers \MultiFlexi\Job::newJob
     */
    public function testnewJob(): void
    {
        $result = $this->object->newJob(1, $this->env, new \DateTime());
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * @covers \MultiFlexi\Job::runBegin
     */
    public function testrunBegin(): void
    {
        $this->object->setMyKey(1);
        $result = $this->object->runBegin();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::runEnd
     */
    public function testrunEnd(): void
    {
        $this->object->setMyKey(1);
        $result = $this->object->runEnd();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::isProvisioned
     */
    public function testisProvisioned(): void
    {
        $this->object->setMyKey(1);
        $result = $this->object->isProvisioned();
        $this->assertIsBool($result);
    }

    /**
     * @covers \MultiFlexi\Job::columnDefs
     */
    public function testcolumnDefs(): void
    {
        $result = $this->object->columnDefs();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @covers \MultiFlexi\Job::prepareJob
     */
    public function testprepareJob(): void
    {
        $result = $this->object->prepareJob();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::scheduleJobRun
     */
    public function testscheduleJobRun(): void
    {
        $result = $this->object->scheduleJobRun();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::reportToZabbix
     */
    public function testreportToZabbix(): void
    {
        $result = $this->object->reportToZabbix('Test Metric', 100);
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::performJob
     */
    public function testperformJob(): void
    {
        $result = $this->object->performJob();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::addOutput
     */
    public function testaddOutput(): void
    {
        $this->object->addOutput('Test Output');
        $this->assertStringContainsString('Test Output', $this->object->getOutput());
    }

    /**
     * @covers \MultiFlexi\Job::getOutputCachePlaintext
     */
    public function testgetOutputCachePlaintext(): void
    {
        $result = $this->object->getOutputCachePlaintext();
        $this->assertIsString($result);
    }

    /**
     * @covers \MultiFlexi\Job::getCmdline
     */
    public function testgetCmdline(): void
    {
        $result = $this->object->getCmdline();
        $this->assertIsString($result);
    }

    /**
     * @covers \MultiFlexi\Job::getCmdParams
     */
    public function testgetCmdParams(): void
    {
        $result = $this->object->getCmdParams();
        $this->assertIsArray($result);
    }

    /**
     * @covers \MultiFlexi\Job::getOutput
     */
    public function testgetOutput(): void
    {
        $this->object->addOutput('Test Output');
        $result = $this->object->getOutput();
        $this->assertStringContainsString('Test Output', $result);
    }

    /**
     * @covers \MultiFlexi\Job::cleanUp
     */
    public function testcleanUp(): void
    {
        $result = $this->object->cleanUp();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\Job::launcherScript
     */
    public function testlauncherScript(): void
    {
        $result = $this->object->launcherScript();
        $this->assertIsString($result);
    }

    /**
     * @covers \MultiFlexi\Job::compileEnv
     */
    public function testcompileEnv(): void
    {
        $result = $this->object->compileEnv();
        $this->assertIsArray($result);
    }

    public function testInitialization(): void
    {
        $job = new Job();
        $this->assertInstanceOf(Job::class, $job);
    }

    public function testSomeFunctionality(): void
    {
        $job = new Job();
        // Add assertions for Job functionality
    }
}
