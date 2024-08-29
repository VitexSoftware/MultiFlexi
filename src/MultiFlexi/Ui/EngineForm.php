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

use Ease\Html\InputHiddenTag;
use Ease\TWB4\Form;
use MultiFlexi\Engine;

class EngineForm extends Form
{
    public ?Engine $engine = null;

    /**
     * Formulář Bootstrapu.
     *
     * @param Engine $engine        jméno formuláře
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                              array('enctype' => 'multipart/form-data')
     */
    public function __construct($engine, $formContents = null, $tagProperties = [])
    {
        $this->engine = $engine;
        $tagProperties['method'] = 'post';
        $tagProperties['name'] = \get_class($engine);
        parent::__construct($tagProperties, [], $formContents);
    }

    /**
     * Add Hidden ID & Class field.
     *
     * @return bool
     */
    public function finalize()
    {
        $recordID = $this->engine->getMyKey();
        $this->addItem(new InputHiddenTag('class', \get_class($this->engine)));

        if (null !== $recordID) {
            $this->addItem(new InputHiddenTag($this->engine->getKeyColumn(), $recordID));
        }

        $this->fillUp($this->engine->getData());

        return parent::finalize();
    }
}
