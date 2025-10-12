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
 * Configuration Translation trait
 * 
 * Provides translation support for Conffield class
 */
trait ConfigurationTranslation
{
    /**
     * Get localized value for configuration field
     *
     * @param string $field Field name (description, hint, name)
     * @param string|null $lang Language code (null for current locale)
     * @return string|null
     */
    public function getLocalizedValue(string $field, ?string $lang = null): ?string
    {
        if ($lang === null) {
            $lang = substr(\Ease\Locale::$localeUsed ?? 'en_US', 0, 2);
        }

        // Try to get from translations table
        try {
            $translation = $this->getFluentPDO()
                ->from('configuration_translations')
                ->where('configuration_id', $this->getMyKey())
                ->where('lang', $lang)
                ->fetch();

            if ($translation && isset($translation[$field]) && $translation[$field] !== null) {
                return $translation[$field];
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        // Fallback to default language
        if ($lang !== 'en') {
            try {
                $translation = $this->getFluentPDO()
                    ->from('configuration_translations')
                    ->where('configuration_id', $this->getMyKey())
                    ->where('lang', 'en')
                    ->fetch();

                if ($translation && isset($translation[$field]) && $translation[$field] !== null) {
                    return $translation[$field];
                }
            } catch (\Exception $e) {
                // Table might not exist yet
            }
        }

        // Fallback to main table
        return $this->getDataValue($field);
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescription(?string $lang = null): ?string
    {
        return $this->getLocalizedValue('description', $lang);
    }

    /**
     * Get localized hint
     */
    public function getLocalizedHint(?string $lang = null): ?string
    {
        return $this->getLocalizedValue('hint', $lang);
    }

    /**
     * Get localized name
     */
    public function getLocalizedName(?string $lang = null): ?string
    {
        return $this->getLocalizedValue('name', $lang);
    }

    /**
     * Get all application configs with translations
     *
     * @param int $appId Application ID
     * @param string|null $lang Language code (null for current locale)
     * @return array
     */
    public function appConfigsLocalized(int $appId, ?string $lang = null): array
    {
        if ($lang === null) {
            $lang = substr(\Ease\Locale::$localeUsed ?? 'en_US', 0, 2);
        }

        $configs = $this->appConfigs($appId);
        
        // Enhance with translations
        foreach ($configs as &$config) {
            try {
                // Get translations for this config
                $translation = $this->getFluentPDO()
                    ->from('configuration_translations')
                    ->where('configuration_id', $config['id'])
                    ->where('lang', $lang)
                    ->fetch();
                
                if ($translation) {
                    // Override with translated values
                    if (!empty($translation['description'])) {
                        $config['description'] = $translation['description'];
                    }
                    if (!empty($translation['hint'])) {
                        $config['hint'] = $translation['hint'];
                    }
                    if (!empty($translation['name'])) {
                        $config['name'] = $translation['name'];
                    }
                } elseif ($lang !== 'en') {
                    // Try English fallback
                    $englishTrans = $this->getFluentPDO()
                        ->from('configuration_translations')
                        ->where('configuration_id', $config['id'])
                        ->where('lang', 'en')
                        ->fetch();
                    
                    if ($englishTrans) {
                        if (!empty($englishTrans['description'])) {
                            $config['description'] = $englishTrans['description'];
                        }
                        if (!empty($englishTrans['hint'])) {
                            $config['hint'] = $englishTrans['hint'];
                        }
                        if (!empty($englishTrans['name'])) {
                            $config['name'] = $englishTrans['name'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Translation tables might not exist yet
            }
        }
        
        return $configs;
    }
}