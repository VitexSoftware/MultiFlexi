<?php
/**
 * Multi FlexiBee Setup  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

class ColumnsForm extends \Ease\TWB4\Form
{
    /**
     * Šířka sloupce.
     *
     * @var int
     */
    public $colsize = 4;

    /**
     * Řádek.
     *
     * @var \Ease\TWB4\Row
     */
    public $row = null;

    /**
     * Počet položek na řádek.
     *
     * @var int
     */
    public $itemsPerRow = 4;

    /**
     * @var SysEngine
     */
    public $engine = null;

    /**
     * Odesílací tlačítka.
     *
     * @var \Ease\Html\Div
     */
    public $savers = null;

    /**
     * Formulář Bootstrapu.
     *
     * @param SysEngine $engine        jméno formuláře
     * @param mixed     $formContents  prvky uvnitř formuláře
     * @param array     $tagProperties vlastnosti tagu například:
     *                                 array('enctype' => 'multipart/form-data')
     */
    public function __construct($engine, $formContents = null,
                                $tagProperties = null)
    {
        $this->engine = $engine;
        parent::__construct(
            get_class($engine), '', 'POST', $formContents, $tagProperties
        );
        $this->newRow();
        $this->savers = new \Ease\Html\DivTag(null,
            ['style' => 'text-align: right']);
    }

    /**
     * Přidá další řadu formuláře.
     *
     * @return \Ease\TWB4\Row Nově vložený řádek formuláře
     */
    public function newRow()
    {
        return $this->row = $this->addItem(new \Ease\TWB4\Row());
    }

    /**
     * Vloží prvek do sloupce formuláře.
     *
     * @param mixed  $input       Vstupní prvek
     * @param string $caption     Popisek
     * @param string $placeholder předvysvětlující text
     * @param string $helptext    Dodatečná nápověda
     * @param string $addTagClass CSS třída kterou má být oskiován vložený prvek
     */
    public function addInput($input, $caption = null, $placeholder = null,
                             $helptext = null, $addTagClass = 'form-control')
    {
        if ($this->row->getItemsCount() > $this->itemsPerRow) {
            $this->row = $this->addItem(new \Ease\TWB4\Row());
        }

        return $this->row->addItem(new \Ease\TWB4\Col($this->colsize,
                new \Ease\TWB4\FormGroup($caption, $input, $placeholder,
                $helptext, $addTagClass)));
    }

    /**
     * Přidá do formuláře tlačítko "Uložit".
     */
    public function addSubmitSave()
    {
        $this->savers->addItem(new EaseTWSubmitButton(_('Uložit'), 'default'),
            ['style' => 'text-align: right']);
    }

    /**
     * Přidá do formuláře tlačítko "Uložit a zpět na přehled".
     */
    public function addSubmitSaveAndList()
    {
        $this->savers->addItem(new \Ease\Html\InputSubmitTag('gotolist',
            _('Save and back'), ['class' => 'btn btn-info']));
    }

    /**
     * Add to form button  "Save next ext".
     */
    public function addSubmitSaveAndNext()
    {
        $this->savers->addItem(new \Ease\Html\InputSubmitTag('gotonew',
            _('Save and next'), ['class' => 'btn btn-success']));
    }

     public function finalize()
    {
        $recordID = $this->engine->getMyKey();
        $this->addItem(new \Ease\Html\InputHiddenTag('class',
            get_class($this->engine)));
        if (!is_null($recordID)) {
            $this->addItem(new \Ease\Html\InputHiddenTag($this->engine->keyColumn,
                $recordID));
        }
        $this->addItem($this->savers);
        \Ease\TWB4\WebPage::singleton()->includeJavaScript('js/jquery.validate.js');
        \Ease\TWB4\WebPage::singleton()->includeJavaScript('js/messages_cs.js');

        return parent::finalize();
    }
}