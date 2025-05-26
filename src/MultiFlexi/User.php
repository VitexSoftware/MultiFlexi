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

use Ease\SQL\Orm;

/**
 * MultiFlexi - Instance Management Class.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2015-2023 Vitex Software
 */
class User extends \Ease\User implements \MultiFlexi\Ui\columns
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
    public array $filter = [];

    /**
     * Tabulka uživatelů.
     */
    public string $myTable = 'user';

    /**
     * Sloupeček obsahující datum vložení záznamu do shopu.
     */
    public string $createColumn = 'DatCreate';

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do shopu.
     */
    public string $lastModifiedColumn = 'DatSave';

    /**
     * Engine Keyword.
     */
    public string $keyword = 'user';

    /**
     * MultiFlexi User.
     *
     * @param int|string $userID
     */
    public function __construct($userID = null)
    {
        $this->settingsColumn = 'settings';
        $this->nameColumn = 'login';
        parent::__construct($this->getDataValue('id'));

        if ($userID) {
            $this->setKeyColumn(is_numeric($userID) ? 'id' : 'login');
            $this->loadFromSQL($userID);
        }
    }

    #[\Override]
    public function __sleep(): array
    {
        $this->pdo = null;
        $this->fluent = null;

        return parent::__sleep();
    }

    public function getNameColumn(): string
    {
        return 'login';
    }

    /**
     * Vrací odkaz na ikonu.
     */
    public function getIcon(): string
    {
        $Icon = $this->GetSettingValue('icon');

        if (null === $Icon) {
            return parent::getIcon();
        }

        return $Icon;
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
     */
    public function getUserName(): string
    {
        $longname = trim($this->getDataValue('firstname').' '.$this->getDataValue('lastname'));

        if (\strlen($longname)) {
            return $longname;
        }

        return parent::getUserName();
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
     * @return null|bool
     */
    public function tryToLogin(array $formData): bool
    {
        if (empty($formData) === true) {
            return false;
        }

        $login = addslashes($formData[$this->loginColumn]);
        $password = addslashes($formData[$this->passwordColumn]);

        if (empty($login)) {
            $this->addStatusMessage(_('missing login'), 'event');

            return false;
        }

        if (empty($password)) {
            $this->addStatusMessage(_('missing password'), 'event');

            return false;
        }

        if ($this->loadFromSQL([$this->loginColumn => $login])) {
            $this->setObjectName();

            if (
                $this->passwordValidation(
                    $password,
                    $this->getDataValue($this->passwordColumn),
                )
            ) {
                if ($this->isAccountEnabled()) {
                    return $this->loginSuccess();
                }

                $this->userID = null;

                return false;
            }

            $this->userID = null;

            if (!empty($this->getData())) {
                $this->addStatusMessage(_('invalid password'), 'event');
            }

            $this->dataReset();
            $result = false;
        } else {
            $this->addStatusMessage(sprintf(
                _('user %s does not exist'),
                $login,
                'error',
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

            if (\count($passwordStack) !== 2) {
                return false;
            }

            if (md5($passwordStack[1].$plainPassword) === $passwordStack[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set logging.
     *
     * @return bool
     */
    public function loginSuccess()
    {
        $userId = $this->getUserID();

        LogToSQL::singleton()->setUser($userId);

        $_SESSION['user_id'] = $userId;
        $_SESSION['ws_token'] = bin2hex(random_bytes(16));

        return parent::loginSuccess();
    }

    /**
     * Perform User signoff.
     */
    public function logout(): bool
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

        return md5($passwordSalt.$plainTextPassword).':'.$passwordSalt;
    }

    /**
     * Změní uživateli uložené heslo.
     *
     * @param string $newPassword nové heslo
     *
     * @return string password hash
     */
    public function passwordChange($newPassword): bool
    {
        return $this->dbsync([$this->passwordColumn => $this->encryptPassword($newPassword), $this->getKeyColumn() => $this->getUserID()]);
    }

    /**
     * @see https://datatables.net/examples/advanced_init/column_render.html
     *
     * @return string Column rendering
     */
    public function columnDefs()
    {
        return <<<'EOD'

"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]
,

EOD;
    }

    /**
     * Common instance of User class.
     *
     * @param null|mixed $user
     *
     * @return User
     */
    public static function singleton($user = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = null === $user ? new self() : $user;
        }

        return self::$instance;
    }
}
