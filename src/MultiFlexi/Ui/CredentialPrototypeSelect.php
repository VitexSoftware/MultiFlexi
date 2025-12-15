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
 * Description of CredentialClassSelect.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class CredentialPrototypeSelect extends \Ease\Html\SelectTag
{
    public array $credentialTypeClasses = [];

    /**
     *  Executor Select.
     *
     * @param string $name
     * @param array  $items
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($name, $items = [], $defaultValue = 'BuiltIn', $properties = [])
    {
        parent::__construct($name, $items, $defaultValue, $properties);
    }

    /**
     * Load credential type items from database (unified PHP and JSON prototypes).
     */
    public function loadItems(): array
    {
        $items = ['' => _('No CredentialType helper used')];

        // Get all credential prototypes from database (includes synced PHP class-based ones)
        $credProto = new \MultiFlexi\CredentialProtoType();
        $allCredTypes = $credProto->listingQuery()
            ->select(['id', 'code', 'name', 'description', 'uuid'])
            ->orderBy('name')
            ->fetchAll();

        foreach ($allCredTypes as $credType) {
            $isPhpClass = self::isPhpClassPrototype($credType);
            $description = !empty($credType['description']) ? $credType['description'] : $credType['name'];
            $sourceLabel = $isPhpClass ? ' ('._('built-in').')' : ' ('._('custom').')';

            // For PHP classes, use the class name as key; for JSON, use JSON: prefix
            $key = $isPhpClass ? $credType['code'] : _('custom').':'.$credType['code'];
            $items[$key] = $description.$sourceLabel;

            // Store the class reference for PHP types
            if ($isPhpClass) {
                $credTypeHelperClass = '\\MultiFlexi\\CredentialType\\'.$credType['code'];
                $this->credentialTypeClasses[$key] = $credTypeHelperClass;
            } else {
                $this->credentialTypeClasses[$key] = null; // No PHP class for JSON types
            }
        }

        return $items;
    }

    /**
     * Determine if a credential prototype comes from a PHP class
     * (based on UUID matching with existing PHP classes).
     */
    private static function isPhpClassPrototype(array $credType): bool
    {
        // Check if UUID looks like a valid UUID format
        $uuid = $credType['uuid'] ?? '';

        if (empty($uuid) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return false;
        }

        // Try to match against known PHP class UUIDs by checking if a corresponding class exists
        $code = $credType['code'] ?? '';
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
}
