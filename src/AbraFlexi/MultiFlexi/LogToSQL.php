<?php

/**
 * Database Engine class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2022 Vitex@hippy.cz (G)
 */

namespace AbraFlexi\MultiFlexi;

/**
 * Description of LogToSQL
 *
 * @author vitex
 */
class LogToSQL extends \Ease\SQL\Engine implements \Ease\Logger\Loggingable {

    /**
     * Saves obejct instace (singleton...).
     */
    private static $instance = null;
    public $myTable = 'log';
    public $companyId = null;
    public $applicationId = null;
    public $userId = null;

    /**
     * 
     */
    public function __construct() {
//        parent::__construct();
        $this->setUser(User::singleton()->getUserID());
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako
     * konstruktor) se bude v ramci behu programu pouzivat pouze jedna jeho
     * instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a
     * priklad
     */
    public static function singleton() {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * ID of current company
     * @param int $id
     */
    public function setCompany($id) {
        $this->companyId = $id;
    }

    /**
     * ID of current application
     * @param int $id
     */
    public function setApplication($id) {
        $this->applicationId = $id;
    }

    /**
     * ID of current user
     * @param int $id
     */
    public function setUser($id) {
        $this->userId = $id;
    }

    /**
     * Zapise zapravu do logu.
     *
     * @param string $caller  název volajícího objektu
     * @param string $message zpráva
     * @param string $type    typ zprávy (success|info|error|warning|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($caller, $message, $type = 'message') {
        return $this->insertToSQL([
                    'venue' => self::venuize($caller),
                    'severity' => $type,
                    'message' => addslashes($message),
                    'apps_id' => $this->applicationId,
                    'user_id' => $this->userId,
                    'company_id' => $this->companyId
        ]);
    }

    /**
     * Prepare venue able to be saved into sql column
     * 
     * @param mixed $caller
     */
    public static function venuize($caller) {
        switch (gettype($caller)) {
            case 'object':
                if(method_exists($caller, 'getObjectName')){
                    $venue = $caller->getObjectName();
                } else {
                    $venue = get_class($caller);
                }
                break;
            case 'string':
            default:
                $venue = $caller;
                break;
        }
        return substr($venue,254);
    }
    
}
