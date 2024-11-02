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

use Phinx\Seed\AbstractSeed;

class ServerSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 0,
                'url' => 'https://demo.flexibee.eu:5434',
                'name' => 'Demo EU',
                'user' => 'winstrom',
                'password' => 'winstrom',
                'DatCreate' => date('Y-m-d H:i:s'),
            ],
        ];

        $posts = $this->table('servers');
        $posts->insert($data)
            ->save();
    }
}
