<?php

use Phinx\Migration\AbstractMigration;

class QueueTableCreation extends AbstractMigration
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
        $compilersTable = $this->table('compilers', [ 'id' => 'compiler_id' ]);
        $compilersTable->addColumn('name', 'string');
        $compilersTable->insert([
            [ 'name' => 'C/C++' ],
            [ 'name' => 'Pascal' ],
            [ 'name' => 'Python' ]
        ]);
        $compilersTable->create();

        $table = $this->table('queue', [ 'id' => 'queue_id' ]);
        $table->addColumn('user_id', 'integer')
            ->addColumn('task_id', 'integer')
            ->addColumn('user_filename', 'string')
            ->addColumn('filename', 'string')
            ->addColumn('compiler_id', 'integer')
            ->addColumn('created_at', 'datetime', [ 'default' => 'CURRENT_TIMESTAMP' ])
            ->addColumn('stan', 'string', [ 'default' => '0' ])
            ->addColumn('tests', 'string', [ 'null' => true ])
            ->addColumn('upload_ip', 'string', [ 'limit' => 16 ])
            ->create();
        $table->addForeignKey('user_id', 'users', 'user_id',  [ 'delete' => 'CASCADE' ])
            ->addForeignKey('task_id', 'tasks', 'task_id',  [ 'delete' => 'CASCADE' ])
            ->addForeignKey('compiler_id', 'compilers', 'compiler_id',  [ 'delete' => 'RESTRICT' ])
            ->update();
    }
}
