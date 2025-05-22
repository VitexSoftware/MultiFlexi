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

class CrTypeField extends Engine
{
    /**
     * Table name.
     */
    public string $myTable = 'crtypefield';

    /**
     * CrTypeField constructor.
     *
     * @param mixed $init
     */
    public function __construct($init = null)
    {
        parent::__construct($init);
    }

    public function getCredTypeFields(CredentialType $credentialType): ConfigFields
    {
        $fields = new ConfigFields();

        foreach ($this->listingQuery()->where(['credential_type_id' => $credentialType->getMyKey()]) as $fieldData) {
            $field = new ConfigFieldWithHelper($fieldData['keyname'], $fieldData['type'], $fieldData['keyname'], $fieldData['description']);
            $field->setHint($fieldData['hint'])->setDefaultValue($fieldData['defval'])->setRequired($fieldData['required'])->setHelper($fieldData['helper']);
            $fields->addField($field);
        }

        return $fields;
    }

    /**
     * Define the structure of the table.
     */
    public function getColumns(): array
    {
        return [
            'credential_type_id' => ['type' => 'integer', 'unsigned' => true, 'null' => false],
            'keyname' => ['type' => 'string', 'limit' => 64, 'default' => null, 'null' => true],
            'type' => ['type' => 'string', 'limit' => 32, 'default' => null, 'null' => true],
            'description' => ['type' => 'string', 'limit' => 1024, 'default' => null, 'null' => true],
            'hint' => ['type' => 'string', 'limit' => 256, 'default' => null, 'null' => true],
            'default' => ['type' => 'string', 'limit' => 256, 'default' => null, 'null' => true],
            'required' => ['type' => 'boolean', 'default' => 0, 'null' => false],
        ];
    }
}
