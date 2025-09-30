<?php
declare(strict_types=1);

use think\migration\Migrator;
use think\migration\db\Column;

class RemoveSeoFieldsFromBrands extends Migrator
{
    /**
     * Eliminar campos SEO de la tabla brands
     */
    public function up()
    {
        $table = $this->table('brands');
        
        // Eliminar los campos especificados
        if ($table->hasColumn('meta_title')) {
            $table->removeColumn('meta_title');
        }
        
        if ($table->hasColumn('meta_description')) {
            $table->removeColumn('meta_description');
        }
        
        if ($table->hasColumn('keywords')) {
            $table->removeColumn('keywords');
        }
        
        if ($table->hasColumn('txt_description')) {
            $table->removeColumn('txt_description');
        }
        
        $table->update();
    }

    /**
     * Revertir la migración (recuperar campos eliminados)
     */
    public function down()
    {
        $table = $this->table('brands');
        
        // Recuperar los campos eliminados
        if (!$table->hasColumn('meta_title')) {
            $table->addColumn('meta_title', 'string', [
                'limit' => 255, 
                'null' => true, 
                'default' => null, 
                'comment' => 'Título SEO',
                'after' => 'slug'
            ]);
        }
        
        if (!$table->hasColumn('meta_description')) {
            $table->addColumn('meta_description', 'text', [
                'null' => true, 
                'default' => null, 
                'comment' => 'Descripción SEO',
                'after' => 'meta_title'
            ]);
        }
        
        if (!$table->hasColumn('keywords')) {
            $table->addColumn('keywords', 'string', [
                'limit' => 255, 
                'null' => true, 
                'default' => null, 
                'comment' => 'Palabras clave SEO',
                'after' => 'meta_description'
            ]);
        }
        
        if (!$table->hasColumn('txt_description')) {
            $table->addColumn('txt_description', 'text', [
                'null' => true, 
                'default' => null, 
                'comment' => 'Descripción larga',
                'after' => 'keywords'
            ]);
        }
        
        $table->update();
    }
}