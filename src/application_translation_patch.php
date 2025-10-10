<?php
/**
 * Patch for Application.php to handle translations properly
 * This code should be integrated into the Application class
 */

// Add this to the Application class in multiflexi-core

/**
 * Import JSON App Definition file with full translation support.
 *
 * @param string $jsonFile Path to the JSON file
 * @return array Fields that were imported
 */
public function importAppJson($jsonFile): array
{
    $fields = [];

    // Validate JSON against schema before import
    $schemaFile = self::$appSchema;

    if (!file_exists($schemaFile)) {
        throw new \RuntimeException(_('Schema file not found: ') . $schemaFile);
    }

    $appSpecRaw = file_get_contents($jsonFile);

    if (empty($appSpecRaw)) {
        throw new \RuntimeException(_('App definition file is empty: ') . $jsonFile);
    }

    $appSpec = json_decode($appSpecRaw, true);

    if (json_last_error() !== \JSON_ERROR_NONE) {
        throw new \RuntimeException(_('Invalid JSON: ') . json_last_error_msg());
    }

    // Extract basic fields
    $basicFields = ['executable', 'homepage', 'uuid', 'version', 'ociimage', 'setup', 'deploy', 'cmdparams'];
    foreach ($basicFields as $field) {
        if (isset($appSpec[$field])) {
            $fields[$field] = $appSpec[$field];
        }
    }

    // Extract localized fields
    $localizedFields = ['name', 'description'];
    $defaultLang = 'en'; // Default language
    $appTranslations = [];

    foreach ($localizedFields as $field) {
        if (isset($appSpec[$field])) {
            if (\is_string($appSpec[$field])) {
                // Legacy string format - use as default
                $fields[$field] = $appSpec[$field];
                $appTranslations[$defaultLang][$field] = $appSpec[$field];
            } elseif (\is_array($appSpec[$field])) {
                // Localized object format
                $fields[$field] = $appSpec[$field][$defaultLang] ?? reset($appSpec[$field]);
                
                foreach ($appSpec[$field] as $lang => $value) {
                    if (!isset($appTranslations[$lang])) {
                        $appTranslations[$lang] = [];
                    }
                    $appTranslations[$lang][$field] = $value;
                }
            }
        }
    }

    // Process requirements
    if (isset($appSpec['requirements']) && \is_array($appSpec['requirements'])) {
        $fields['requirements'] = implode(',', $appSpec['requirements']);
    }

    // Process topics
    if (isset($appSpec['topics']) && \is_array($appSpec['topics'])) {
        $fields['topics'] = implode(',', $appSpec['topics']);
    }

    // Save basic app data
    $this->takeData($fields);
    $appId = $this->saveToSQL();

    if (!$appId) {
        throw new \RuntimeException(_('Failed to save application'));
    }

    // Save app translations
    $this->saveTranslations($appId, $appTranslations);

    // Process environment configurations with translations
    if (isset($appSpec['environment']) && \is_array($appSpec['environment'])) {
        $this->importEnvironmentConfigs($appId, $appSpec['environment']);
    }

    // Process artifacts with translations
    if (isset($appSpec['artifacts']) && \is_array($appSpec['artifacts'])) {
        $this->importArtifacts($appId, $appSpec['artifacts']);
    }

    return $fields;
}

/**
 * Save application translations to database
 */
private function saveTranslations(int $appId, array $translations): void
{
    foreach ($translations as $lang => $data) {
        try {
            $this->getFluentPDO()
                ->insertInto('app_translations', array_merge($data, [
                    'app_id' => $appId,
                    'lang' => $lang,
                ]))
                ->onDuplicateKeyUpdate($data)
                ->execute();
        } catch (\Exception $e) {
            $this->addStatusMessage(
                sprintf(_('Failed to save %s translation: %s'), $lang, $e->getMessage()), 
                'error'
            );
        }
    }
}

/**
 * Import environment configurations with translations
 */
private function importEnvironmentConfigs(int $appId, array $environment): void
{
    $conffield = new Conffield();
    
    foreach ($environment as $key => $config) {
        $configData = [
            'app_id' => $appId,
            'keyname' => $key,
            'type' => $config['type'] ?? 'string',
            'defval' => $config['defval'] ?? '',
            'required' => isset($config['required']) && $config['required'] ? 1 : 0,
        ];

        // Handle description - default language
        $defaultLang = 'en';
        if (isset($config['description'])) {
            if (\is_string($config['description'])) {
                $configData['description'] = $config['description'];
            } elseif (\is_array($config['description'])) {
                $configData['description'] = $config['description'][$defaultLang] ?? reset($config['description']);
            }
        }

        // Save configuration
        $conffield->takeData($configData);
        $configId = $conffield->saveToSQL();

        // Save configuration translations
        if ($configId && isset($config['description']) && \is_array($config['description'])) {
            $this->saveConfigurationTranslations($configId, $config);
        }
    }
}

/**
 * Save configuration translations
 */
private function saveConfigurationTranslations(int $configId, array $config): void
{
    $translations = [];
    
    // Process description translations
    if (isset($config['description']) && \is_array($config['description'])) {
        foreach ($config['description'] as $lang => $description) {
            $translations[$lang]['description'] = $description;
        }
    }

    // Process hint translations if available
    if (isset($config['hint']) && \is_array($config['hint'])) {
        foreach ($config['hint'] as $lang => $hint) {
            if (!isset($translations[$lang])) {
                $translations[$lang] = [];
            }
            $translations[$lang]['hint'] = $hint;
        }
    }

    // Process name translations if available
    if (isset($config['name']) && \is_array($config['name'])) {
        foreach ($config['name'] as $lang => $name) {
            if (!isset($translations[$lang])) {
                $translations[$lang] = [];
            }
            $translations[$lang]['name'] = $name;
        }
    }

    // Save translations
    foreach ($translations as $lang => $data) {
        try {
            $this->getFluentPDO()
                ->insertInto('configuration_translations', array_merge($data, [
                    'configuration_id' => $configId,
                    'lang' => $lang,
                ]))
                ->onDuplicateKeyUpdate($data)
                ->execute();
        } catch (\Exception $e) {
            $this->addStatusMessage(
                sprintf(_('Failed to save configuration %s translation: %s'), $lang, $e->getMessage()),
                'error'
            );
        }
    }
}

/**
 * Get localized value for application field
 *
 * @param string $field Field name (name, description, etc.)
 * @param string|null $lang Language code (null for current locale)
 * @return string|null
 */
public function getLocalizedValue(string $field, ?string $lang = null): ?string
{
    if ($lang === null) {
        $lang = substr(\Ease\Locale::$localeUsed ?? 'en_US', 0, 2);
    }

    // Try to get from translations table
    $translation = $this->getFluentPDO()
        ->from('app_translations')
        ->where('app_id', $this->getMyKey())
        ->where('lang', $lang)
        ->fetch();

    if ($translation && isset($translation[$field]) && $translation[$field] !== null) {
        return $translation[$field];
    }

    // Fallback to default language
    if ($lang !== 'en') {
        $translation = $this->getFluentPDO()
            ->from('app_translations')
            ->where('app_id', $this->getMyKey())
            ->where('lang', 'en')
            ->fetch();

        if ($translation && isset($translation[$field]) && $translation[$field] !== null) {
            return $translation[$field];
        }
    }

    // Fallback to main table
    return $this->getDataValue($field);
}

/**
 * Get localized name
 */
public function getLocalizedName(?string $lang = null): ?string
{
    return $this->getLocalizedValue('name', $lang);
}

/**
 * Get localized description
 */
public function getLocalizedDescription(?string $lang = null): ?string
{
    return $this->getLocalizedValue('description', $lang);
}

/**
 * Export application as JSON with all translations
 */
public function exportToJson(): string
{
    $appData = $this->getData();
    $output = [
        '$schema' => 'https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json',
    ];

    // Basic fields
    $basicFields = ['executable', 'homepage', 'uuid', 'version', 'ociimage', 'setup', 'deploy', 'cmdparams'];
    foreach ($basicFields as $field) {
        if (!empty($appData[$field])) {
            $output[$field] = $appData[$field];
        }
    }

    // Get translations
    $translations = $this->getFluentPDO()
        ->from('app_translations')
        ->where('app_id', $this->getMyKey())
        ->fetchAll();

    // Build localized fields
    $localizedFields = ['name', 'description'];
    foreach ($localizedFields as $field) {
        $localizedData = [];
        
        // Add translations
        foreach ($translations as $trans) {
            if (!empty($trans[$field])) {
                $localizedData[$trans['lang']] = $trans[$field];
            }
        }
        
        // Add default from main table if not in translations
        if (!empty($appData[$field]) && !isset($localizedData['en'])) {
            $localizedData['en'] = $appData[$field];
        }
        
        if (!empty($localizedData)) {
            $output[$field] = count($localizedData) === 1 ? reset($localizedData) : $localizedData;
        }
    }

    // Export requirements
    if (!empty($appData['requirements'])) {
        $output['requirements'] = explode(',', $appData['requirements']);
    }

    // Export topics
    if (!empty($appData['topics'])) {
        $output['topics'] = explode(',', $appData['topics']);
    }

    // Export environment configurations with translations
    $conffield = new Conffield();
    $configs = $conffield->appConfigs($this->getMyKey());
    
    if (!empty($configs)) {
        $output['environment'] = [];
        
        foreach ($configs as $config) {
            $envConfig = [
                'type' => $config['type'],
                'defval' => $config['defval'],
                'required' => (bool)$config['required'],
            ];

            // Get configuration translations
            $configTrans = $this->getFluentPDO()
                ->from('configuration_translations')
                ->where('configuration_id', $config['id'])
                ->fetchAll();

            // Build localized description
            $descriptions = [];
            foreach ($configTrans as $trans) {
                if (!empty($trans['description'])) {
                    $descriptions[$trans['lang']] = $trans['description'];
                }
            }
            
            if (!empty($config['description']) && !isset($descriptions['en'])) {
                $descriptions['en'] = $config['description'];
            }
            
            if (!empty($descriptions)) {
                $envConfig['description'] = count($descriptions) === 1 ? reset($descriptions) : $descriptions;
            }

            $output['environment'][$config['keyname']] = $envConfig;
        }
    }

    return json_encode($output, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE);
}