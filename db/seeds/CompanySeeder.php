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
    public function run() {

        $data = [
            [
                'id' => 0,
                'enabled' => true,
                'flexibee' => 0,
                'nazev' => 'Demo Firma',
                'ic' => '12345678',
                'company' => 'demo',
                'rw' => 'true',
                'setup' => false,
                'webhook' => false,
                'DatCreate' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 1,
                'enabled' => true,
                'flexibee' => 0,
                'nazev' => 'Ja Zivnostnik',
                'ic' => '87654321',
                'company' => 'demo_de',
                'rw' => 'true',
                'setup' => false,
                'webhook' => false,
                'DatCreate' => date('Y-m-d H:i:s')
            ]
        ];

        $posts = $this->table('company');
        $posts->insert($data)->save();
    }

}
