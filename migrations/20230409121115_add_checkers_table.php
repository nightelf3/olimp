<?php

use Phinx\Migration\AbstractMigration;

class AddCheckersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('checkers', [ 'id' => 'checker_id' ]);
        $table->addColumn('name', 'string', [ 'limit' => 255, 'null' => false ])
            ->addColumn('user_id', 'integer', [ 'null' => false ])
            ->addColumn('token', 'string', [ 'limit' => 32 ])
            ->addColumn('is_active', 'boolean', [ 'default' => false ])
            ->addColumn('created_at', 'datetime', [ 'default' => 'CURRENT_TIMESTAMP' ])
            ->addColumn('updated_at', 'datetime', [ 'default' => 'CURRENT_TIMESTAMP' ])
            ->create();
        $table->addIndex(['name', 'user_id'], [ 'unique' => true ])->update();
        $table->addForeignKey('user_id', 'users', 'user_id',  [ 'delete' => 'CASCADE' ])
            ->update();
    }
}
