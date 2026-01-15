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
 * @no-named-arguments
 */
class BootstrapMenu extends \Ease\TWB4\Navbar
{
    /**
     * Navigation.
     */
    public ?\Ease\Html\UlTag $nav = null;

    /**
     * Application Main Menu.
     *
     * @param string $name
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct(
        $name = null,
        $content = null,
        $properties = [],
    ) {
        $this->mainpage = 'main.php';
        parent::__construct(new \Ease\Html\ImgTag('images/project-logo.svg', $name, ['width' => 50, 'height' => 50, 'class' => 'img-rounded d-inline-block align-top']), 'main-menu', ['class' => 'navbar-fixed-top'.(\array_key_exists('class', $properties) ? $properties['class'] : '')]);

        if (\Ease\Shared::user()->isLogged() === false) {
            $loginForm = new \Ease\TWB4\Form(['action' => 'login.php', 'class' => 'form-inline my-2 my-lg-0']);
            $loginForm->addItem(new \Ease\Html\InputTextTag('login', WebPage::getRequestValue('login'), ['class' => 'form-control mr-sm-2', 'placeholder' => _('Login')]));
            $loginForm->addItem(new \Ease\Html\InputPasswordTag('password', WebPage::getRequestValue('password'), ['class' => 'form-control mr-sm-2', 'placeholder' => _('Password')]));
            $loginForm->addItem(new \Ease\TWB4\SubmitButton(_('Sign In'), 'success my-2 my-sm-0', ['title' => _('Sign in to application'),'id' => 'signinbuttonmenu']));

            // Add CSRF token to form if CSRF protection is enabled

            if (\Ease\Shared::cfg('CSRF_PROTECTION_ENABLED', true) && isset($GLOBALS['csrfProtection'])) {
                $csrfToken = $GLOBALS['csrfProtection']->generateToken();
                $loginForm->addItem(new \Ease\Html\InputHiddenTag('csrf_token', $csrfToken));
            }

            $loginForm->addItem('&nbsp;&nbsp;&nbsp;');
            $loginForm->addItem(new \Ease\TWB4\LinkButton('passwordrecovery.php', _('Password recovery'), 'warning my-2 my-sm-0', ['title' => _('Recover your password'),'id' => 'passwordrecoverybuttonmuenu']));
            $this->addMenuItem($loginForm);
            $this->addItem($content);
        }
    }
}
