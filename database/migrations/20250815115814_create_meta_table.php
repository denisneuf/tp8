<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateMetaTable extends Migrator
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
    public function change(): void
    {
        $table = $this->table('meta', ['engine' => 'InnoDB', 'comment' => 'Tabla de metadatos']);

        $table->addColumn('page', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Página'])
              ->addColumn('title', 'string', ['limit' => 70, 'null' => true, 'comment' => 'Título'])
              ->addColumn('metatitle', 'string', ['limit' => 70, 'null' => true, 'comment' => 'Meta título'])
              ->addColumn('description', 'string', ['limit' => 160, 'null' => true, 'comment' => 'Descripción'])
              ->addColumn('keywords', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Palabras clave'])
              ->addColumn('create_time', 'datetime', ['null' => true])
              ->addColumn('update_time', 'datetime', ['null' => true])
              ->addIndex(['page'], ['unique' => true])
              ->create();
    }
}
