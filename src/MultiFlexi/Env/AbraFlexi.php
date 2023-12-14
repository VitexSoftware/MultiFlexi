<?php

declare(strict_types=1);

/**
 * Multi Flexi - AbraFlexi environment variables handler
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of AbraFlexi
 *
 * @author vitex
 */
class AbraFlexi extends \MultiFlexi\Environmentor implements Injector
{
    /**
     * List of all known keys
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return [
            'ABRAFLEXI_URL',
            'ABRAFLEXI_LOGIN',
            'ABRAFLEXI_PASSWORD',
            'ABRAFLEXI_COMPANY'
        ];
    }

    /**
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        $abraFlexiEnv = [];
        if ($this->engine->company->getDataValue('server')) {
            $server = new \MultiFlexi\Servers($this->engine->company->getDataValue('server'));
            if ($server->getDataValue('type') == 'AbraFlexi') {
                $platformHelper = new \MultiFlexi\AbraFlexi\Company($this->engine->company->getMyKey(), $server->getData());
                foreach ($platformHelper->getEnvironment() as $key => $value) {
                    $abraFlexiEnv[$key] = ['value' => $value];
                }
            }
        }
        return $this->addMetaData($this->addSelfAsSource($abraFlexiEnv));
    }
    
    /**
     * 
     * @return string
     */
    public static function name(){
        return 'AbraFlexi';
    }
    
    /**
     * 
     * @return string
     */
    public static function description(){
        return _('Provide Connection credentials for AbraFlexi');
    }
    
}
