<?php
use think\migration\Migrator;
use think\migration\db\Column;


class AddMetaFieldsToProductsTable extends Migrator
{
    public function change()
    {
        $table = $this->table('products');
        
        // Campos meta básicos con prefijo
        $table->addColumn('title', 'string', [
            'limit' => 70,
            'null' => true,
            'default' => null,
            'comment' => 'Title - normal'
        ])->addColumn('meta_title', 'string', [
            'limit' => 70,
            'null' => true,
            'default' => null,
            'comment' => 'Meta Title - Para pestaña del navegador'
        ])->addColumn('meta_description', 'string', [
            'limit' => 160,
            'null' => true, 
            'default' => null,
            'comment' => 'Meta Description (SEO)'
        ])->addColumn('meta_keywords', 'string', [
            'limit' => 255,
            'null' => true,
            'default' => null,
            'comment' => 'Palabras clave SEO'
        ])
        
        // Open Graph fields
        ->addColumn('og_title', 'string', [
            'limit' => 70,
            'null' => true,
            'default' => null,
            'comment' => 'Open Graph Title'
        ])->addColumn('og_description', 'string', [
            'limit' => 160,
            'null' => true,
            'default' => null, 
            'comment' => 'Open Graph Description'
        ])->addColumn('og_image', 'string', [
            'limit' => 255,
            'null' => true,
            'default' => null,
            'comment' => 'Open Graph Image URL'
        ])->addColumn('og_type', 'string', [
            'limit' => 50,
            'default' => 'product',
            'comment' => 'Open Graph Type'
        ])->update();
    }
}