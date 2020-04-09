<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'id' => 0,
                'enabled'=>true,
                'email' => 'info@vitexsoftware.cz',
                'login' => 'vitex',
                'password' => '7254a96290b564d1b0cd85b9881b6b1a:b3',
                'firstname' => 'Vítězslav',
                'lastname' => 'Dvořák',
                'DatCreate' => date('Y-m-d H:i:s')
            ]
        ];

        $posts = $this->table('user');
        $posts->insert($data)
            ->save();
    }
}
