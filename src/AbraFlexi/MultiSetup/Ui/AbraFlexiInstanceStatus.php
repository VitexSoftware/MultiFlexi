<?php

/**
 * Multi AbraFlexi Setup  - AbraFlexi server companys status
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of AbraFlexiInstanceStatus
 *
 * @author vitex
 */
class AbraFlexiInstanceStatus extends \Ease\Html\TableTag {

    public function __construct($abraflexis, $properties = array()) {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);

        $this->addRowHeaderColumns([_('Code'), _('Name'), _('Show'), _('State'), _('watching Changes'), '']);

        $companer = new \AbraFlexi\MultiSetup\Company();
        $registered = $companer->getColumnsFromSQL(['id', 'company'], ['abraflexi' => $abraflexis->getMyKey()], 'id', 'company');

        foreach ($this->companys($abraflexis->getData()) as $companyData) {
            $setter = new \AbraFlexi\Nastaveni(1, array_merge($abraflexis->getData(), ['company' => $companyData['dbNazev']]));
            $companyDetail = $setter->getData();

            $registerParams = [
                'company' => $companyData['dbNazev'],
                'nazev' => $companyData['nazev'],
                'abraflexi' => $abraflexis->getMyKey(),
                'ic' => array_key_exists('ic', $companyDetail) ? $companyDetail['ic'] : '',
                'email' => array_key_exists('email', $companyDetail) ? $companyDetail['email'] : '',
            ];

            unset($companyData['id']);
            unset($companyData['licenseGroup']);
            unset($companyData['createDt']);

            $companyData['action'] = array_key_exists($companyData['dbNazev'], $registered) ? new \Ease\TWB4\LinkButton('company.php?id=' . $registered[$companyData['dbNazev']]['id'], _('Edit'), 'success') : new \Ease\TWB4\LinkButton('company.php?' . http_build_query($registerParams), _('Register'), 'warning');
            $this->addRowColumns($companyData);
        }
    }

    public function companys($serverAccess) {
        $companer = new \AbraFlexi\Company(null, $serverAccess);
        $companys = $companer->getAllFromAbraFlexi();
        return empty($companys) ? [] : $companys;
    }

}
