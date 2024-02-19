<?php

use Phinx\Seed\AbstractSeed;

class CompanySeeder extends AbstractSeed {

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
                'enabled' => true,
                'server' => 0,
                'name' => 'Demo Firma',
                'ic' => '12345678',
                'company' => 'demo',
                'rw' => 0,
                'setup' => 0,
                'webhook' => 0,
                'code' => 'DEMO',
                'DatCreate' => date('Y-m-d H:i:s')
            ],
            [
                'enabled' => true,
                'server' => 0,
                'name' => 'Ja Zivnostnik',
                'ic' => '87654321',
                'company' => 'demo_de',
                'rw' => 0,
                'setup' => 0,
                'webhook' => 0,
                'code' => 'DEMODE',
                'DatCreate' => date('Y-m-d H:i:s')
            ]
        ];

        $posts = $this->table('company');
        $posts->insert($data)->save();
    }

}
