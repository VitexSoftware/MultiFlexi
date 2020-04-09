<?php

/**
 * Multi FlexiBee Setup  - Common menu class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

class BootstrapMenu extends \Ease\TWB4\Navbar {

    /**
     * Navigace.
     *
     * @var \Ease\Html\UlTag
     */
    public $nav = null;

    /**
     * Hlavní menu aplikace.
     *
     * @param string $name
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct($name = null, $content = null,
            $properties = []) {
        parent::__construct( new \Ease\Html\ImgTag('images/project-logo.svg', 'Project logo',['width'=>30,'height'=>30, 'class' => 'img-rounded d-inline-block align-top']), 'main-menu', ['class' => 'navbar-fixed-top' . (array_key_exists('class', $properties) ? $properties['class'] : '')]);

//        $user = \Ease\Shared::user();
//        \Ease\TWB4\Part::twBootstrapize();
//        if (!is_null($user->getUserID())) {
//            if (get_class($user) != 'Ease\Anonym') {
//
//                //   $this->addMenuItem('<a href="createaccount.php">' . \Ease\TWB4\Part::GlyphIcon('leaf') . ' ' . _('Registrace') . '</a>', 'right');
//                $this->addMenuItem(
//                    '
//<li class="divider-vertical"></li>
//<li class="dropdown">
//<a class="dropdown-toggle" href="login.php" data-toggle="dropdown"><i class="icon-circle-arrow-left"></i> '._('Sign In').'<strong class="caret"></strong></a>
//<div class="dropdown-menu" style="padding: 15px; padding-bottom: 0px; left: -120px;">
//<form method="post" class="navbar-form navbar-left" action="login.php" accept-charset="UTF-8">
//<input style="margin-bottom: 15px;" type="text" placeholder="'._('login').'" id="username" name="login">
//<input style="margin-bottom: 15px;" type="password" placeholder="'._('Password').'" id="password" name="password">
//<!-- input style="float: left; margin-right: 10px;" type="checkbox" name="remember-me" id="remember-me" value="1">
//<label class="string optional" for="remember-me"> '._('Remember Me').'</label -->
//<input class="btn btn-primary btn-block" type="submit" id="sign-in" value="'._('Sign In').'">
//</form>
//</div>', 'right'
//                );
//            }
//        } else {
//            $userMenu = '<li class="dropdown" style="width: 120px; text-align: right; background-image: url( '.$user->getIcon().' ) ;  background-repeat: no-repeat; background-position: left center; background-size: 40px 40px;"><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$user->getUserLogin().' <b class="caret"></b></a>
//<ul class="dropdown-menu" style="text-align: left; left: -60px;">
//<li><a href="settings.php">'.\Ease\TWB4\Part::GlyphIcon('wrench').'<i class="icon-cog"></i> '._('Settings').'</a></li>
//';
//
//            if ($user->getSettingValue('admin')) {
////                $userMenu .= '<li><a href="overview.php">'.\Ease\TWB4\Part::GlyphIcon('list').' '._('Přehled konfigurací').'</a></li>';
//            }
//
//            $this->addMenuItem($userMenu.'
//<li><a href="changepassword.php">'.\Ease\TWB4\Part::GlyphIcon('lock').' '._('Password change').'</a></li>
//<li><a href="about.php">'.\Ease\TWB4\Part::GlyphIcon('info-sign').' '._('About').'</a></li>
//<li class="divider"></li>
//<li><a href="logout.php">'.\Ease\TWB4\Part::GlyphIcon('off').' '._('Sign Off').'</a></li>
//</ul>
//</li>
//', 'right');
//        }
    }


}
