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
 * Description of Engine.
 *
 * @author vitex
 */
class Engine extends \Ease\SQL\Engine
{
    public $filter = [];

    /**
     * MultiFlexi Engine.
     *
     * @param mixed $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        if (\array_key_exists('autoload', $options) === false) {
            $options['autoload'] = true;
        }

        parent::__construct($identifier, $options);
        $this->setObjectName(\get_class($this));
    }

    /**
     * Set my key value and object name accordingly.
     *
     * @param mixed $key value of object indentifier to be set
     *
     * @return bool
     */
    public function setMyKey($key)
    {
        return parent::setMyKey($key) && $this->setObjectName();
    }

    /**
     * Save data.
     *
     * @param array $data
     *
     * @return int
     */
    public function saveToSQL($data = null)
    {
        if (null === $data) {
            $data = $this->getData();
        }

        unset($data['class']);

        if (\array_key_exists('app_id', $data) && \array_key_exists('company_id', $data)) {
            $found = $this->getColumnsFromSQL(['id'], ['app_id' => $data['app_id'], 'company_id' => $data['company_id']]);

            if ($found) {
                $data[$this->getKeyColumn()] = ((int) $found[0]['id']);
            }
        }

        return parent::saveToSQL($data);
    }
}
