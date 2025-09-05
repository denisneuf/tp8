<?php

use think\migration\Migrator;
use think\migration\db\Column;

class UpdateMenuTables extends Migrator
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

        $this->table('menus')
            ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => 'Fecha de creación'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => 'Fecha de actualización'])
            ->update();

        // Añadir campos a la tabla menu_columns
        $this->table('menu_columns')
            ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => 'Fecha de creación'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => 'Fecha de actualización'])
            ->update();

        // Añadir campos a la tabla menu_links
        $this->table('menu_links')
            ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => 'Fecha de creación'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => 'Fecha de actualización'])
            ->update();

    }
}
