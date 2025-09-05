<?php
use think\migration\Migrator;
use think\migration\db\Column;

class CreateCategoriesTable extends Migrator
{
    public function change()
    {
        $table = $this->table('categories');
        $table->addColumn('bs_icon', 'string', ['limit' => 55, 'null' => true, 'comment' => 'Icono Bootstrap'])
              ->addColumn('txt_short', 'string', ['limit' => 15, 'comment' => 'Texto corto'])
              ->addColumn('txt_long', 'string', ['limit' => 35, 'comment' => 'Texto largo'])
              ->addColumn('slug', 'string', ['limit' => 55, 'comment' => 'Nombre amigable para URL'])
              ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'comment' => 'Descripción'])
              ->addColumn('pic', 'string', ['limit' => 35, 'null' => true, 'comment' => 'Imagen'])
              ->addColumn('bg', 'string', ['limit' => 35, 'null' => true, 'comment' => 'Fondo'])
              ->addColumn('visible', 'integer', ['default' => 0, 'comment' => 'Visibilidad'])
              ->addColumn('create_time', 'integer', ['default' => 0])
              ->addColumn('update_time', 'integer', ['default' => 0])
              ->addColumn('delete_time', 'integer', ['null' => true, 'default' => null])
              ->addIndex(['slug'], ['unique' => true])
              ->addIndex(['delete_time'])
              ->create();
    }
}
