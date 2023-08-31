<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed {

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void {
        $data = [
            [
                'id' => 0,
                'enabled' => true,
                'email' => 'demo@localhost.localhomain',
                'login' => 'demo',
                'password' => 'a26ac720512764602ce1c1ae537efb04:9d',
                'firstname' => 'Demo',
                'lastname' => 'Demo',
                'DatCreate' => date('Y-m-d H:i:s')
            ]
        ];

        $posts = $this->table('user');
        $posts->insert($data)
                ->save();
    }

}
