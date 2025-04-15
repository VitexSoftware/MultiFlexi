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
 * Description of ConfigFields.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ConfigFields implements \Iterator
{
    /**
     * @var array<string, ConfigField>
     */
    private array $fields = [];
    private string $name = '';

    public function __construct(string $name = '')
    {
        $this->setName($name);
    }

    /**
     * @return array<string, ConfigField> all Configuration fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Add Field into stack.
     */
    public function addField(ConfigField $field): void
    {
        $code = $field->getCode();
        $this->fields[$code] = $field;
        ksort($this->fields);
    }

    /**
     * Summary of getField.
     *
     * @return null|ConfigField[]
     */
    public function getField(string $name): ?ConfigField
    {
        return \array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
    }

    public function &getFieldByCode(string $code): ?ConfigField
    {
        $field = \array_key_exists($code, $this->fields) ? $this->fields[$code] : null;

        if ($field && empty($field->getSource()) && $this->getName()) {
            $field->setSource($this->getName());
        }

        return $field;
    }

    /**
     * @return array<string, string>
     */
    public function getEnvArray(): array
    {
        $fields = [];

        foreach ($this->fields as $field) {
            $fields[$field->getCode()] = $field->getValue();
        }

        return $fields;
    }

    #[\Override]
    public function current(): mixed
    {
        return current($this->fields);
    }

    #[\Override]
    public function key(): mixed
    {
        return key($this->fields);
    }

    #[\Override]
    public function next(): void
    {
        next($this->fields);
    }

    #[\Override]
    public function rewind(): void
    {
        reset($this->fields);
    }

    #[\Override]
    public function valid(): bool
    {
        return key($this->fields) !== null;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Add multiple ConfigFields.
     */
    public function addFields(self $configs): self
    {
        foreach ($configs as $config) {
            $this->addField($config);
        }

        return $this;
    }

    /**
     * Field names list.
     *
     * @return array<string>
     */
    public function getFieldNames(): array
    {
        return array_keys($this->fields);
    }
}
