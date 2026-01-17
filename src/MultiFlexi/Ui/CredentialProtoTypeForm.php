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
 * Class CredentialProtoTypeForm.
 *
 * Handles the form for editing CredentialProtoType.
 *
 * @no-named-arguments
 */
class CredentialProtoTypeForm extends SecureForm
{
    public \MultiFlexi\CredentialProtoType $prototype;

    public function __construct(\MultiFlexi\CredentialProtoType $prototype, $formProperties = [])
    {
        $this->prototype = $prototype;

        // Main prototype information
        $prototypeRow1 = new \Ease\TWB4\Row(null, ['class' => 'border border-secondary rounded p-3']);
        $prototypeRow1->addItem(new \Ease\Html\H4Tag(_('Prototype Information')));
        $prototypeRow1->addColumn(6, [
            new \Ease\TWB4\FormGroup(_('Code'), new \Ease\Html\InputTextTag('code', $prototype->getDataValue('code'), ['required'])),
            new \Ease\TWB4\FormGroup(_('Prototype Name'), new \Ease\Html\InputTextTag('name', $prototype->getDataValue('name'), ['required'])),
            new \Ease\TWB4\FormGroup(_('Version'), new \Ease\Html\InputTextTag('version', $prototype->getDataValue('version'))),
        ]);

        $prototypeRow1->addColumn(6, [
            new \Ease\TWB4\FormGroup(_('URL'), new \Ease\Html\InputTextTag('url', $prototype->getDataValue('url'))),
            new \Ease\TWB4\FormGroup(_('Logo'), new \Ease\Html\InputTextTag('logo', $prototype->getDataValue('logo'))),
            new \Ease\TWB4\FormGroup(_('UUID'), new \Ease\Html\InputTextTag('uuid', $prototype->getDataValue('uuid'), ['readonly'])),
        ]);

        // Description section
        $prototypeRow2 = new \Ease\TWB4\Row(null, ['class' => 'border border-secondary rounded p-3 mt-3']);
        $prototypeRow2->addItem(new \Ease\Html\H4Tag(_('Description')));
        $prototypeRow2->addColumn(12, [
            new \Ease\TWB4\FormGroup(_('Description'), new \Ease\Html\TextareaTag('description', $prototype->getDataValue('description'), ['rows' => 4])),
        ]);

        // Metadata section
        if ($prototype->getMyKey()) {
            $metadataRow = new \Ease\TWB4\Row(null, ['class' => 'border border-secondary rounded p-3 mt-3']);
            $metadataRow->addItem(new \Ease\Html\H4Tag(_('Metadata')));
            $metadataRow->addColumn(6, [
                new \Ease\TWB4\FormGroup(_('Created'), new \Ease\Html\InputTextTag('', $prototype->getDataValue('created_at'), ['readonly'])),
            ]);
            $metadataRow->addColumn(6, [
                new \Ease\TWB4\FormGroup(_('Updated'), new \Ease\Html\InputTextTag('', $prototype->getDataValue('updated_at'), ['readonly'])),
            ]);
        }

        $formContents[] = $prototypeRow1;
        $formContents[] = $prototypeRow2;

        if ($prototype->getMyKey() && isset($metadataRow)) {
            $formContents[] = $metadataRow;
        }

        // Localization section (available for both new and existing prototypes)
        $formContents[] = new \Ease\Html\H3Tag(_('Localization'));
        $formContents[] = new \Ease\Html\PTag(_('Provide translations for different languages.'));

        $supportedLanguages = [
            'en' => _('English'),
            'cs' => _('Czech'),
            'sk' => _('Slovak'),
            'de' => _('German'),
            'fr' => _('French'),
            'es' => _('Spanish'),
            'it' => _('Italian'),
            'pl' => _('Polish'),
            'nl' => _('Dutch'),
            'pt' => _('Portuguese'),
            'sv' => _('Swedish'),
            'fi' => _('Finnish'),
            'da' => _('Danish'),
            'no' => _('Norwegian'),
            'hu' => _('Hungarian'),
            'ro' => _('Romanian'),
            'bg' => _('Bulgarian'),
            'el' => _('Greek'),
            'tr' => _('Turkish'),
            'hr' => _('Croatian'),
            'sl' => _('Slovenian'),
            'et' => _('Estonian'),
            'lt' => _('Lithuanian'),
            'lv' => _('Latvian'),
            'ru' => _('Russian'),
            'uk' => _('Ukrainian'),
            'ja' => _('Japanese'),
            'zh' => _('Chinese'),
            'ko' => _('Korean'),
            'ar' => _('Arabic'),
        ];

        // Language controls - dropdown to show/hide language sections
        $langControlDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-3']);
        $langControlDiv->addItem(new \Ease\Html\LabelTag(_('Add language translation:').' ', null, ['for' => 'languageSelector']));

        // Initialize dropdown options - will be populated in loop below
        $languageOptions = ['' => _('-- Select language to add --')];

        $formContents[] = $langControlDiv;
        // Note: langSelect will be created after loop when languageOptions is complete

        $localizationContainer = new \Ease\Html\DivTag(null, ['id' => 'localizationContainer']);

        // Determine current UI language to show by default
        $currentLang = 'en';

        if (class_exists('Locale')) {
            $loc = \Locale::getDefault();

            if (!empty($loc)) {
                $currentLang = substr($loc, 0, 2);
            }
        } elseif (getenv('LANG')) {
            $langEnv = getenv('LANG');

            if (!empty($langEnv)) {
                $currentLang = substr($langEnv, 0, 2);
            }
        }

        // Fallback if current language not supported
        if (!\array_key_exists($currentLang, $supportedLanguages)) {
            $currentLang = 'en';
        }

        // Generate language sections - show current language, hide others
        foreach ($supportedLanguages as $langCode => $langName) {
            // Add option to dropdown (skip current language)
            if ($langCode !== $currentLang) {
                $languageOptions[$langCode] = $langName;
            }

            $langDiv = new \Ease\Html\DivTag(null, [
                // Show current language by default, hide others via hidden class
                'class' => 'language-section border rounded p-3'.($langCode === $currentLang ? '' : ' hidden'),
                'id' => 'lang-'.$langCode,
            ]);

            $langHeader = new \Ease\Html\DivTag(null, ['class' => 'd-flex justify-content-between align-items-center mb-2']);
            $langHeader->addItem(new \Ease\Html\H5Tag($langName.' ('.$langCode.')', ['class' => 'mb-0']));

            // Always add close button for non-current languages
            if ($langCode !== $currentLang) {
                $langHeader->addItem(new \Ease\TWB4\LinkButton('#', '×', 'outline-secondary btn-sm', [
                    'onclick' => 'hideLanguage(\''.$langCode.'\'); return false;',
                    'title' => _('Hide this language'),
                ]));
            } else {
                // Add indicator for current language
                $langHeader->addItem(new \Ease\Html\SpanTag(_('(Current)'), ['class' => 'badge badge-primary']));
            }

            $langDiv->addItem($langHeader);

            $langRow = new \Ease\TWB4\Row();
            $langRow->addColumn(6, [
                new \Ease\TWB4\FormGroup(_('Name'), new \Ease\Html\InputTextTag('localized[name]['.$langCode.']', '', ['id' => 'name_'.$langCode])),
            ]);

            $langRow->addColumn(6, [
                new \Ease\TWB4\FormGroup(_('Description'), new \Ease\Html\TextareaTag('localized[description]['.$langCode.']', '', ['rows' => 2, 'id' => 'description_'.$langCode])),
            ]);

            $langDiv->addItem($langRow);
            $localizationContainer->addItem($langDiv);

            // keep all hidden by default
        }

        // Create and add the dropdown now that languageOptions is populated
        $langSelect = new \Ease\Html\SelectTag('language_selector', $languageOptions);
        $langSelect->setTagProperty('id', 'languageSelector');
        $langSelect->setTagProperty('class', 'form-control');
        $langSelect->setTagProperty('style', 'width: auto; display: inline-block;');
        $langSelect->setTagProperty('onchange', 'showSelectedLanguage(this.value)');
        $langControlDiv->addItem($langSelect);

        $formContents[] = $localizationContainer;

        // Add JavaScript for language dropdown functionality
        $jsCode = <<<'JS'
(function(){
    function setHidden(el, hidden){
        if (!el) return;
        if (hidden) { el.classList.add('hidden'); }
        else { el.classList.remove('hidden'); }
    }
    function qsAll(selector){ return document.querySelectorAll(selector); }

    function updateControls(){
        var hiddenCount = 0; var total = 0;
        var langs = qsAll('.language-section');
        total = langs.length;
        langs.forEach(function(l){ if (l.classList.contains('hidden')) hiddenCount++; });
        var showBtn = document.getElementById('showAllLangs');
        var hideBtn = document.getElementById('hideAllLangs');
        if (!showBtn || !hideBtn) return;
        if (hiddenCount === total){ showBtn.style.display = 'inline-block'; hideBtn.style.display = 'none'; }
        else if (hiddenCount === 0){ showBtn.style.display = 'none'; hideBtn.style.display = 'inline-block'; }
        else { showBtn.style.display = 'inline-block'; hideBtn.style.display = 'inline-block'; }
    }

    window.showSelectedLanguage = function(langCode){
        if (!langCode) return; // Empty selection

        var el = document.getElementById('lang-' + langCode);
        if (!el || !el.classList.contains('hidden')) return;

        // Show the selected language section
        el.classList.remove('hidden');

        // Reset dropdown to default
        var select = document.getElementById('languageSelector');
        if (select) select.value = '';
    };

    // Hide language section
    window.hideLanguage = function(langCode){
        var el = document.getElementById('lang-' + langCode);
        if (!el) return;
        el.classList.add('hidden');
    };

    // Show new field language section
    window.showNewFieldLanguage = function(langCode){
        if (!langCode) return; // Empty selection

        var el = document.getElementById('new-field-lang-' + langCode);
        if (!el || !el.classList.contains('hidden')) return;

        // Show the selected language section
        el.classList.remove('hidden');

        // Reset dropdown to default
        var select = document.getElementById('newFieldLanguageSelector');
        if (select) select.value = '';
    };

    // Hide new field language section
    window.hideNewFieldLanguage = function(langCode){
        var el = document.getElementById('new-field-lang-' + langCode);
        if (!el) return;
        el.classList.add('hidden');
    };

    // Show existing field language section
    window.showFieldLanguage = function(fieldId, langCode){
        if (!langCode) return; // Empty selection

        var el = document.getElementById('field-' + fieldId + '-lang-' + langCode);
        if (!el || !el.classList.contains('hidden')) return;

        // Show the selected language section
        el.classList.remove('hidden');

        // Reset dropdown to default
        var select = document.getElementById('fieldLanguageSelector' + fieldId);
        if (select) select.value = '';
    };

    // Hide existing field language section
    window.hideFieldLanguage = function(fieldId, langCode){
        var el = document.getElementById('field-' + fieldId + '-lang-' + langCode);
        if (!el) return;
        el.classList.add('hidden');
    };
})();
JS;
        \Ease\WebPage::singleton()->addJavaScript($jsCode, null, false);

        // Fields section - for managing credential prototype fields
        $formContents[] = new \Ease\Html\H3Tag(_('Credential Fields'));
        $formContents[] = new \Ease\Html\PTag(_('Define the fields required for credentials of this type.'));

        // Display existing fields (only for existing prototypes)
        if ($prototype->getMyKey()) {
            $fielder = new \MultiFlexi\CredentialProtoTypeField();
            $existingFields = $fielder->listFields($prototype->getMyKey());

            if (!empty($existingFields)) {
                foreach ($existingFields as $field) {
                    // Reduce spacing to keep form compact and avoid visible gaps
                    $fieldRow = new \Ease\TWB4\Row(null, ['class' => 'border border-secondary rounded p-3 mt-3']);
                    $fieldRow->addItem(new \Ease\Html\H5Tag(_('Field').': '.$field['keyword']));

                    $fieldRow->addColumn(4, [
                        new \Ease\TWB4\FormGroup(_('Field Keyword'), new \Ease\Html\InputTextTag($field['id'].'[keyword]', $field['keyword'])),
                        new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag($field['id'].'[name]', $field['name'])),
                    ]);

                    $fieldTypeOptions = [
                        'string' => _('String'),
                        'password' => _('Password'),
                        'number' => _('Number'),
                        'boolean' => _('Boolean'),
                        'secret' => _('Secret'),
                        'select' => _('Select'),
                        'filepath' => _('File Path'),
                    ];

                    $fieldRow->addColumn(4, [
                        new \Ease\TWB4\FormGroup(_('Field Type'), new \Ease\Html\SelectTag($field['id'].'[type]', $fieldTypeOptions, $field['type'])),
                        new \Ease\TWB4\FormGroup(_('Default Value'), new \Ease\Html\InputTextTag($field['id'].'[default_value]', $field['default_value'])),
                    ]);

                    $fieldRow->addColumn(4, [
                        new \Ease\TWB4\FormGroup(_('Required'), new \Ease\Html\InputTag($field['id'].'[required]', '1', ['type' => 'checkbox', 'checked' => (bool) $field['required']])),
                        new \Ease\TWB4\FormGroup(_('Description'), new \Ease\Html\TextareaTag($field['id'].'[description]', $field['description'], ['rows' => 2])),
                        new \Ease\TWB4\FormGroup(_('Hint'), new \Ease\Html\InputTextTag($field['id'].'[hint]', $field['hint'])),
                    ]);

                    // Add localization section for existing field
                    $fieldLocalizationDiv = new \Ease\Html\DivTag(null, ['class' => 'mt-3']);
                    $fieldLocalizationDiv->addItem(new \Ease\Html\H5Tag(_('Field Localization'), ['class' => 'h6']));

                    // Language dropdown for this field
                    $fieldLangControlDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-3']);
                    $fieldLangControlDiv->addItem(new \Ease\Html\LabelTag(_('Add field translation:').' ', null, ['for' => 'fieldLanguageSelector'.$field['id']]));

                    $fieldLangSelect = new \Ease\Html\SelectTag('field_'.$field['id'].'_language_selector', $languageOptions);
                    $fieldLangSelect->setTagProperty('id', 'fieldLanguageSelector'.$field['id']);
                    $fieldLangSelect->setTagProperty('class', 'form-control');
                    $fieldLangSelect->setTagProperty('style', 'width: auto; display: inline-block;');
                    $fieldLangSelect->setTagProperty('onchange', 'showFieldLanguage('.$field['id'].', this.value)');
                    $fieldLangControlDiv->addItem($fieldLangSelect);
                    $fieldLocalizationDiv->addItem($fieldLangControlDiv);

                    $fieldLocalizationContainer = new \Ease\Html\DivTag(null, ['id' => 'fieldLocalizationContainer'.$field['id']]);

                    // Generate language sections for this field
                    foreach ($supportedLanguages as $langCode => $langName) {
                        $fieldLangDiv = new \Ease\Html\DivTag(null, [
                            'class' => 'field-language-section border rounded p-3 hidden',
                            'id' => 'field-'.$field['id'].'-lang-'.$langCode,
                        ]);

                        $fieldLangHeader = new \Ease\Html\DivTag(null, ['class' => 'd-flex justify-content-between align-items-center mb-2']);
                        $fieldLangHeader->addItem(new \Ease\Html\H5Tag($langName.' ('.$langCode.')', ['class' => 'mb-0 h6']));
                        $fieldLangHeader->addItem(new \Ease\TWB4\LinkButton('#', '×', 'outline-secondary btn-sm', [
                            'onclick' => 'hideFieldLanguage('.$field['id'].', \''.$langCode.'\'); return false;',
                            'title' => _('Hide this language'),
                        ]));
                        $fieldLangDiv->addItem($fieldLangHeader);

                        $fieldLangRow = new \Ease\TWB4\Row();
                        $fieldLangRow->addColumn(6, [
                            new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag($field['id'].'[localized_name]['.$langCode.']', '', ['id' => 'field_'.$field['id'].'_name_'.$langCode])),
                        ]);

                        $fieldLangRow->addColumn(6, [
                            new \Ease\TWB4\FormGroup(_('Field Description'), new \Ease\Html\TextareaTag($field['id'].'[localized_description]['.$langCode.']', '', ['rows' => 2, 'id' => 'field_'.$field['id'].'_description_'.$langCode])),
                        ]);

                        $fieldLangDiv->addItem($fieldLangRow);
                        $fieldLocalizationContainer->addItem($fieldLangDiv);
                    }

                    $fieldLocalizationDiv->addItem($fieldLocalizationContainer);
                    $fieldRow->addColumn(12, $fieldLocalizationDiv);

                    // Add delete button
                    $deleteButton = new \Ease\TWB4\LinkButton(
                        'credentialprototype.php?id='.$prototype->getMyKey().'&removefield='.$field['id'],
                        '🗑️ '._('Delete Field'),
                        'danger btn-sm',
                        ['onclick' => 'return confirm(\''._('Really delete this field?').'\');'],
                    );
                    $fieldRow->addColumn(12, new \Ease\Html\DivTag($deleteButton, ['class' => 'text-right']));

                    $formContents[] = $fieldRow;
                }
            }
        }

        // Add new field form (available for both new and existing prototypes)
        $newFieldRow = new \Ease\TWB4\Row(null, ['class' => 'border border-secondary rounded p-3 mt-3']);
        $newFieldRow->addItem(new \Ease\Html\H4Tag(_('Add New Field')));

        $newFieldRow->addColumn(4, [
            new \Ease\TWB4\FormGroup(_('Field Keyword'), new \Ease\Html\InputTextTag('new_field[keyword]', '')),
            new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag('new_field[name]', '')),
        ]);

        $fieldTypeOptions = [
            '' => _('Select type'),
            'string' => _('String'),
            'password' => _('Password'),
            'number' => _('Number'),
            'boolean' => _('Boolean'),
            'secret' => _('Secret'),
            'select' => _('Select'),
        ];

        $newFieldRow->addColumn(4, [
            new \Ease\TWB4\FormGroup(_('Field Type'), new \Ease\Html\SelectTag('new_field[type]', $fieldTypeOptions)),
            new \Ease\TWB4\FormGroup(_('Default Value'), new \Ease\Html\InputTextTag('new_field[default_value]', '')),
        ]);

        $newFieldRow->addColumn(4, [
            new \Ease\TWB4\FormGroup(_('Required'), new \Ease\Html\InputTag('new_field[required]', '1', ['type' => 'checkbox'])),
            new \Ease\TWB4\FormGroup(_('Description'), new \Ease\Html\TextareaTag('new_field[description]', '', ['rows' => 2])),
            new \Ease\TWB4\FormGroup(_('Hint'), new \Ease\Html\InputTextTag('new_field[hint]', '')),
        ]);

        $formContents[] = $newFieldRow;

        // Add localization section for new field
        $newFieldLocalizationDiv = new \Ease\Html\DivTag(null, ['class' => 'mt-3']);
        $newFieldLocalizationDiv->addItem(new \Ease\Html\H5Tag(_('Field Localization')));
        $newFieldLocalizationDiv->addItem(new \Ease\Html\PTag(_('Provide translations for field name and description.')));

        // Language dropdown for new field
        $newFieldLangControlDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-3']);
        $newFieldLangControlDiv->addItem(new \Ease\Html\LabelTag(_('Add field translation:').' ', null, ['for' => 'newFieldLanguageSelector']));

        $newFieldLangSelect = new \Ease\Html\SelectTag('new_field_language_selector', $languageOptions);
        $newFieldLangSelect->setTagProperty('id', 'newFieldLanguageSelector');
        $newFieldLangSelect->setTagProperty('class', 'form-control');
        $newFieldLangSelect->setTagProperty('style', 'width: auto; display: inline-block;');
        $newFieldLangSelect->setTagProperty('onchange', 'showNewFieldLanguage(this.value)');
        $newFieldLangControlDiv->addItem($newFieldLangSelect);
        $newFieldLocalizationDiv->addItem($newFieldLangControlDiv);

        $newFieldLocalizationContainer = new \Ease\Html\DivTag(null, ['id' => 'newFieldLocalizationContainer']);

        // Generate language sections for new field
        foreach ($supportedLanguages as $langCode => $langName) {
            $fieldLangDiv = new \Ease\Html\DivTag(null, [
                'class' => 'field-language-section border rounded p-3 hidden',
                'id' => 'new-field-lang-'.$langCode,
            ]);

            $fieldLangHeader = new \Ease\Html\DivTag(null, ['class' => 'd-flex justify-content-between align-items-center mb-2']);
            $fieldLangHeader->addItem(new \Ease\Html\H5Tag($langName.' ('.$langCode.')', ['class' => 'mb-0 h6']));
            $fieldLangHeader->addItem(new \Ease\TWB4\LinkButton('#', '×', 'outline-secondary btn-sm', [
                'onclick' => 'hideNewFieldLanguage(\''.$langCode.'\'); return false;',
                'title' => _('Hide this language'),
            ]));
            $fieldLangDiv->addItem($fieldLangHeader);

            $fieldLangRow = new \Ease\TWB4\Row();
            $fieldLangRow->addColumn(6, [
                new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag('new_field[localized_name]['.$langCode.']', '', ['id' => 'new_field_name_'.$langCode])),
            ]);

            $fieldLangRow->addColumn(6, [
                new \Ease\TWB4\FormGroup(_('Field Description'), new \Ease\Html\TextareaTag('new_field[localized_description]['.$langCode.']', '', ['rows' => 2, 'id' => 'new_field_description_'.$langCode])),
            ]);

            $fieldLangDiv->addItem($fieldLangRow);
            $newFieldLocalizationContainer->addItem($fieldLangDiv);
        }

        $newFieldLocalizationDiv->addItem($newFieldLocalizationContainer);
        $formContents[] = $newFieldLocalizationDiv;

        parent::__construct(['action' => 'credentialprototype.php'], ['method' => 'POST'], $formContents);

        // Add CSS for hidden utility class
        \Ease\WebPage::singleton()->addCSS('.hidden{display:none !important;} .language-section{margin-bottom:0;} .field-language-section{margin-bottom:10px;}');

        $submitRow = new \Ease\TWB4\Row();

        if (null === $prototype->getMyKey()) {
            $submitRow->addColumn(6, new \Ease\TWB4\SubmitButton(_('Create Prototype'), 'success btn-lg btn-block', ['name' => 'create', 'title' => _('Create new credential prototype'), 'id' => 'createprototypebutton']));
            $submitRow->addColumn(6, new \Ease\TWB4\LinkButton('credentialprototypes.php', _('Cancel'), 'secondary btn-lg btn-block', ['title' => _('Cancel creating new prototype'), 'id' => 'cancelprototypebutton']));
        } else {
            $submitRow->addColumn(3, new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block', ['name' => 'save']));
            $submitRow->addColumn(3, new \Ease\TWB4\LinkButton('credentialprototype.php', _('New Prototype'), 'info btn-lg btn-block'));
            $submitRow->addColumn(3, new \Ease\TWB4\LinkButton('credentialprototypes.php', _('Prototype List'), 'warning btn-lg btn-block'));
            $submitRow->addColumn(3, new \Ease\TWB4\LinkButton(
                'credentialprototype.php?delete='.$prototype->getMyKey(),
                _('Delete'),
                'danger btn-lg btn-block',
                ['onclick' => 'return confirm(\''._('Really delete credential prototype?').'\');'],
            ));
        }

        $this->addItem($submitRow);

        if ($prototype->getMyKey()) {
            $this->addItem(new \Ease\Html\InputHiddenTag('id', (string) $prototype->getMyKey()));
        }
    }
}
