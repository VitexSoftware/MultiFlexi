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

use Phinx\Migration\AbstractMigration;

/**
 * @no-named-arguments
 */
final class AbraFlexiAppsEnvironment extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $apps = $this->fetchAll('SELECT * FROM apps');
        $table = $this->table('conffield');

        foreach ($apps as $appInfo) {
            $rows = [
                [
                    'app_id' => $appInfo['id'],
                    'keyname' => 'ABRAFLEXI_URL',
                    'description' => 'AbraFlexi Server URI',
                    'defval' => 'https://demo.flexibee.eu:5434',
                    'type' => 'string',
                ],
                [
                    'app_id' => $appInfo['id'],
                    'keyname' => 'ABRAFLEXI_LOGIN',
                    'description' => 'AbraFlexi Login',
                    'defval' => 'winstrom',
                    'type' => 'string',
                ],
                [
                    'app_id' => $appInfo['id'],
                    'keyname' => 'ABRAFLEXI_PASSWORD',
                    'description' => 'AbraFlexi password',
                    'defval' => 'winstrom',
                    'type' => 'string',
                ],
                [
                    'app_id' => $appInfo['id'],
                    'keyname' => 'ABRAFLEXI_COMPANY',
                    'description' => '',
                    'defval' => 'demo_de',
                    'type' => 'string',
                ],
            ];
            $table->insert($rows)->saveData();
        }
    }
}
