<?php

use Phinx\Migration\AbstractMigration;

class SettingsTableCreation extends AbstractMigration
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
        $table = $this->table('settings', [ 'id' => 'setting_id' ]);
        $table->addColumn('key', 'string')
            ->addColumn('value', 'string')
            ->create();
        $table->addIndex('key', [ 'unique' => true ])->update();
        $table->insert([
            [ 'key' => 'olimp_start', 'value' => time() ],
            [ 'key' => 'olimp_continuity', 'value' => 3600 * 24 * 4 ]
        ])->update();
    }
}
