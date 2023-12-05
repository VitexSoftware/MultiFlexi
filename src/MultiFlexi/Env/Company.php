<?php

declare(strict_types=1);

/**
 * Multi Flexi - Company Environment Handler
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Company
 *
 * @author vitex
 */
class Company extends \MultiFlexi\Environmentor implements Injector
{
    /**
     * List of all known keys
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return [];
    }

    /**
     * Company Environment
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        $companyEnvironment = [];
        $companyEnvironmentRaw = $this->engine->company->getEnvironment();
        foreach ($companyEnvironmentRaw as $key => $value) {
            $companyEnvironment[$key]['value'] = $value;
        }
        return $this->addSelfAsSource($companyEnvironment);
    }
}
