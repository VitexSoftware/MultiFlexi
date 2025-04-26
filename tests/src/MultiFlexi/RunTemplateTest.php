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

use MultiFlexi\RunTemplate;

/**
 * Tests for MultiFlexi\RunTemplate.
 */
class RunTemplateTest extends \PHPUnit\Framework\TestCase
{
    protected RunTemplate $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $rt = new RunTemplate();
        $rtid = $rt->listingQuery()->limit(1)->fetch('id');
        $this->object = new RunTemplate($rtid);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::runTemplateID
     */
    public function testrunTemplateID(): void
    {
        $this->assertIsInt($this->object->runTemplateID(1, 1));
    }

    /**
     * @covers \MultiFlexi\RunTemplate::setState
     */
    public function testsetState(): void
    {
        $this->object->setState(true);
        $this->assertEquals('active', $this->object->getState());
    }

    /**
     * @covers \MultiFlexi\RunTemplate::performInit
     */
    public function testperformInit(): void
    {
        $result = $this->object->performInit();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::deleteFromSQL
     */
    public function testdeleteFromSQL(): void
    {
        $result = $this->object->deleteFromSQL();
        $this->assertTrue($result);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::getCompanyEnvironment
     */
    public function testgetCompanyEnvironment(): void
    {
        $result = $this->object->getCompanyEnvironment();
        $this->assertIsArray($result);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::getAppEnvironment
     */
    public function testgetAppEnvironment(): void
    {
        $result = $this->object->getAppEnvironment();
        $this->assertIsArray($result);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::getAppInfo
     */
    public function testgetAppInfo(): void
    {
        $result = $this->object->getAppInfo();
        $this->assertIsArray($result);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::setEnvironment
     */
    public function testsetEnvironment(): void
    {
        $env = ['key' => 'value'];
        $this->object->setEnvironment($env);
        $this->assertEquals($env, $this->object->getEnvironment());
    }

    /**
     * @covers \MultiFlexi\RunTemplate::getAppsForCompany
     */
    public function testgetAppsForCompany(): void
    {
        $result = $this->object->getAppsForCompany(1);
        $this->assertIsArray($result);
    }

    /**
     * @covers \MultiFlexi\RunTemplate::setProvision
     */
    public function testsetProvision(): void
    {
        $this->object->setProvision(true);
        $this->assertTrue($this->object->isProvisioned());
    }
}
