<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of RunTemplateLister
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
class RunTemplateLister extends MultiFlexi\RunTemplate {
   public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID')],
            ['name' => 'active', 'type' => 'boolean', 'label' => _('Active')],
            ['name' => 'interv', 'type' => 'text', 'label' => _('Interval')],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            //                    ['name' => 'resolved', 'type' => 'datetime', 'label' => _('Resolved')],
            ['name' => 'app_id', 'type' => 'selectize', 'label' => _('Application'),
                'listingPage' => 'apps.php',
                'detailPage' => 'app.php',
                //                'idColumn' => 'apps.id',
                //                'valueColumn' => 'apps.name',
                'engine' => '\MultiFlexi\Application',
                'filterby' => 'name',
            ],
            ['name' => 'company_id', 'type' => 'selectize', 'label' => _('Company'),
                'listingPage' => 'companies.php',
                'detailPage' => 'company.php',
                //                'idColumn' => 'company',
                //                'valueColumn' => 'company.name',
                'engine' => '\MultiFlexi\Company',
                'filterby' => 'name',
            ],
            ['name' => 'delay', 'type' => 'text', 'label' => _('Delay')],
            ['name' => 'executor', 'type' => 'text', 'label' => _('Executor')],
            //            ['name' => 'created', 'type' => 'datetime', 'label' => _('Created')],
        ]);
    }
    
    
    public function completeDataRow(array $dataRowRaw): array
    {
        $dataRowRaw['interv'] = '<span title="'._(self::codeToInterval($dataRowRaw['interv'])).'">'.self::getIntervalEmoji($dataRowRaw['interv']).' '._(self::codeToInterval($dataRowRaw['interv'])).'</span>';
        $dataRowRaw['active'] = (string) $dataRowRaw['active'] ? '&nbsp;<a href="schedule.php?id='.$dataRowRaw['id'].'&when=now&executor=Native" title="'._('Launch now').'"><span style="color: green; font-weight: xx-large;">▶</span></a> ' : '<span style="color: lightgray; font-weight: xx-large;" title="'._('Disabled').'">🚧</span>';
        $dataRowRaw['name'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['id'], '<strong>'.$dataRowRaw['name'].'</strong>');
        $dataRowRaw['id'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['id'], '⚗️ #'.$dataRowRaw['id']);

        $dataRowRaw['executor'] = (string) new Ui\ExecutorImage($dataRowRaw['executor'], ['style' => 'height: 40px;']);
        $dataRowRaw['app_id'] = (string) new Ui\AppLinkButton(new Application((int) $dataRowRaw['app_id']), ['style' => 'height: 40px;']);
        $dataRowRaw['company_id'] = (string) new Ui\CompanyLinkButton(new Company((int) $dataRowRaw['company_id']), ['style' => 'height: 40px;']);

        return $dataRowRaw;
    }
}
