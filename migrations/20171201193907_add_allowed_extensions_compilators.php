<?php

use Phinx\Migration\AbstractMigration;

class AddAllowedExtensionsCompilators extends AbstractMigration
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
        $table = $this->table('compilers');
        $table->addColumn('ext', 'string')->update();

        $this->execute("DELETE FROM oi_compilers WHERE 1");
        $table->insert([
            [ 'name' => 'C', 'ext' => 'c' ],
            [ 'name' => 'C++', 'ext' => 'cpp' ],
            [ 'name' => 'Pascal', 'ext' => 'pas' ],
            [ 'name' => 'Python', 'ext' => 'py' ]
        ])->update();
    }
}
