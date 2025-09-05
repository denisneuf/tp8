<?php
use think\migration\Migrator;

class RemoveProductsUniqueFieldManufacturer extends Migrator
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('products');
        
        // Eliminar el índice único del campo manufacturer por nombre
        $table->removeIndexByName('idx_unique_manufacturer');
        
        $table->update();
    }
}