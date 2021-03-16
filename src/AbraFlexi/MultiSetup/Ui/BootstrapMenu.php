<?php

/**
 * Multi AbraFlexi Setup  - Common menu class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

class BootstrapMenu extends \Ease\TWB4\Navbar {

    /**
     * Navigace.
     *
     * @var \Ease\Html\UlTag
     */
    public $nav = null;

    /**
     * Brand icon link
     * 
     * @var string 
     */
    public $mainpage = 'main.php';

    /**
     * Hlavní menu aplikace.
     *
     * @param string $name
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct($name = null, $content = null,
            $properties = []) {

        parent::__construct(new \Ease\Html\ImgTag('images/project-logo.svg', 'Project logo', ['width' => 30, 'height' => 30, 'class' => 'img-rounded d-inline-block align-top']), 'main-menu', ['class' => 'navbar-fixed-top' . (array_key_exists('class', $properties) ? $properties['class'] : '')]);

        if (\Ease\Shared::user()->isLogged() === false) {
            $loginForm = new \Ease\TWB4\Form(['action' => 'login.php', 'class' => 'form-inline my-2 my-lg-0']);
            $loginForm->addItem(new \Ease\Html\InputTextTag('login', WebPage::getRequestValue('login'), ['class' => 'form-control mr-sm-2', 'placeholder' => _('Login')]));
            $loginForm->addItem(new \Ease\Html\InputPasswordTag('password', WebPage::getRequestValue('password'), ['class' => 'form-control mr-sm-2', 'placeholder' => _('Password')]));
            $loginForm->addItem(new \Ease\TWB4\SubmitButton(_('Sign In'), 'success my-2 my-sm-0'));
            $loginForm->addItem('&nbsp;&nbsp;&nbsp;');
            $loginForm->addItem(new \Ease\TWB4\LinkButton('passwordrecovery.php', _('Password recovery'), 'warning my-2 my-sm-0'));
            $this->addMenuItem($loginForm);
        }
    }

}
