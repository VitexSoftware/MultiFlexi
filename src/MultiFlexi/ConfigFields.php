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

    /**
     * @return array<string, ConfigField> all Configuration fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function addField(ConfigField $filed): void
    {
        $this->fields[$filed->getCode()] = $filed;
    }

    /**
     * Summary of getField.
     *
     * @return null|ConfigField[]
     */
    public function getField(string $class): ?ConfigField
    {
        return \array_key_exists($class, $this->fields) ? $this->fields : null;
    }

    public function &getFieldByCode(string $code): ?ConfigField
    {
        $field = \array_key_exists($code, $this->fields) ? $this->fields[$code] : null;

        return $field;
    }

    /**
     * @return array<string, ConfigField>
     */
    public function getArray(): array
    {
        $fields = [];

        foreach ($this->fields as $field) {
            $fields[$field->getCode()] = $field;
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
}
