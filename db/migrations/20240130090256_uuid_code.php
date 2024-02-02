<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UuidCode extends AbstractMigration
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
    public function change(): void
    {
        $rows = $this->fetchAll('SELECT * FROM apps');
        $allCodes = [];
        foreach ($rows as $appRow) {
            $allCodes[$appRow['code']] = $appRow['id'];
        }


        foreach ($rows as $appRow) {
            if (empty($appRow['uuid'])) {
                $builder = $this->getQueryBuilder();
                $builder->update('apps')->set('uuid', \Ease\Functions::guidv4())->where(['id' => $appRow['id']])->execute();
            }
            if (empty($appRow['code'])) {
                $code = substr(substr(strtoupper(basename($appRow['executable'])), -7), 0, 6);
                $try = 1;
                while (array_key_exists($code, $allCodes)) {
                    $code = substr(substr(strtoupper($appRow['executable']), -6), 0, 6) . strval($try++);
                }
                $builder = $this->getQueryBuilder();
                $builder->update('apps')->set('code', $code)->where(['id' => $appRow['id']])->execute();
                $allCodes[$code] = $appRow['id'];
            }
        }

    }
}
