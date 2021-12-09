<?php


use Phinx\Migration\AbstractMigration;

class ReworkCommentsColumns extends AbstractMigration
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
        $table = $this->table('comments');
        $table->dropForeignKey('user_id')->save();
        $table->removeColumn('user_id')->save();

        $table->addColumn('from_id', 'integer', [ 'after' => 'task_id' ])
            ->addColumn('to_id', 'integer',  [ 'after' => 'from_id' ])
            ->update();

        $table->addForeignKey('from_id', 'users', 'user_id',  [ 'delete' => 'CASCADE' ])
            ->addForeignKey('to_id', 'users', 'user_id',  [ 'delete' => 'CASCADE' ])
            ->update();
    }
}
