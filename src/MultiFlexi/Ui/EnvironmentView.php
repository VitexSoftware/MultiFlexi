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
     * @param array<string, string> $properties
     */
    public function __construct(\MultiFlexi\ConfigFields $environment, array $properties = [])
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        $this->addRowHeaderColumns([_('Name'), _('Value'), _('Source')]);

        foreach ($environment as $key => $field) {
            $this->addRowColumns([new \Ease\Html\SpanTag($key, ['title' => $field->getDescription()]), $field->getValue(), $field->getSource()]);
        }
    }
    
    public function functionName($param) {
            if ($field->isSecret()) {
                $envData['value'] = preg_replace('(.)', '*', $envData['value']);
            } else {
                $envData['value'] = new \Ease\TWB4\Badge('inverse', $field->getDefaultValue(), ['title' => _('Default Value')]);
            }

            //            if(empty($envData['value'])){
            // TODO                $envData['value'] = new \Ease\TWB4\Badge('danger',_('Required'));
            //            }

            if (\array_key_exists('credential_id', $envData)) {
                $source = new \Ease\Html\DivTag(new \Ease\Html\ATag('credential.php?id='.$envData['credential_id'], $envData['source']));
                $credTyper = new \MultiFlexi\CredentialType($envData['credential_type']);

                if ($credTyper->getDataValue('logo')) {
                    $credTyper->addStatusMessage(sprintf(_('There is no Logo defined for %s Credential Types'), $envData['credential_type']), 'warning');
                }

                $source->addItem(new \Ease\Html\ImgTag((string) $credTyper->getDataValue('logo'), $envData['credential_type'], ['title' => $credTyper->getRecordName(), 'height' => '30', 'align' => 'right']));
            } else {
                $ns = \array_key_exists('source', $envData) ? explode('\\', $envData['source']) : ['n/a'];
                $source = end($ns);
            }

        
    }
    
    
}
