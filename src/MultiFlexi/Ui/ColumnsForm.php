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

use Ease\Html\DivTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputSubmitTag;
use Ease\TWB4\Col;
use Ease\TWB4\Form;
use Ease\TWB4\FormGroup;
use Ease\TWB4\Row;
use Ease\TWB4\SubmitButton;
use MultiFlexi\Engine;

class ColumnsForm extends Form
{
    /**
     * Šířka sloupce.
     */
    public int $colsize = 4;

    /**
     * Řádek.
     */
    public Row $row;

    /**
     * Počet položek na řádek.
     */
    public int $itemsPerRow = 3;
    public SysEngine $engine;

    /**
     * Odesílací tlačítka.
     */
    public \Ease\Html\Div $savers;

    /**
     * Formulář Bootstrapu.
     *
     * @param Engine $engine        jméno formuláře
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                              array('enctype' => 'multipart/form-data')
     */
    public function __construct(
        $engine,
        $formContents = null,
        $tagProperties = []
    ) {
        $this->engine = $engine;
        $tagProperties['method'] = 'post';
        $tagProperties['name'] = \get_class($engine);
        parent::__construct($tagProperties, [], $formContents);
        $this->newRow();
        $this->savers = new DivTag(null,['style' => 'text-align: right'],);
    }

    /**
     * Přidá další řadu formuláře.
     *
     * @return Row Nově vložený řádek formuláře
     */
    public function newRow()
    {
        return $this->row = $this->addItem(new Row());
    }

    /**
     * Vloží prvek do sloupce formuláře.
     *
     * @param mixed  $input       Vstupní prvek
     * @param string $caption     Popisek
     * @param string $placeholder předvysvětlující text
     * @param string $helptext    Dodatečná nápověda
     * @param string $addTagClass CSS třída kterou má být oskiován vložený prvek
     *
     * @return Row New item
     */
    public function addInput(
        $input,
        $caption = null,
        $placeholder = null,
        $helptext = null,
        $addTagClass = 'form-control'
    ) {
        if ($this->row->getItemsCount() > $this->itemsPerRow) {
            $this->row = $this->addItem(new Row());
        }

        return $this->row->addItem(new Col(
            $this->colsize,
            new FormGroup(
                $caption,
                $input,
                $placeholder,
                $helptext,
                $addTagClass,
            ),
        ));
    }

    /**
     * Přidá do formuláře tlačítko "Uložit".
     */
    public function addSubmitSave(): void
    {
        $this->savers->addItem(
            new SubmitButton(_('Save'), 'default'),
            ['style' => 'text-align: right'],
        );
    }

    /**
     * Přidá do formuláře tlačítko "Uložit a zpět na přehled".
     */
    public function addSubmitSaveAndList(): void
    {
        $this->savers->addItem(new InputSubmitTag(
            'gotolist',
            _('Save and back'),
            ['class' => 'btn btn-info'],
        ));
    }

    /**
     * Add to form button  "Save next ext".
     */
    public function addSubmitSaveAndNext(): void
    {
        $this->savers->addItem(new InputSubmitTag(
            'gotonew',
            _('Save and next'),
            ['class' => 'btn btn-success'],
        ));
    }

    /**
     * Add Submit buttons.
     *
     * @return bool
     */
    public function finalize()
    {
        $recordID = $this->engine->getMyKey();
        $this->addItem(new InputHiddenTag(
            'class',
            \get_class($this->engine),
        ));

        if (null !== $recordID) {
            $this->addItem(new InputHiddenTag(
                $this->engine->getKeyColumn(),
                $recordID,
            ));
        }

        $this->newRow();
        $this->addItem($this->savers);

        return parent::finalize();
    }
}
