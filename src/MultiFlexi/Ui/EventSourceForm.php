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
 * Form for editing an EventSource (webhook adapter database connection).
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class EventSourceForm extends SecureForm
{
    /**
     * @param \MultiFlexi\EventSource $eventSource EventSource object
     * @param array                   $formProperties Additional form properties
     */
    public function __construct(\MultiFlexi\EventSource $eventSource, array $formProperties = [])
    {
        $row1 = new \Ease\TWB4\Row();
        $row1->addColumn(6, [
            new \Ease\TWB4\FormGroup(_('Source Name'), new \Ease\Html\InputTextTag('name', $eventSource->getDataValue('name'))),
            new \Ease\TWB4\FormGroup(_('Adapter Type'), new \Ease\Html\InputTextTag('adapter_type', $eventSource->getDataValue('adapter_type'), [], _('e.g. abraflexi-webhook-acceptor'))),
        ]);
        $row1->addColumn(6, [
            new \Ease\TWB4\FormGroup(_('Poll Interval (seconds)'), new \Ease\Html\InputNumberTag('poll_interval', (string) ($eventSource->getDataValue('poll_interval') ?: '60'))),
            new \Ease\TWB4\FormGroup(
                _('Enabled'),
                new \Ease\Html\SelectTag('enabled', ['0' => _('No'), '1' => _('Yes')], (string) ($eventSource->getDataValue('enabled') ?: '1')),
            ),
        ]);

        $row2 = new \Ease\TWB4\Row();
        $row2->addColumn(4, [
            new \Ease\TWB4\FormGroup(_('DB Driver'), new \Ease\Html\SelectTag('db_connection', [
                'mysql' => 'MySQL / MariaDB',
                'pgsql' => 'PostgreSQL',
                'sqlite' => 'SQLite',
            ], $eventSource->getDataValue('db_connection') ?: 'mysql')),
        ]);
        $row2->addColumn(4, [
            new \Ease\TWB4\FormGroup(_('DB Host'), new \Ease\Html\InputTextTag('db_host', $eventSource->getDataValue('db_host') ?: 'localhost')),
        ]);
        $row2->addColumn(4, [
            new \Ease\TWB4\FormGroup(_('DB Port'), new \Ease\Html\InputTextTag('db_port', $eventSource->getDataValue('db_port') ?: '3306')),
        ]);

        $row3 = new \Ease\TWB4\Row();
        $row3->addColumn(4, new \Ease\TWB4\FormGroup(_('Database Name'), new \Ease\Html\InputTextTag('db_database', $eventSource->getDataValue('db_database'))));
        $row3->addColumn(4, new \Ease\TWB4\FormGroup(_('DB Username'), new \Ease\Html\InputTextTag('db_username', $eventSource->getDataValue('db_username'))));
        $row3->addColumn(4, new \Ease\TWB4\FormGroup(_('DB Password'), new \Ease\Html\InputPasswordTag('db_password', $eventSource->getDataValue('db_password'))));

        $formContents = [$row1, $row2, $row3];

        parent::__construct(['action' => 'eventsource.php', 'method' => 'POST'], $formContents);

        $submitRow = new \Ease\TWB4\Row();

        if (null === $eventSource->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton('🚀 '._('Test Connection'), 'disabled btn-lg btn-block'));
        } else {
            $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('eventsource.php?id='.$eventSource->getMyKey().'&test=true', '🚀 '._('Test Connection'), 'success btn-lg btn-block'));
        }

        $submitRow->addColumn(8, new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block', ['title' => _('Apply changes')]));

        if (null === $eventSource->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton('⚰️ '._('Remove').' !', 'disabled btn-lg btn-block', ['disabled' => 'true']));
        } else {
            $this->addItem(new \Ease\Html\InputHiddenTag('id', $eventSource->getMyKey()));

            if (WebPage::getRequestValue('remove') === 'true') {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('eventsource.php?delete='.$eventSource->getMyKey(), '⚰️ '._('Remove').' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('eventsource.php?id='.$eventSource->getMyKey().'&remove=true', '⚰️ '._('Remove').' ?', 'warning btn-lg btn-block'));
            }
        }

        $this->addItem($submitRow);
    }

    public function afterAdd(): void
    {
    }
}
