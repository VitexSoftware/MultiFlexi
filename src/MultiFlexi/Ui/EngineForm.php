<?php

/**
 * Multi Flexi  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\DivTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputSubmitTag;
use Ease\TWB4\Col;
use Ease\TWB4\Form;
use Ease\TWB4\FormGroup;
use Ease\TWB4\Row;
use MultiFlexi\Engine;

class EngineForm extends Form
{
    /**
     * @var SysEngine
     */
    public $engine = null;

    /**
     * Formulář Bootstrapu.
     *
     * @param Engine $engine        jméno formuláře
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                                 array('enctype' => 'multipart/form-data')
     */
    public function __construct($engine, $formContents = null, $tagProperties = [])
    {
        $this->engine = $engine;
        $tagProperties['method'] = 'post';
        $tagProperties['name'] = get_class($engine);
        parent::__construct($tagProperties, [], $formContents);
    }

    /**
     * Add Hidden ID & Class field
     *
     * @return boolean
     */
    public function finalize()
    {
        $recordID = $this->engine->getMyKey();
        $this->addItem(new InputHiddenTag('class', get_class($this->engine)));
        if (!is_null($recordID)) {
            $this->addItem(new InputHiddenTag($this->engine->getKeyColumn(), $recordID));
        }
        $this->fillUp($this->engine->getData());
        return parent::finalize();
    }
}
