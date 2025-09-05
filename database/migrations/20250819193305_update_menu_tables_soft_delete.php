<?php

use think\migration\Migrator;
use think\migration\db\Column;

class UpdateMenuTablesSoftDelete extends Migrator
{
    public function change()
    {
        // Tabla menus
        $this->table('menus')
            ->addColumn('delete_time', 'datetime', ['null' => true, 'comment' => 'Soft delete'])
            ->update();

        // Tabla menu_columns
        $this->table('menu_columns')
            ->addColumn('delete_time', 'datetime', ['null' => true, 'comment' => 'Soft delete'])
            ->update();

        // Tabla menu_links
        $this->table('menu_links')
            ->addColumn('delete_time', 'datetime', ['null' => true, 'comment' => 'Soft delete'])
            ->update();
    }
}

