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
 *
 * @no-named-arguments
 */
class EnvironmentEditor extends \Ease\Html\TableTag
{
    public $fields;

    /**
     * @param array                 $fields
     * @param array<string, string> $properties
     */
    public function __construct(\MultiFlexi\ConfigFields $fields, array $properties = [])
    {
        $properties['class'] = 'table table-hover table-striped environment-editor-table';
        parent::__construct(null, $properties);
        $this->addRowHeaderColumns([
            '<i class="fas fa-key me-2"></i>'._('Keyword'),
            '<i class="fas fa-tag me-2"></i>'._('Value'),
            '',
        ]);
        $this->fields = $fields;

        foreach ($fields as $field) {
            $tableRow = new \Ease\Html\TrTag(null, ['class' => 'env-row']);

            // Create keyword cell with edit icon
            $keyInput = new \Ease\Html\ATag(
                '#',
                '<i class="fas fa-key me-2 text-muted small"></i>'.$field->getCode(),
                [
                    'class' => 'editable editable-click text-primary fw-semibold',
                    'id' => 'keyword',
                    'data-pk' => $field->getCode(),
                    'data-url' => 'companyenv.php',
                    'data-title' => _('Update Keyname'),
                    'data-emptytext' => _('Empty'),
                ],
            );
            $tableRow->addItem(new \Ease\Html\TdTag($keyInput, ['class' => 'align-middle', 'style' => 'width: 28%']));

            // Create value cell with edit icon
            $displayValue = $field->getValue();

            // Mask sensitive values
            if (stristr($field->getCode(), 'pass') || stristr($field->getCode(), 'secret') || stristr($field->getCode(), 'token')) {
                $displayValue = str_repeat('•', min(12, \strlen($displayValue)));
            }

            $valueInput = new \Ease\Html\ATag(
                '#',
                $displayValue.' <i class="fas fa-edit ms-2 text-muted small edit-hint"></i>',
                [
                    'class' => 'editable editable-click',
                    'id' => 'value',
                    'data-pk' => $field->getCode(),
                    'data-url' => 'companyenv.php',
                    'data-title' => _('Update value'),
                    'data-emptytext' => _('Empty'),
                ],
            );
            $tableRow->addItem(new \Ease\Html\TdTag($valueInput, ['class' => 'align-middle']));

            // Delete button
            $deleteBtn = new \Ease\Html\ATag(
                '#',
                '❌',
                [
                    'class' => 'btn-env-delete',
                    'data-keyword' => $field->getCode(),
                    'title' => _('Remove').' '.$field->getCode(),
                ],
            );
            $tableRow->addItem(new \Ease\Html\TdTag($deleteBtn, ['class' => 'align-middle text-center', 'style' => 'width: 3em']));

            $this->tBody->addItem($tableRow);
        }

        // Create modern form for adding new fields
        $newItemForm = new SecureForm(['class' => 'p-3']);
        $formRow = new \Ease\Html\DivTag(null, ['class' => 'row g-2 align-items-end']);

        // Keyword input
        $keyCol = new \Ease\Html\DivTag(null, ['class' => 'col-sm-4']);
        $keyCol->addItem(new \Ease\Html\LabelTag('env-newkey', '<i class="fas fa-key me-1"></i>'._('Keyword'), ['class' => 'form-label small text-muted mb-1']));
        $keyCol->addItem(new \Ease\Html\InputTextTag('env[newkey]', '', ['class' => 'form-control', 'id' => 'env-newkey', 'placeholder' => _('Enter keyword')]));
        $formRow->addItem($keyCol);

        // Value input
        $valueCol = new \Ease\Html\DivTag(null, ['class' => 'col-sm-5']);
        $valueCol->addItem(new \Ease\Html\LabelTag('env-newvalue', '<i class="fas fa-tag me-1"></i>'._('Value'), ['class' => 'form-label small text-muted mb-1']));
        $valueCol->addItem(new \Ease\Html\InputTextTag('env[newvalue]', '', ['class' => 'form-control', 'id' => 'env-newvalue', 'placeholder' => _('Enter value')]));
        $formRow->addItem($valueCol);

        // Submit button
        $btnCol = new \Ease\Html\DivTag(null, ['class' => 'col-sm-3']);
        $btnCol->addItem(new \Ease\Html\SpanTag('&nbsp;', ['class' => 'form-label small d-block mb-1']));
        $btnCol->addItem(new \Ease\TWB4\SubmitButton(
            '<i class="fas fa-plus-circle me-1"></i>'._('Add Field'),
            'success',
            ['title' => _('Add new environment field'), 'id' => 'addnewenvfieldbutton', 'class' => 'btn-block w-100'],
        ));
        $formRow->addItem($btnCol);

        $newItemForm->addItem($formRow);

        // Build footer manually with colspan to span full table width
        $footerRow = $this->getFoot()->addItem(new \Ease\Html\TrTag());
        $footerCell = new \Ease\Html\ThTag(null, ['colspan' => '3', 'class' => 'p-0']);

        $footerWrapper = new \Ease\Html\DivTag(null, ['class' => 'bg-light border-top']);
        $footerWrapper->addItem($newItemForm);
        $footerWrapper->addItem(new \Ease\Html\DivTag(
            new \Ease\Html\SmallTag(
                '<i class="fas fa-database me-1"></i>'.sprintf(_('%s items'), \count($this->fields)),
                ['class' => 'text-muted'],
            ),
            ['class' => 'text-end px-3 pb-2'],
        ));

        $footerCell->addItem($footerWrapper);
        $footerRow->addItem($footerCell);

        // Include required assets
        $this->includeJavaScript('js/bootstrap-editable.js');
        $this->includeCss('css/bootstrap-editable.css');

        // Add custom CSS for modern look
        $this->addCss(
            <<<'CSS'
.environment-editor-table {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}
.environment-editor-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.environment-editor-table thead th {
    border: none;
    padding: 1rem;
    font-weight: 600;
}
.env-row {
    transition: all 0.2s ease;
}
.env-row:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
}
.editable {
    text-decoration: none;
    border-bottom: 1px dashed #dee2e6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}
.editable:hover {
    background-color: #fff3cd;
    border-bottom-color: #ffc107;
}
.edit-hint {
    opacity: 0;
    transition: opacity 0.2s ease;
}
.env-row:hover .edit-hint {
    opacity: 1;
}
.editable-click {
    cursor: pointer;
}
.btn-env-delete {
    opacity: 0.3;
    text-decoration: none;
    cursor: pointer;
    transition: opacity 0.2s ease, transform 0.2s ease;
    font-size: 0.85rem;
}
.env-row:hover .btn-env-delete {
    opacity: 1;
}
.btn-env-delete:hover {
    transform: scale(1.3);
}
.environment-editor-table thead th:last-child {
    width: 3em;
}
CSS
        );

        // Configure editable with CSRF token
        $csrfToken = '';

        if (isset($GLOBALS['csrfProtection'])) {
            $csrfToken = $GLOBALS['csrfProtection']->generateToken();
        }

        $this->addJavaScript("$.fn.editable.defaults.mode = 'inline';");
        $this->addJavaScript(<<<'EOD'
      $.fn.editableform.buttons =
        '<button type="submit" class="btn btn-primary btn-sm editable-submit">' +
        '<i class="fas fa-check"></i>' +
        '</button>' +
        '<button type="button" class="btn btn-secondary btn-sm editable-cancel">' +
        '<i class="fas fa-times"></i>' +
        '</button>'

EOD);
        $this->addJavaScript(
            <<<JS
$('.editable').editable({
    params: function(params) {
        params.csrf_token = '{$csrfToken}';
        return params;
    }
});
$('.btn-env-delete').on('click', function(e) {
    e.preventDefault();
    var keyword = $(this).data('keyword');
    var row = $(this).closest('tr');
    if (confirm('⚠ ' + keyword + '?')) {
        $.post('companyenv.php', {
            action: 'delete',
            keyword: keyword,
            csrf_token: '{$csrfToken}'
        }).done(function() {
            row.fadeOut(300, function() { $(this).remove(); });
        }).fail(function() {
            alert('Error deleting ' + keyword);
        });
    }
});
JS,
            null,
            true,
        );
    }
    //    public function &addRowColumns($columns = null, $properties = [])
    //    {
    //        $key = [new InplaceInput($columns[0]), ' 🖉'];
    //        $value = [new InplaceInput($columns[1]), ' 🖉'];
    //        return parent::addRowColumns([$key, $value], $properties);
    //    }
}
