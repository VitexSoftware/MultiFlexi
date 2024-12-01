<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of RunTplCreds
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
class RunTplCreds extends Engine {

    public function __construct($identifier = null, $options = []) {
        $this->myTable = 'runtplcreds';
        parent::__construct($identifier, $options);
    }

    public function getRuntemplatesForCredential($credentials_id) {
        return $this->listingQuery()->where(['credentials_id'=>$credentials_id]);
    }

    public function getCredentialsForRuntemplate($runtemplates_id) {
        return $this->listingQuery()->where(['runtemplate_id'=>$runtemplates_id]);
    }
    
    public function bind(int $runtemplate_id, int $credentials_id) {
        $check = $this->listingQuery()->where(['runtemplate_id' => $runtemplate_id, 'credentials_id' => $credentials_id]);
        if($check->count() == 0){
            $this->insertToSQL(['runtemplate_id' => $runtemplate_id, 'credentials_id' => $credentials_id]);
        }
        return true;
    }

    public function unbind(int $runtemplate_id, int $credentials_id) {
        return $this->deleteFromSQL(['runtemplate_id' => $runtemplate_id, 'credentials_id' => $credentials_id]);
    }

    public function unbindAll(int $runtemplate_id, string $reqType) {
        $runtemplater = new RunTemplate($runtemplate_id);
        $kredenc = new Credential();
        $candidates = $kredenc->listingQuery()->where(['company_id'=>$runtemplater->getDataValue('company_id'),'formType'=>$reqType]);
        foreach ($candidates as $candidat){
            $this->unbind($runtemplate_id, $candidat['id']);
        }
    }
}
