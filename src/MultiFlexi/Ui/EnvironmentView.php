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
 * Description of EnvironmentView.
 *
 * @author vitex
 */
class EnvironmentView extends \Ease\Html\TableTag
{
    /**
     * @param array $environment
     * @param array $properties
     */
    public function __construct($environment = null, $properties = [])
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        $this->addRowHeaderColumns([_('Name'), _('Value'), _('Source')]);
        asort($environment);

        foreach ($environment as $key => $envData) {
            if (\is_string($envData)) { // Fallback for Flat data
                $value = $envData;
                $envData = [];
                $envData['value'] = $value;
                $envData['type'] = '';
                $envData['source'] = _('n/a');
            }

            if (!\array_key_exists('value', $envData) && \array_key_exists('defval', $envData)) {
                $envData['value'] = new \Ease\TWB4\Badge('inverse', $envData['defval'], ['title' => _('Default Value')]);
            }

            if (\array_key_exists('type', $envData) && $envData['type'] === 'secret') {
                $envData['value'] = preg_replace('(.)', '*', $envData['value']);
            }

            //            if(empty($envData['value'])){
            // TODO                $envData['value'] = new \Ease\TWB4\Badge('danger',_('Required'));
            //            }

            $ns = \array_key_exists('source', $envData) ? explode('\\', $envData['source']) : ['n/a'];
            $this->addRowColumns([new \Ease\Html\SpanTag($key, ['title' => \array_key_exists('description', $envData) ? $envData['description'] : '']), $envData['value'], end($ns)]);
        }
    }
}
