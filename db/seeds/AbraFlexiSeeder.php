<?php

use Phinx\Seed\AbstractSeed;

class AbraFlexiSeeder extends AbstractSeed {

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
                'url' => 'https://demo.flexibee.eu:5434',
                'name' => 'Demo EU',
                'user' => 'winstrom',
                'password' => 'winstrom',
                'DatCreate' => date('Y-m-d H:i:s')
            ]
        ];

        $posts = $this->table('abraflexis');
        $posts->insert($data)
                ->save();
    }

}
