<?php
/**
 * Multi Flexi  - AbraFlexi server companys status
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AbraFlexiInstanceStatus
 *
 * @author vitex
 */
class AbraFlexiInstanceStatus extends \Ease\Html\TableTag
{

    public function __construct($servers, $properties = array())
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        $this->addRowHeaderColumns([_('Code'), _('Name'), _('Show'), _('State'), _('watching Changes'), '']);
        $companer = new \MultiFlexi\Company();
        $registered = $companer->getColumnsFromSQL(['id', 'company'], ['server' => $servers->getMyKey()], 'id', 'company');
        foreach ($this->companys($servers->getData()) as $companyData) {
            try {
                $setter = new \AbraFlexi\Nastaveni(1, array_merge($servers->getData(), ['company' => $companyData['dbNazev']]));
                $companyDetail = $setter->getData();
                $registerParams = [
                    'company' => $companyData['dbNazev'],
                    'nazev' => $companyData['nazev'],
                    'server' => $servers->getMyKey(),
                    'ic' => array_key_exists('ic', $companyDetail) ? $companyDetail['ic'] : '',
                    'email' => array_key_exists('email', $companyDetail) ? $companyDetail['email'] : '',
                ];
                unset($companyData['id']);
                unset($companyData['licenseGroup']);
                unset($companyData['createDt']);
                $companyData['action'] = array_key_exists($companyData['dbNazev'], $registered) ? new \Ease\TWB4\LinkButton('company.php?id=' . $registered[$companyData['dbNazev']]['id'], _('Edit'), 'success') : new \Ease\TWB4\LinkButton('companysetup.php?' . http_build_query($registerParams), _('Register'), 'warning');
                $this->addRowColumns($companyData);
            } catch (\AbraFlexi\Exception $exc) {
                $this->addStatusMessage($exc->getMessage());
            }
        }
    }

    /**
     * List Companys on target server
     * 
     * @param array $serverAccess
     * 
     * @return array
     */
    public function companys($serverAccess)
    {
        $companer = new \AbraFlexi\Company(null, $serverAccess);
        $companys = $companer->getAllFromAbraFlexi();
        return empty($companys) ? [] : $companys;
    }
}
