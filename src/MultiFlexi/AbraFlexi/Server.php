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

namespace MultiFlexi\AbraFlexi;

/**
 * Description of Server.
 *
 * @author vitex
 */
class Server extends \MultiFlexi\Engine implements \MultiFlexi\platformServer
{
    /**
     * SQL Table we use.
     *
     * @param null|mixed $identifier
     * @param mixed      $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'servers';
        parent::__construct($identifier, $options);
    }

    /**
     * Connection Environment by Server.
     *
     * @return array
     */
    public function getEnvironment()
    {
        $connectionData = $this->getData();

        return [
            'ABRAFLEXI_URL' => $connectionData['url'],
            'ABRAFLEXI_LOGIN' => $connectionData['user'],
            'ABRAFLEXI_PASSWORD' => $connectionData['password'],
        ];
    }

    public function todo($param): void
    {
        $this->addInput(
            new SemaforLight((bool) $this->engine->getDataValue('rw')),
            _('write permission'),
        );
        $this->addItem(new InputHiddenTag('rw', false));
        $this->addInput(
            new SemaforLight((bool) $this->engine->getDataValue('setup')),
            _('Setup performed'),
        );
        $this->addItem(new InputHiddenTag('setup'), false);
        $this->addInput(
            new SemaforLight((bool) $this->engine->getDataValue('webhook')),
            _('WebHook established'),
        );
        $this->addItem(new InputHiddenTag('webhook'));
    }
}
