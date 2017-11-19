<?php

use Phinx\Migration\AbstractMigration;

class UpdateUserTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('users')
            ->changeColumn('username', 'string', [ 'limit' => 128 ])
            ->addColumn('surname', 'string', [ 'limit' => 256, 'after' => 'email' ])
            ->addColumn('name', 'string', [ 'limit' => 256, 'after' => 'email' ])
            ->addColumn('phone', 'string', [ 'limit' => 64, 'after' => 'email' ])
            ->addColumn('school', 'string', [ 'limit' => 128, 'after' => 'email' ])
            ->addColumn('class', 'string', [ 'limit' => 64, 'after' => 'email' ])
            ->update();
    }
}
