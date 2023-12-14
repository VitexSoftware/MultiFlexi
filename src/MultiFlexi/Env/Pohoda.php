<?php

declare(strict_types=1);

/**
 * Multi Flexi - Stormware Pohoda Environment handler
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Pohoda
 *
 * @author vitex
 */
class Pohoda extends \MultiFlexi\Environmentor implements Injector {

    /**
     * List of all known keys
     *
     * @return array
     */
    public static function allKeysHandled() {
        return [
            'POHODA_URL',
            'POHODA_USERNAME',
            'POHODA_PASSWORD',
            'POHODA_ICO'
        ];
    }

    /**
     *
     * @return array
     */
    public function getEnvironment(): array {
        $pohodaEnv = [];
        if ($this->engine->company->getDataValue('server')) {
            $server = new \MultiFlexi\Servers($this->engine->company->getDataValue('server'));
            if ($server->getDataValue('type') == 'Pohoda') {
                $platformHelper = new \MultiFlexi\Pohoda\Company($this->engine->company->getMyKey(), $server->getData());
                $pohodaEnv = $platformHelper->getEnvironment();
            }
        }
        return $this->addMetaData($this->addSelfAsSource($pohodaEnv));
    }

    /**
     * 
     * @return string
     */
    public static function name() {
        return _('Pohoda mServer');
    }
    
    /**
     * 
     * @return string
     */
    public static function description(){
        return _('Provide Connection information for Stromware Pohoda');
    }
   
    
}
