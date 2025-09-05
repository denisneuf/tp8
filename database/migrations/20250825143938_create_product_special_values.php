<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateProductSpecialValues extends Migrator
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
        $table = $this->table('product_special_values');
        $table->addColumn('product_id', 'integer')
              ->addColumn('special_field_id', 'integer')
              ->addColumn('value', 'string', ['limit' => 255])
              ->addColumn('create_time', 'datetime', ['null' => true])
              ->addColumn('update_time', 'datetime', ['null' => true])
              ->addColumn('delete_time', 'datetime', ['null' => true])
              ->create();
    }
}
