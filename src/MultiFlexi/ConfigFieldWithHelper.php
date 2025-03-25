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
 * Description of ConfigFieldWithHelper.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ConfigFieldWithHelper extends ConfigField
{
    use \Ease\recordkey;
    public array $data = [];
    protected string $helper = '';

    public function getHelper(): string
    {
        return $this->helper;
    }

    public function setHelper(string $helper): self
    {
        $this->helper = $helper;

        return $this;
    }

    #[\Override]
    public function getData(): ?array
    {
        $data = $this->getArray();
        $data['helper'] = $this->getHelper();

        return $data;
    }

    #[\Override]
    public function setDataValue(string $columnName, $value): bool
    {
        $this->data[$columnName] = $value;

        return true;
    }

    public function getDataValue($key)
    {
        return \array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }
}
