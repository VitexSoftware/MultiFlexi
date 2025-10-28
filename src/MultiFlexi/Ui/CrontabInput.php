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
 * Description of CrontabInput.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */

/**
 * CrontabInput renders a cron expression input field with validation and UI enhancements.
 *
 * This class uses the cron-expression-input.min.js and cron-expression-input.min.css assets
 * to provide a user-friendly cron expression editor with Bootstrap 4.6 styling.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CrontabInput extends \Ease\Html\PairTag
{
    /**
     * Construct a cron expression input field with Bootstrap 4.6 form styling.
     *
     * @param string $name       the input field name and id
     * @param string $value      the initial cron expression value
     * @param array  $attributes additional HTML attributes for the input
     */
    public function __construct(string $name, string $value = '', array $attributes = [])
    {
        $attributes['name'] = $name;
        $attributes['id'] = $name;
        $attributes['value'] = $value;
        
        // Bootstrap 4.6 form control styling
        $existingClasses = $attributes['class'] ?? '';
        $attributes['class'] = trim($existingClasses . ' form-control');
        
        $attributes['color'] = 'd58512';
        $attributes['data-cron-expression-input'] = 'true';
        parent::__construct('cron-expression-input', $attributes);
    }

    /**
     * Include required JS and CSS for the cron expression input.
     */
    public static function includeAssets(): void
    {
        // These should be called from the page controller or panel
        \Ease\WebPage::singleton()->includeCss('css/cron-expression-input.min.css');
        \Ease\WebPage::singleton()->includeCss('css/cron-expression-input-custom.css');
        \Ease\WebPage::singleton()->includeJavaScript('js/cron-expression-input.min.js');
    }
}
