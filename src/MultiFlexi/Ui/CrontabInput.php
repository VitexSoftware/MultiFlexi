<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CrontabInput
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
use Ease\Html\DivTag;

/**
 * CrontabInput renders a cron expression input field with validation and UI enhancements.
 *
 * This class uses the cron-expression-input.min.js and cron-expression-input.min.css assets
 * to provide a user-friendly cron expression editor.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CrontabInput extends \Ease\Html\PairTag {

    /**
     * Construct a cron expression input field.
     *
     * @param string $name        The input field name and id.
     * @param string $value       The initial cron expression value.
     * @param array  $attributes  Additional HTML attributes for the input.
     */
    public function __construct(string $name, string $value = '', array $attributes = []) {
        parent::__construct('cron-expression-input', ['color' => 'd58512', 'value' => $value]);
    }

    /**
     * Include required JS and CSS for the cron expression input.
     *
     * @return void
     */
    public static function includeAssets(): void {
        // These should be called from the page controller or panel
        \Ease\WebPage::singleton()->includeCss('css/cron-expression-input.min.css');
        \Ease\WebPage::singleton()->includeJavaScript('js/cron-expression-input.min.js');
    }
}
