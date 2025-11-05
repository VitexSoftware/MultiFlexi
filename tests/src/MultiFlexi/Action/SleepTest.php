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

namespace Tests\MultiFlexi\Action;

use MultiFlexi\Action\Sleep;
use MultiFlexi\Job;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sleep Action.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.com>
 *
 * @no-named-arguments
 */
class SleepTest extends TestCase
{
    /**
     * Test instance.
     */
    private Sleep $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new Sleep(['seconds' => 2]);
    }

    /**
     * Test name method.
     */
    public function testName(): void
    {
        $this->assertIsString(Sleep::name());
        $this->assertNotEmpty(Sleep::name());
    }

    /**
     * Test description method.
     */
    public function testDescription(): void
    {
        $this->assertIsString(Sleep::description());
        $this->assertNotEmpty(Sleep::description());
    }

    /**
     * Test logo method.
     */
    public function testLogo(): void
    {
        $this->assertIsString(Sleep::logo());
        $this->assertStringStartsWith('data:image/svg+xml;base64,', Sleep::logo());
    }

    /**
     * Test perform method
     * We'll mock the sleep function to avoid actual delays.
     */
    public function testPerform(): void
    {
        // Create a mock job
        $job = $this->createMock(Job::class);

        // Create a partial mock for Sleep to avoid actual sleep
        $sleepAction = $this->getMockBuilder(Sleep::class)
            ->setConstructorArgs([['seconds' => 5]])
            ->onlyMethods(['addStatusMessage'])
            ->getMock();

        // Expect the status message to be added
        $sleepAction->expects($this->once())
            ->method('addStatusMessage')
            ->with($this->stringContains('Sleepeng for 5 seconds'));

        // Use runkit to temporarily override the sleep function
        // This is a workaround since we can't mock PHP built-in functions directly
        // If runkit is not available, this test will be skipped
        if (\function_exists('runkit7_function_redefine') || \function_exists('runkit_function_redefine')) {
            $sleepFunction = \function_exists('runkit7_function_redefine') ? 'runkit7_function_redefine' : 'runkit_function_redefine';

            // Save the original sleep function
            $original = static function ($seconds) {
                return \sleep($seconds);
            };

            // Redefine sleep to do nothing
            $sleepFunction('sleep', static function ($seconds) {
                return 0; // Return 0 as if no processes were awakened
            }, true);

            // Execute the perform method
            $sleepAction->perform($job);

            // Restore the original sleep function
            $sleepFunction('sleep', $original, true);
        } else {
            // If runkit is not available, we'll do a time-based test
            // This is less precise but still useful
            $startTime = microtime(true);
            $sleepAction->perform($job);
            $endTime = microtime(true);

            // The execution should take approximately 5 seconds
            // We'll allow a small margin of error
            $this->assertGreaterThanOrEqual(4.5, $endTime - $startTime, 'Sleep duration is too short');
            $this->assertLessThanOrEqual(5.5, $endTime - $startTime, 'Sleep duration is too long');
        }
    }

    /**
     * Test error handling in perform method.
     */
    public function testPerformWithInvalidSeconds(): void
    {
        // Create a mock job
        $job = $this->createMock(Job::class);

        // Create a Sleep action with invalid seconds (non-numeric)
        $sleepAction = new Sleep(['seconds' => 'invalid']);

        // The perform method should handle this gracefully (convert to 0)
        $sleepAction->perform($job);

        // No assertion needed - if no exception is thrown, the test passes
    }

    /**
     * Test the inputs method returns expected form elements.
     */
    public function testInputs(): void
    {
        $prefix = 'test';
        // Create instance with a dummy RunTemplate to call inputs
        $sleepInstance = new Sleep(new \MultiFlexi\RunTemplate());
        $inputs = $sleepInstance->inputs($prefix);

        $this->assertInstanceOf(\Ease\Embedable::class, $inputs);

        // The input should be a FormGroup
        $this->assertInstanceOf(\Ease\TWB4\FormGroup::class, $inputs);

        // For Sleep action, we expect the inputs to contain a form field for number of seconds
    }

    /**
     * Test the usableForApp method.
     */
    public function testUsableForApp(): void
    {
        $app = $this->createMock(\MultiFlexi\Application::class);

        $this->assertTrue(Sleep::usableForApp($app), 'Sleep action should be usable for any application');
    }

    /**
     * Test the initialData method.
     */
    public function testInitialData(): void
    {
        $data = $this->object->initialData('test');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('seconds', $data);
        $this->assertEquals('60', $data['seconds']);
    }
}
