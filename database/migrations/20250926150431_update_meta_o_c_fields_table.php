<?php
use think\migration\Migrator;
use think\migration\db\Column;

class UpdateMetaOCFieldsTable extends Migrator
{
    public function change()
    {
        $table = $this->table('meta');
        
        // Open Graph fields (solo los 4 esenciales)
        $table->addColumn('og_title', 'string', [
            'limit' => 70,
            'null' => true,
            'default' => null,
            'comment' => 'Open Graph Title - Si vacío usa title'
        ])->addColumn('og_description', 'string', [
            'limit' => 160, 
            'null' => true,
            'default' => null,
            'comment' => 'Open Graph Description - Si vacío usa description'
        ])->addColumn('og_image', 'string', [
            'limit' => 255,
            'null' => true, 
            'default' => null,
            'comment' => 'Open Graph Image URL (CDN/recomendado 1200x630px)'
        ])->addColumn('og_type', 'string', [
            'limit' => 50,
            'default' => 'website',
            'comment' => 'Open Graph Type: website, article, product, etc.'
        ])->update();
    }
}