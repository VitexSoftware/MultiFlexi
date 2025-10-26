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

use Ease\Html\InputHiddenTag;

/**
 * Enhanced form widget with automatic CSRF protection.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SecureForm extends \Ease\TWB4\Form
{
    /**
     * Create secure form with CSRF protection.
     *
     * @param array $properties
     * @param mixed $formContents
     * @param mixed $tagProperties
     */
    public function __construct($properties = [], $formContents = null, $tagProperties = [])
    {
        parent::__construct($properties, $tagProperties, $formContents);

        // Add CSRF token automatically
        $this->addCSRFToken();
    }

    /**
     * Create a secure form factory method.
     */
    public static function create(string $method = 'POST', string $action = '', array $attributes = []): self
    {
        $attributes['method'] = $method;

        if (!empty($action)) {
            $attributes['action'] = $action;
        }

        return new self($attributes);
    }

    /**
     * Add CSRF token to form.
     */
    private function addCSRFToken(): void
    {
        if (isset($GLOBALS['csrfProtection'])) {
            $csrfProtection = $GLOBALS['csrfProtection'];
            $token = $csrfProtection->generateToken();
            $this->addItem(new InputHiddenTag('csrf_token', $token));
        }
    }
}
