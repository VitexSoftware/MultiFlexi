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
 * Application Translation trait
 * 
 * Provides translation support for Application class
 */
trait ApplicationTranslation
{
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
        try {
            $translation = $this->getFluentPDO()
                ->from('app_translations')
                ->where('app_id', $this->getMyKey())
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
                    ->from('app_translations')
                    ->where('app_id', $this->getMyKey())
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
}