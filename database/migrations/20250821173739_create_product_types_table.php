<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateProductTypesTable extends Migrator
{
    public function change()
    {
        $table = $this->table('product_types');
        $table->addColumn('name', 'string', ['limit' => 100, 'comment' => 'Nombre del tipo de producto'])
              ->addColumn('slug', 'string', ['limit' => 100, 'comment' => 'Slug para URL'])
              ->addColumn('bs_icon', 'string', ['limit' => 50, 'null' => true, 'comment' => 'Ícono Bootstrap'])
              ->addColumn('txt_short', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Texto corto descriptivo'])
              ->addColumn('txt_long', 'text', ['null' => true, 'comment' => 'Texto largo descriptivo'])
              ->addColumn('description', 'text', ['null' => true, 'comment' => 'Descripción adicional'])
              ->addColumn('pic', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Imagen representativa'])
              ->addColumn('bg', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Fondo personalizado'])
              ->addColumn('visible', 'boolean', ['default' => true, 'comment' => 'Visibilidad del tipo de producto'])
              ->addColumn('create_time', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Fecha de creación'])
              ->addColumn('update_time', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Fecha de actualización'])
              ->addColumn('delete_time', 'datetime', ['null' => true, 'comment' => 'Fecha de eliminación (soft delete)'])
              ->create();
    }
}
