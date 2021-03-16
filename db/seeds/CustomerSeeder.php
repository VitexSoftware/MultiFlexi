<?php

use Phinx\Seed\AbstractSeed;

class CustomerSeeder extends AbstractSeed {

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
                'email' => 'demo@abraflexi.eu',
                'firstname' => 'Demo',
                'lastname' => 'Demo',
                'password' => '',
                'login' => 'demo',
                'DatCreate' => date('Y-m-d H:i:s')
            ]
        ];

        $posts = $this->table('customer');
        $posts->insert($data)->save();
    }

}
