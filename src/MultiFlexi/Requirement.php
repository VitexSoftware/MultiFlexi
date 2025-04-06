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

/**
 * Description of Requirement.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Requirement
{
    /**
     * List of classes in \MultiFlexi\CredentialType\ name space.
     *
     * @return array<string, string>
     */
    public static function getCredentialProviders(): array
    {
        $forms = [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\CredentialType');

        foreach (\Ease\Functions::classesInNamespace('MultiFlexi\CredentialType') as $form) {
            $forms[$form] = '\MultiFlexi\CredentialType\\'.$form;
        }

        return $forms;
    }

    /**
     * List of credential types.
     */
    public static function getCredentialTypes(Company $company): array
    {
        $credentialTypes = [];
        $credentialType = new CredentialType();

        foreach ($credentialType->listingQuery()->where('credential_type.company_id', $company->getMyKey()) as $credType) {
            $credentialTypes[$credType['class']][$credType['id']] = $credType;
        }

        return $credentialTypes;
    }

    /**
     * List of company credentials.
     *
     * @return type
     */
    public static function getCredentials(Company $company): array
    {
        $credentialsByType = [];
        $credentialType = new Credential();

        foreach ($credentialType->listingQuery()->select(['credential_type.*', 'credentials.id AS credential_id'])->leftJoin('credential_type ON credentials.credential_type_id = credential_type.id')->where('credential_type.company_id', $company->getMyKey()) as $credential) {
            if ($credential['credential_type_id']) {
                $credentialsByType[$credential['class']][$credential['credential_id']] = $credential;
            }
        }

        return $credentialsByType;
    }
}
