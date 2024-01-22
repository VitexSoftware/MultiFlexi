<?php

namespace MultiFlexi;

use Ease\SQL\Orm;

/**
 * Multi Flexi - Instance Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2015-2023 Vitex Software
 */
class User extends \Ease\User
{
    use Orm;

    public $useKeywords = [
        'login' => 'STRING',
        'firstname' => 'STRING',
        'lastname' => 'STRING',
        'email' => 'STRING',
    ];
    public $keywordsInfo = [
        'login' => [],
        'firstname' => [],
        'lastname' => [],
        'email' => [],
    ];

    /**
     *
     * @var array
     */
    public $filter = [];

    /**
     * Tabulka uživatelů.
     *
     * @var string
     */
    public $myTable = 'user';

    /**
     * Sloupeček obsahující datum vložení záznamu do shopu.
     *
     * @var string
     */
    public $createColumn = 'DatCreate';

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do shopu.
     *
     * @var string
     */
    public $lastModifiedColumn = 'DatSave';

    /**
     * Budeme používat serializovaná nastavení uložená ve sloupečku.
     *
     * @var string
     */
    public $settingsColumn = 'settings';

    /**
     * Klíčové slovo.
     *
     * @var string
     */
    public $keyword = 'user';

    /**
     * Jmenný sloupec.
     *
     * @var string
     */
    public $nameColumn = 'login';

    /**
     * MultiFlexi User
     *
     * @param int|string $userID
     */
    public function __construct($userID = null)
    {
        if ($userID) {
            $this->setKeyColumn(is_numeric($userID) ? 'id' : 'login');
            $this->loadFromSQL($userID);
            parent::__construct($this->getDataValue('id'));
        }
    }

    /**
     * Vrací odkaz na ikonu.
     *
     * @return string
     */
    public function getIcon()
    {
        $Icon = $this->GetSettingValue('icon');
        if (is_null($Icon)) {
            return parent::getIcon();
        } else {
            return $Icon;
        }
    }

    /**
     * Vrací ID aktuálního záznamu.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->getMyKey();
    }

    /**
     * Give you user name.
     *
     * @return string
     */
    public function getUserName()
    {
        $longname = trim($this->getDataValue('firstname') . ' ' . $this->getDataValue('lastname'));
        if (strlen($longname)) {
            return $longname;
        } else {
            return parent::getUserName();
        }
    }

    public function getRecordName()
    {
        return $this->getUserName();
    }

    public function getEmail()
    {
        return $this->getDataValue('email');
    }

    /**
     * Pokusí se o přihlášení.
     * Try to Sign in.
     *
     * @param array $formData pole dat z přihlaš. formuláře např. $_REQUEST
     *
     * @return null|boolean
     */
    public function tryToLogin($formData)
    {
        if (empty($formData) === true) {
            return false;
        }
        $login = addSlashes($formData[$this->loginColumn]);
        $password = addSlashes($formData[$this->passwordColumn]);
        if (empty($login)) {
            $this->addStatusMessage(_('missing login'), 'event');
            return null;
        }
        if (empty($password)) {
            $this->addStatusMessage(_('missing password'), 'event');
            return null;
        }
        if ($this->loadFromSQL([$this->loginColumn => $login])) {
            $this->setObjectName();
            if (
                $this->passwordValidation(
                    $password,
                    $this->getDataValue($this->passwordColumn)
                )
            ) {
                if ($this->isAccountEnabled()) {
                    return $this->loginSuccess();
                } else {
                    $this->userID = null;
                    return false;
                }
            } else {
                $this->userID = null;
                if (!empty($this->getData())) {
                    $this->addStatusMessage(_('invalid password'), 'event');
                }
                $this->dataReset();
                $result = false;
            }
        } else {
            $this->addStatusMessage(sprintf(
                _('user %s does not exist'),
                $login,
                'error'
            ));
            $result = false;
        }
        return $result;
    }

    /**
     * Ověření hesla.
     *
     * @param string $plainPassword     heslo v nešifrované podobě
     * @param string $encryptedPassword šifrovné heslo
     *
     * @return bool
     */
    public static function passwordValidation($plainPassword, $encryptedPassword)
    {
        if ($plainPassword && $encryptedPassword) {
            $passwordStack = explode(':', $encryptedPassword);
            if (sizeof($passwordStack) != 2) {
                return false;
            }
            if (md5($passwordStack[1] . $plainPassword) == $passwordStack[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set logging
     *
     * @return boolean
     */
    public function loginSuccess()
    {
        LogToSQL::singleton()->setUser($this->getUserID());
        return parent::loginSuccess();
    }

    /**
     * Perform User signoff
     *
     * @return boolean
     */
    public function logout()
    {
        $this->dataReset();
        return parent::logout();
    }

    /**
     * Zašifruje heslo.
     *
     * @param string $plainTextPassword nešifrované heslo (plaintext)
     *
     * @return string Encrypted password
     */
    public static function encryptPassword($plainTextPassword)
    {
        $encryptedPassword = '';
        for ($i = 0; $i < 10; ++$i) {
            $encryptedPassword .= \Ease\Functions::randomNumber();
        }
        $passwordSalt = substr(md5($encryptedPassword), 0, 2);
        $encryptedPassword = md5($passwordSalt . $plainTextPassword) . ':' . $passwordSalt;
        return $encryptedPassword;
    }

    /**
     * Změní uživateli uložené heslo.
     *
     * @param string $newPassword nové heslo
     *
     * @return string password hash
     */
    public function passwordChange($newPassword)
    {
        return $this->dbsync([$this->passwordColumn => $this->encryptPassword($newPassword), $this->getKeyColumn() => $this->getUserID()]);
    }

    /**
     * @link https://datatables.net/examples/advanced_init/column_render.html
     *
     * @return string Column rendering
     */
    public function columnDefs()
    {
        return '
"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]            
,
';
    }

    /**
     * Common instance of User class
     *
     * @return User
     */
    public static function singleton($user = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = is_null($user) ? new self() : $user;
        }
        return self::$instance;
    }
}
