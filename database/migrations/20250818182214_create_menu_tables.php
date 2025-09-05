<?php
declare(strict_types=1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateMenuTables extends Migrator
{
    public function change()
    {
        // Tabla principal: menus
        $this->table('menus', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('title', 'string', ['limit' => 100, 'comment' => 'Título del menú'])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Enlace directo (opcional)'])
            ->addColumn('has_submenu', 'boolean', ['default' => false, 'comment' => 'Indica si tiene submenú'])
            ->addColumn('order', 'integer', ['default' => 0, 'comment' => 'Orden de aparición'])
            ->addColumn('visible', 'boolean', ['default' => true, 'comment' => 'Mostrar en frontend'])
            ->create();

        // Tabla de columnas del submenú
        $this->table('menu_columns', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('menu_id', 'integer', ['signed' => false, 'comment' => 'ID del menú principal'])
            ->addColumn('title', 'string', ['limit' => 100, 'comment' => 'Título de la columna'])
            ->addColumn('order', 'integer', ['default' => 0, 'comment' => 'Orden de la columna'])
            ->addForeignKey('menu_id', 'menus', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();

        // Tabla de enlaces dentro de cada columna
        $this->table('menu_links', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('column_id', 'integer', ['signed' => false, 'comment' => 'ID de la columna del submenú'])
            ->addColumn('title', 'string', ['limit' => 100, 'comment' => 'Texto del enlace'])
            ->addColumn('url', 'string', ['limit' => 255, 'comment' => 'URL del enlace'])
            ->addColumn('order', 'integer', ['default' => 0, 'comment' => 'Orden del enlace'])
            ->addForeignKey('column_id', 'menu_columns', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
