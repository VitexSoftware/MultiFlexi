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

/**
 * Class Topics.
 *
 * Used for keeping a set of Topic objects.
 */
class Topics implements \Iterator
{
    /**
     * @var Topic[]
     */
    private array $topics = [];
    private int $position = 0;

    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * Add a Topic to the set.
     */
    public function add(Topic $topic): void
    {
        $this->topics[] = $topic;
    }

    /**
     * Get all Topics.
     *
     * @return Topic[]
     */
    public function getAll(): array
    {
        return $this->topics;
    }

    /**
     * Find a Topic by keyword.
     */
    public function findByKeyword(string $keyword): ?Topic
    {
        foreach ($this->topics as $topic) {
            if ($topic->getKeyword() === $keyword) {
                return $topic;
            }
        }

        return null;
    }

    /**
     * Update a Topic by keyword.
     */
    public function updateByKeyword(string $keyword, Topic $newTopic): bool
    {
        foreach ($this->topics as $index => $topic) {
            if ($topic->getKeyword() === $keyword) {
                $this->topics[$index] = $newTopic;

                return true;
            }
        }

        return false;
    }

    /**
     * Delete a Topic by keyword.
     */
    public function deleteByKeyword(string $keyword): bool
    {
        foreach ($this->topics as $index => $topic) {
            if ($topic->getKeyword() === $keyword) {
                array_splice($this->topics, $index, 1);

                return true;
            }
        }

        return false;
    }

    /**
     * Join another Topics set into this one.
     */
    public function join(self $other): void
    {
        foreach ($other->getAll() as $topic) {
            $this->add($topic);
        }
    }

    // \Iterator interface implementation

    public function current(): mixed
    {
        return $this->topics[$this->position] ?? null;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->topics[$this->position]);
    }
}
