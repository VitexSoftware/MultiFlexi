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
 * Topic description.
 *
 * @author vitex
 */
class Topic
{
    private ?string $keyword = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?bool $isProvided = null;
    private ?bool $isRequired = null;
    private ?string $color = null; // Color code, e.g. "#FF0000"

    /**
     * @param string $mode "provider" or "requester"
     */
    public function __construct(string $name, string $mode)
    {
        $this->setName($name);

        if ($mode === 'provider') {
            $this->setIsProvided(true);
            $this->setIsRequired(false);
        } elseif ($mode === 'requester') {
            $this->setIsProvided(false);
            $this->setIsRequired(true);
        } else {
            throw new \InvalidArgumentException('Mode must be "provider" or "requester"');
        }
    }

    /**
     * Get the keyword.
     */
    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    /**
     * Set the keyword.
     */
    public function setKeyword(string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get the name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the description.
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get isProvided.
     */
    public function getIsProvided(): ?bool
    {
        return $this->isProvided;
    }

    /**
     * Set isProvided.
     */
    public function setIsProvided(bool $isProvided): self
    {
        $this->isProvided = $isProvided;

        return $this;
    }

    /**
     * Get isRequired.
     */
    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }

    /**
     * Set isRequired.
     */
    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get the color code.
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Set the color code.
     */
    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
