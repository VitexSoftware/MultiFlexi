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
 * Description of RunTplCreds.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RunTplCreds extends Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'runtplcreds';
        parent::__construct($identifier, $options);
    }

    public function getRuntemplatesForCredential($credentials_id)
    {
        return $this->listingQuery()->where(['credentials_id' => $credentials_id]);
    }

    public function getCredentialsForRuntemplate($runtemplates_id)
    {
        return $this->listingQuery()->select(['credentials.name', 'credentials.formType'])->where(['runtemplate_id' => $runtemplates_id])->leftJoin('credentials ON credentials.id=runtplcreds.credentials_id');
    }

    public function bind(int $runtemplate_id, int $credentials_id, string $reqType)
    {
        $this->unbindAll($runtemplate_id, $reqType);
        $this->insertToSQL(['runtemplate_id' => $runtemplate_id, 'credentials_id' => $credentials_id]);

        return true;
    }

    public function unbind(int $runtemplate_id, int $credentials_id)
    {
        return $this->deleteFromSQL(['runtemplate_id' => $runtemplate_id, 'credentials_id' => $credentials_id]);
    }

    public function unbindAll(int $runtemplate_id, string $reqType): void
    {
        $runtemplater = new RunTemplate($runtemplate_id);
        $kredenc = new Credential();
        $candidates = $kredenc->listingQuery()->leftJoin('credential_type ON credential_type.id = credentials.credential_type_id')->where(['credentials.company_id' => $runtemplater->getDataValue('company_id'), 'credential_type.class' => $reqType]);

        foreach ($candidates as $candidat) {
            $this->unbind($runtemplate_id, $candidat['id']);
        }
    }
}
