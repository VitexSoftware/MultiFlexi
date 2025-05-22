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
        // ->where('class', $this->class)
        $companyCredentials = $kredenc->listingQuery()->where('company_id', $this->company_id)->fetchAll('id');

        foreach ($companyCredentials as $credential) {
            $credentials[$credential['id']] = $credential['name'].' ⦉'.$credential['class'].'⦊';
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
