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
 * Description of CredentialSelect.
 *
 * @author vitex
 */
class CredentialSelect extends \Ease\Html\SelectTag
{
    use \Ease\Html\Widgets\Selectizer;
    private int $company_id;
    private string $requirement;

    public function __construct(string $name, int $company_id, string $requirement, string $selected = '', array $properties = [])
    {
        $this->company_id = $company_id;
        $this->requirement = $requirement;
        parent::__construct($name, [], $selected, $properties);
    }

    /**
     * obtain credentials.
     *
     * @return array
     */
    public function loadItems()
    {
        $kredenc = new \MultiFlexi\Credential();

        $credentials = ['' => _('Do not use')];

        $companyCredentials = $kredenc->listingQuery()->where('company_id', $this->company_id)->where('formType', $this->requirement)->fetchAll('id');

        foreach ($companyCredentials as $credential) {
            $credentials[$credential['id']] = $credential['name'];
        }

        return $credentials;
    }

    #[\Override]
    public function finalize(): void
    {
        $this->selectize();
        parent::finalize();
    }
}
