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

namespace MultiFlexi\Ui;

/**
 * Description of CredentialProtoTypeLister
 *
 * @author vitex
 */
class CredentialProtoTypeLister extends \Ease\TWB4\Table
{
    /**
     * Table CSS class
     */
    public string $cssClass = 'table table-hover';

    /**
     * List Credential Prototypes
     *
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        $defaultProperties = [
            'class' => 'table-striped table-hover'
        ];
        $mergedProperties = array_merge($defaultProperties, $properties);
        
        parent::__construct(null, $mergedProperties);

        $credentialProtoTypes = new \MultiFlexi\CredentialProtoType();
        
        // Get all prototypes from database (includes synced PHP class-based ones)
        $protoTypesRaw = $credentialProtoTypes->getColumnsFromSQL(
            ['id', 'code', 'name', 'version', 'uuid'], 
            null, 
            'name', 
            'id'
        );

        // Create header row
        $headerRow = new \Ease\Html\TrTag();
        $headerRow->addItem(new \Ease\Html\ThTag(_('ID')));
        $headerRow->addItem(new \Ease\Html\ThTag(_('Code')));
        $headerRow->addItem(new \Ease\Html\ThTag(_('Name')));
        $headerRow->addItem(new \Ease\Html\ThTag(_('Version')));
        $headerRow->addItem(new \Ease\Html\ThTag(_('UUID')));
        $headerRow->addItem(new \Ease\Html\ThTag(_('Actions')));
        $this->addItem($headerRow);

        // Add data rows
        if (empty($protoTypesRaw) === false) {
            foreach ($protoTypesRaw as $protoTypeInfo) {
                $row = new \Ease\Html\TrTag();
                
                $actions = new \Ease\Html\DivTag();
                
                // Determine source type and colors
                $isPhpClass = $this->isPhpClassPrototype($protoTypeInfo);
                $sourceColor = $isPhpClass ? 'warning' : 'info';
                $codeColor = $isPhpClass ? 'danger' : 'secondary';
                
                // All prototypes now have full actions since they're all in database
                $actions->addItem(new \Ease\TWB4\LinkButton(
                    'credentialprototype.php?id=' . $protoTypeInfo['id'], 
                    '🔧 ' . _('Edit'), 
                    'info btn-sm'
                ));
                $actions->addItem('&nbsp;');
                
                // Only allow deletion of user-created prototypes, not PHP class ones
                if (!$isPhpClass) {
                    $actions->addItem(new \Ease\TWB4\LinkButton(
                        'credentialprototype.php?delete=' . $protoTypeInfo['id'], 
                        '🗑️ ' . _('Delete'), 
                        'danger btn-sm',
                        ['onclick' => 'return confirm(\'' . _('Really delete credential prototype?') . '\');']
                    ));
                } else {
                    $actions->addItem(new \Ease\TWB4\Badge('secondary', _('System')));
                }

                $row->addItem(new \Ease\Html\TdTag($protoTypeInfo['id']));
                $row->addItem(new \Ease\Html\TdTag(new \Ease\TWB4\Badge($codeColor, $protoTypeInfo['code'])));
                $row->addItem(new \Ease\Html\TdTag(new \Ease\TWB4\Badge($sourceColor, $protoTypeInfo['name'])));
                $row->addItem(new \Ease\Html\TdTag($protoTypeInfo['version'] ?: '1.0'));
                $row->addItem(new \Ease\Html\TdTag(new \Ease\Html\SmallTag($protoTypeInfo['uuid'])));
                $row->addItem(new \Ease\Html\TdTag($actions));
                
                $this->addItem($row);
            }
        } else {
            $row = new \Ease\Html\TrTag();
            $row->addItem(new \Ease\Html\TdTag(_('No credential prototypes found'), ['colspan' => '6']));
            $this->addItem($row);
        }
    }

    /**
     * Determine if a credential prototype comes from a PHP class
     * (based on UUID matching with existing PHP classes)
     *
     * @param array $protoTypeInfo
     * @return bool
     */
    private function isPhpClassPrototype(array $protoTypeInfo): bool
    {
        // Check if UUID looks like a valid UUID format
        $uuid = $protoTypeInfo['uuid'] ?? '';
        if (empty($uuid) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return false;
        }
        
        // Try to match against known PHP class UUIDs by checking if a corresponding class exists
        $code = $protoTypeInfo['code'] ?? '';
        $fullClassName = "\\MultiFlexi\\CredentialType\\{$code}";
        
        if (class_exists($fullClassName) && method_exists($fullClassName, 'uuid')) {
            try {
                return $fullClassName::uuid() === $uuid;
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return false;
    }

    /**
     * Process and render table
     */
    public function finalize(): void
    {
        if (count($this->pageParts)) {
            $this->setTagClass($this->cssClass);
        }
    }


}