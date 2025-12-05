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
 * Description of CredentialSelect.F.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class CredentialTypeSelect extends \Ease\Html\SelectTag
{
    use \Ease\TWB4\Widgets\Selectizer;
    private int $company_id;

    public function __construct(string $name, int $company_id, ?int $selected = null, array $properties = [])
    {
        $this->company_id = $company_id;
        parent::__construct($name, [], (string) $selected, $properties);
    }

    /**
     * obtain credentials.
     *
     * @return array
     */
    public function loadItems()
    {
        $kredenc = new \MultiFlexi\CredentialType();
        $credentials = ['' => _('Do not use')];

        // Get PHP-based credential types (legacy system)
        $companyCredentials = $kredenc->listingQuery()->where('company_id', $this->company_id)->fetchAll('id');

        foreach ($companyCredentials as $credential) {
            $source = !empty($credential['class']) ? 'PHP' : 'JSON';
            $credentials[$credential['id']] = $credential['name'].' ⦉'.$source.'⦊';
        }

        // Get JSON-based credential types from credential_prototype table
        $credProto = new \MultiFlexi\CredentialProtoType();
        $jsonCredentials = $credProto->listingQuery()
            ->leftJoin('credential_type ON credential_type.uuid = credential_prototype.uuid')
            ->where('credential_type.company_id', $this->company_id)
            ->orWhere('credential_type.company_id IS NULL') // Global credential types
            ->select([
                'credential_type.id',
                'credential_prototype.name',
                'credential_prototype.code',
                'credential_prototype.uuid',
            ])
            ->fetchAll('id');

        foreach ($jsonCredentials as $jsonCred) {
            if (!isset($credentials[$jsonCred['id']])) { // Avoid duplicates
                $credentials[$jsonCred['id']] = $jsonCred['name'].' ⦉JSON⦊';
            }
        }

        ksort($credentials);
        $this->setData($credentials);

        return $credentials;
    }

    #[\Override]
    public function finalize(): void
    {
        $this->selectize();
        parent::finalize();
    }
}
