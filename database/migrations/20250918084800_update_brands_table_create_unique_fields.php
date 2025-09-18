<?php

use think\migration\Migrator;
use think\migration\db\Column;

class UpdateBrandsTableCreateUniqueFields extends Migrator
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

        $table = $this->table('brands');
        
        // Segundo: Modificar las columnas para establecer constraints apropiados
        $table->changeColumn('brand_en', 'string', [
            'limit' => 100, 
            'null' => false, 
            'comment' => 'Nombre en inglés'
        ])->changeColumn('brand_cn', 'string', [
            'limit' => 100, 
            'null' => true, 
            'default' => null,
            'comment' => 'Nombre en chino'
        ])->changeColumn('slug', 'string', [
            'limit' => 100, 
            'null' => false, 
            'comment' => 'Slug para URL'
        ])->update();
        
        // Tercero: Agregar índices únicos
        $table->addIndex(['brand_en'], ['unique' => true, 'name' => 'idx_brand_en_unique'])
              ->addIndex(['brand_cn'], ['unique' => true, 'name' => 'idx_brand_cn_unique'])
              ->addIndex(['slug'], ['unique' => true, 'name' => 'idx_slug_unique'])
              ->update();
    }

}
