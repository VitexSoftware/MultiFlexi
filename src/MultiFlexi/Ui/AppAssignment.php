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

use Ease\Html\ATag;
use Ease\Html\DivTag;
use Ease\TWB4\Table;
use Ease\TWB4\Widgets\Toggle;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\CompanyApp;

/**
 * MultiFlexi - Application Launch Form.
 *
 * @copyright  2015-2026 Vitex Software
 * @license    https://opensource.org/licenses/MIT MIT
 *
 * @no-named-arguments
 */
class AppAssignment extends DivTag
{
    public function __construct(Application $application, $properties = [])
    {
        $companies = new Company();
        $allCompanies = $companies->listingQuery()->select(['id', 'name'], true)->orderBy('name')->fetchAll();
        $companyApp = new CompanyApp();
        $assignedTo = $companyApp->listingQuery()->where('app_id', $application->getMyKey())->fetchAll('company_id');

        $assignmentsTable = new Table(null, ['class' => 'table table-hover mb-0', 'id' => 'assignments-table']);
        $assignmentsTable->addRowHeaderColumns([_('Company'), _('Assigned')], ['class' => 'thead-light']);

        foreach ($allCompanies as $companyData) {
            $toggle = new Toggle(
                'assign['.$companyData['id'].']',
                \array_key_exists($companyData['id'], $assignedTo),
                (string) $companyData['id'],
                ['class' => 'company-assign-toggle', 'data-company-id' => $companyData['id'], 'data-app-id' => $application->getMyKey()],
            );
            $assignmentsTable->addRowColumns([
                new ATag('company.php?id='.$companyData['id'], $companyData['name']),
                $toggle,
            ]);
        }

        $csrfToken = $GLOBALS['csrfProtection']->generateToken();

        $card = new \Ease\TWB4\Card(
            new \Ease\Html\DivTag([
                new \Ease\Html\H4Tag(_('Company Assignments'), ['class' => 'card-title mb-0']),
                new \Ease\Html\SmallTag(_('Enable or disable this application for specific companies'), ['class' => 'text-muted']),
            ], ['class' => 'd-flex justify-content-between align-items-center mb-3']),
            ['class' => 'shadow-sm border-0 mt-4'],
        );

        $searchBox = new \Ease\Html\InputSearchTag('company_search', '', [
            'placeholder' => _('Search companies...'),
            'class' => 'form-control mb-3',
            'id' => 'company-search',
        ]);

        $cardBody = new \Ease\Html\DivTag([$searchBox, new \Ease\Html\DivTag($assignmentsTable, ['class' => 'table-responsive'])], ['class' => 'card-body']);
        $card->addItem($cardBody);

        WebPage::singleton()->addJavaScript(<<<'JS'
            $('#company-search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#assignments-table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
JS);

        WebPage::singleton()->addJavaScript(<<<JS
            $('.company-assign-toggle').change(function() {
                var toggle = $(this);
                var companyId = toggle.data('company-id');
                var appId = toggle.data('app-id');
                var state = toggle.prop('checked');

                $.post('togglecompanyapp.php', {
                    company_id: companyId,
                    app_id: appId,
                    state: state,
                    csrf_token: '{$csrfToken}'
                }, function(data) {
                    if (data.result === 'success') {
                        // Success
                    } else {
                        alert('Error updating assignment');
                        toggle.bootstrapToggle(state ? 'off' : 'on', true);
                    }
                }, 'json').fail(function() {
                    alert('Request failed');
                    toggle.bootstrapToggle(state ? 'off' : 'on', true);
                });
            });
JS);

        parent::__construct($card, $properties);
    }
}
