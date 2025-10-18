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
 * Description of CredentialTypeLister.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialTypeLister extends CredentialType
{
    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'),
                'detailPage' => 'credentialtype.php', 'valueColumn' => 'credential_type.id', 'idColumn' => 'credential_type.id', ],
            ['name' => 'logo', 'type' => 'text', 'label' => _('Logo'), 'searchable' => false],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            ['name' => 'uuid', 'type' => 'text', 'label' => _('UUID')],
            ['name' => 'company_id', 'type' => 'text', 'label' => _('Company')],
        ]);
    }
    #[\Override]
    public function completeDataRow(array $dataRowRaw): array
    {
        $this->setData($dataRowRaw);
        $data = parent::completeDataRow($dataRowRaw);

        $helperClass = \get_class($this->getHelper() ?? new CredentialType\Common());

        if (empty($data['logo'])) {
            $data['logo'] = 'images/'.$helperClass::logo();
        }

        if (empty($data['name'])) {
            $data['name'] = $helperClass::name();
        }

        $data['logo'] = (string) new \Ease\Html\ATag('credentialtype.php?id='.$this->getMyKey(), new \Ease\Html\ImgTag($data['logo'], $data['name'], ['title' => $data['name'], 'style' => 'height: 50px;']));

        $data['company_id'] = (string) new Ui\CompanyLinkButton(new Company($dataRowRaw['company_id']), ['style' => 'height: 50px;']);

        return $data;
    }
}
