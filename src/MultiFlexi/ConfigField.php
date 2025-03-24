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
 * Description of ConfigField.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ConfigField
{
    private string $code;
    private string $name;
    private string $description;
    private string $hint;
    private ?string $value;
    private string $type;
    private string $defaultValue;
    private bool $required = false;

    /**
     * ConfigField constructor.
     *
     * @param string $code        Field code
     * @param string $type        Field type
     * @param string $name        Field name
     * @param string $description Field description
     * @param string $hint        Field hint
     * @param string $value       Field value
     */
    public function __construct(string $code, string $type, string $name, string $description, string $hint = '', ?string $value = null)
    {
        $this->setCode($code);
        $this->setType($type);
        $this->setName($name);
        $this->setDescription($description);
        $this->setHint($hint);
        $this->setValue($value);
    }

    /**
     * Set name of the field.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Summary of getName.
     */
    public function getName(): string
    {
        return $this->name;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function setHint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }
    public function getHint(): string
    {
        return $this->hint;
    }
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
    public function getValue(): ?string
    {
        return $this->value;
    }
    public function setCode(string $code): void
    {
        $this->code = $code;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function setType(string $type): self
    {
        $allowedTypes = ['string', 'file-path', 'email', 'url', 'integer', 'float', 'bool'];

        if (!\in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException("Invalid type: {$type}. Allowed types are: ".implode(', ', $allowedTypes));
        }

        $this->type = $type;

        return $this;
    }
    public function getType(): string
    {
        return $this->type;
    }

    public function setDefaultValue(string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get the configuration field as an array.
     *
     * @return array<string, string>
     */
    public function getArray(): array
    {
        return [
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'hint' => $this->getHint(),
            'value' => $this->getValue(),
            'type' => $this->getType(),
            'defaultValue' => $this->getDefaultValue(),
            'required' => $this->isRequired(),
        ];
    }
}
