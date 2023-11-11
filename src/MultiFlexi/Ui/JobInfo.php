<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

/**
 *
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of JobInfo
 *
 * @author vitex
 */
class JobInfo extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\Job $job, $properties = [])
    {
        parent::__construct(new \Ease\Html\H1Tag(_('Job') . ' ' . $job->getMyKey()), $properties);

        $jobInfoTable = new \Ease\Html\TableTag();
        $jobInfoTable->addRowColumns([_('Application'), new AppLinkButton(new \MultiFlexi\Application($job->getDataValue('app_id')))]);
        $jobInfoTable->addRowColumns([_('Company'), new CompanyLinkButton(new \MultiFlexi\Company($job->getDataValue('company_id')))]);
        $jobInfoTable->addRowColumns([_('Begin'), [$job->getDataValue('begin'), '&nbsp;', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job->getDataValue('begin')))->getTimestamp()))]]);
        $jobInfoTable->addRowColumns([_('End'), [$job->getDataValue('end'), '&nbsp;', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job->getDataValue('end')))->getTimestamp()))]]);
        $jobInfoTable->addRowColumns([_('Commandline'), $job->getDataValue('command')]);
        $jobInfoTable->addRowColumns([_('Exitcode'), new ExitCode($job->getDataValue('exitcode'))]);

        $launcher = new \MultiFlexi\User($job->getDataValue('launched_by'));
        $jobInfoTable->addRowColumns([_('Person'), $launcher->getMyKey() ? new \Ease\Html\ATag('user.php?id=' . $launcher->getMyKey(), new \Ease\TWB4\Badge('info', $launcher->getUserLogin())) : _('Timer')]);
        $jobInfoTable->addRowColumns([_('Environment'), new EnvironmentView($job->getEnv())]);

        $this->addItem($jobInfoTable);
    }
}
