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
 * Description of CredentialType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialType extends DBEngine
{
    /**
     * @var string Name Column
     */
    public string $nameColumn = 'name';

    public function __construct($init = null, $filter = [])
    {
        $this->myTable = 'credential_type';

        parent::__construct(false /* TODO $init */, $filter);
    }

    /**
     * Prepare data for save.
     */
    #[\Override]
    public function takeData(array $data): int
    {
        unset($data['class']);

        if (\array_key_exists('id', $data) && !is_numeric($data['id'])) {
            unset($data['id']);
        }

        if (\array_key_exists('uuid', $data) === false) {
            $data['uuid'] = \Ease\Functions::guidv4();
        }

        return parent::takeData($data);
    }

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
        ]);
    }
}
