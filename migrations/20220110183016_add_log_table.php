<?php


use Phinx\Migration\AbstractMigration;

class AddLogTable extends AbstractMigration
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
        $table = $this->table('logs', [ 'id' => 'log_id' ]);
        $table->addColumn('user_id', 'integer')
            ->addColumn('data', 'string', [ 'limit' => 255 ])
            ->addColumn('created_at', 'datetime', [ 'default' => 'CURRENT_TIMESTAMP' ])
            ->create();
        $table->addForeignKey('user_id', 'users', 'user_id',  [ 'delete' => 'CASCADE' ])
            ->update();
    }
}
