<?php

use Phinx\Migration\AbstractMigration;

class TaskTableCreation extends AbstractMigration
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
        $table = $this->table('tasks', [ 'id' => 'task_id' ]);
        $table->addColumn('task', 'text')
            ->addColumn('input', 'text')
            ->addColumn('output', 'text')
            ->addColumn('tests_count', 'integer', [ 'signed' => false ])
            ->addColumn('time_limit', 'decimal', [ 'precision' => 10, 'scale' => 2 ])
            ->addColumn('memory_limit', 'decimal', [ 'precision' => 10, 'scale' => 2 ])
            ->addColumn('max_score', 'integer', [ 'signed' => false ])
            ->addColumn('mulct', 'integer', [ 'signed' => false ])
            ->create();
    }
}
