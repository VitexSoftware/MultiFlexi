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

use MultiFlexi\Artifact;

/**
 * Tests for MultiFlexi\Artifact.
 */
class ArtifactTest extends \PHPUnit\Framework\TestCase
{
    protected Artifact $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new Artifact();
    }

    /**
     * @covers \MultiFlexi\Artifact::createArtifact
     */
    public function testcreateArtifact(): void
    {
        $data = ['name' => 'Test Artifact', 'type' => 'file', 'content' => 'Test Content'];
        $artifactId = $this->object->createArtifact($data);

        $this->assertIsInt($artifactId);
        $this->assertGreaterThan(0, $artifactId);
    }

    /**
     * @covers \MultiFlexi\Artifact::getArtifactById
     */
    public function testgetArtifactById(): void
    {
        $data = ['name' => 'Test Artifact', 'type' => 'file', 'content' => 'Test Content'];
        $artifactId = $this->object->createArtifact($data);

        $artifact = $this->object->getArtifactById($artifactId);

        $this->assertIsArray($artifact);
        $this->assertEquals('Test Artifact', $artifact['name']);
        $this->assertEquals('file', $artifact['type']);
        $this->assertEquals('Test Content', $artifact['content']);
    }

    /**
     * @covers \MultiFlexi\Artifact::updateArtifact
     */
    public function testupdateArtifact(): void
    {
        $data = ['name' => 'Test Artifact', 'type' => 'file', 'content' => 'Test Content'];
        $artifactId = $this->object->createArtifact($data);

        $updatedData = ['name' => 'Updated Artifact', 'type' => 'file', 'content' => 'Updated Content'];
        $result = $this->object->updateArtifact($artifactId, $updatedData);

        $this->assertTrue($result);

        $artifact = $this->object->getArtifactById($artifactId);
        $this->assertEquals('Updated Artifact', $artifact['name']);
        $this->assertEquals('Updated Content', $artifact['content']);
    }

    /**
     * @covers \MultiFlexi\Artifact::deleteArtifact
     */
    public function testdeleteArtifact(): void
    {
        $data = ['name' => 'Test Artifact', 'type' => 'file', 'content' => 'Test Content'];
        $artifactId = $this->object->createArtifact($data);

        $result = $this->object->deleteArtifact($artifactId);

        $this->assertTrue($result);

        $artifact = $this->object->getArtifactById($artifactId);
        $this->assertNull($artifact);
    }
}
