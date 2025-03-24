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
 * Description of CredentialProtoType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
abstract class CredentialProtoType extends \Ease\Sand
{
    use \Ease\recordkey;
    protected \MultiFlexi\ConfigFields $configFieldsProvided;
    protected \MultiFlexi\ConfigFields $configFieldsInternal;

    public function __construct()
    {
        $this->configFieldsProvided = new \MultiFlexi\ConfigFields();
        $this->configFieldsInternal = new \MultiFlexi\ConfigFields();
    }

    public function save(): bool
    {
        $credentialTypeId = $this->getDataValue('credential_type_id');
        $this->unsetDataValue('credential_type_id');
        $fielder = new \MultiFlexi\CrTypeOption();
        foreach ($this->getData() as $keyName => $value) {
            $fielder->takeData(['credential_type_id' => $credentialTypeId, 'name' => $keyName, 'value' => $value]);
            $fielder->saveToSQL();
        }

        return true;
    }

    public function providedFieldsSelect(): \Ease\Html\SelectTag
    {
        return new \Ease\Html\SelectTag($name, $items, $defaultValue, $properties);
    }
    
    public function takeData($data): int {
        $imported = 0;
        
        foreach ($data as $key => $fieldData){
            $field = $this->configFieldsInternal->getFieldByCode($key);
            if($field){
                $field->setValue($fieldData['value']);
                $imported++;
            }
        }
        
        return $imported;
    }
    
    public function fieldsProvided(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsProvided;
    }

    public function fieldsInternal(): \MultiFlexi\ConfigFields {
        return $this->configFieldsInternal;
    }
    
}
