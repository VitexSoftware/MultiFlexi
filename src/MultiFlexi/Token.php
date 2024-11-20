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
 * Description of Token.
 *
 * @author vitex
 */
class Token extends Engine
{
    /**
     * Summary of __construct.
     *
     * @param mixed $identifier
     * @param mixed $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->nameColumn = 'token';
        $this->myTable = 'token';
        $this->createColumn = 'start';
        parent::__construct($identifier, $options);
    }

    /**
     * @return \MultiFlexi\User
     */
    public function getUser()
    {
        return $this->getDataValue('user') ? new \MultiFlexi\User($this->getDataValue('user')) : null;
    }

    /**
     * Generate New Token.
     *
     * @return $this
     */
    public function generate()
    {
        $this->setDataValue($this->nameColumn, \Ease\Functions::randomString(20));

        return $this;
    }
}
