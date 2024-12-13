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

namespace MultiFlexi;

use Ease\SQL\Engine;

/**
 * Class Artifact.
 *
 * Handles operations related to the artifacts table.
 */
class Artifact extends Engine
{
    /**
     * Artifact constructor.
     *
     * @param null|mixed $identifier
     * @param mixed      $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'artifacts';
        parent::__construct($identifier, $options);
    }

    /**
     * Create a new artifact record.
     *
     * @param int   $jobId       Number of producing job
     * @param mixed $filename
     * @param mixed $contentType
     * @param mixed $note
     *
     * @return int Atrifacts Record ID
     */
    public function createArtifact(int $jobId, string $artifact, $filename = '', $contentType = '', $note = ''): int
    {
        return $this->insertToSQL([
            'job_id' => $jobId,
            'filename' => $filename,
            'content_type' => $contentType,
            'artifact' => $artifact,
            'created_at' => date('Y-m-d H:i:s'),
            'note' => $note,
        ]);
    }

    /**
     * Get an artifact by ID.
     */
    public function getArtifactById(int $id): ?array
    {
        return $this->getColumnsFromSQL('*', ['id' => $id]);
    }

    /**
     * Update an artifact record.
     */
    public function updateArtifact(int $id, string $artifact): bool
    {
        return $this->updateToSQL(['artifact' => $artifact], ['id' => $id]);
    }

    /**
     * Delete an artifact record.
     */
    public function deleteArtifact(int $id): bool
    {
        return $this->deleteFromSQL(['id' => $id]);
    }
}
