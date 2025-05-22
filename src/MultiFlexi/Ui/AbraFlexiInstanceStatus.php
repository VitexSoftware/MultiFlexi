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
 * Description of AbraFlexiInstanceStatus.
 *
 * @author vitex
 */
class AbraFlexiInstanceStatus extends \Ease\Html\TableTag
{
    public function __construct($servers, $properties = [])
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        $this->addRowHeaderColumns([_('Code'), _('Name'), _('Show'), _('State'), _('watching Changes'), '']);
        $companer = new \MultiFlexi\Company();
        $registered = $companer->getColumnsFromSQL(['id', 'company', 'name'], ['server' => $servers->getMyKey()], 'id', 'company');

        foreach ($this->companies($servers->getData()) as $companyData) {
            try {
                $setter = new \AbraFlexi\Nastaveni(1, array_merge($servers->getData(), ['company' => $companyData['dbNazev']]));
                $companyDetail = $setter->getData();
                $registerParams = [
                    'company' => $companyData['dbNazev'],
                    'name' => $companyData['nazev'],
                    'server' => $servers->getMyKey(),
                    'ic' => \array_key_exists('ic', $companyDetail) ? $companyDetail['ic'] : '',
                    'email' => \array_key_exists('email', $companyDetail) ? $companyDetail['email'] : '',
                ];
                unset($companyData['id'], $companyData['licenseGroup'], $companyData['createDt']);

                $companyData['action'] = \array_key_exists($companyData['dbNazev'], $registered) ? new \Ease\TWB5\LinkButton('company.php?id='.$registered[$companyData['dbNazev']]['id'], _('Edit'), 'success') : new \Ease\TWB5\LinkButton('companysetup.php?'.http_build_query($registerParams), _('Register'), 'warning');
                $this->addRowColumns($companyData);
            } catch (\AbraFlexi\Exception $exc) {
                $this->addStatusMessage($exc->getMessage());
            }
        }
    }

    /**
     * List companies on target server.
     *
     * @param array $serverAccess
     *
     * @return array
     */
    public function companies($serverAccess)
    {
        $companer = new \AbraFlexi\Company(null, $serverAccess);

        try {
            $companies = $companer->getAllFromAbraFlexi();
        } catch (\AbraFlexi\Exception $exc) {
            $companies = [];
        }

        return empty($companies) ? [] : $companies;
    }
}
