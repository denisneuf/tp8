<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateProductsTable extends Migrator
{
    public function change()
    {
        $table = $this->table('products');

        $table->addColumn('name', 'string', ['limit' => 255])
              ->addColumn('slug', 'string', ['limit' => 255, 'default' => '', 'null' => false])
              ->addColumn('sku', 'string', ['limit' => 40, 'default' => '', 'null' => false])
              ->addColumn('asin', 'string', ['limit' => 10, 'default' => '', 'null' => false])
              ->addColumn('manufacturer', 'char', ['limit' => 15, 'default' => '', 'null' => false])
              ->addColumn('productcode', 'string', ['limit' => 13, 'default' => '', 'null' => true])
              ->addColumn('amazonlink', 'string', ['limit' => 255, 'default' => null, 'null' => true])
              ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
              ->addColumn('description', 'text', ['null' => true])
              ->addColumn('stock', 'integer', ['default' => 0])
              ->addColumn('like', 'integer', ['default' => null, 'null' => true])
              ->addColumn('visible', 'boolean', ['default' => 0])
              ->addColumn('available', 'boolean', ['default' => 0])
              ->addColumn('brand_id', 'integer', ['signed' => false])
              ->addColumn('category_id', 'integer', ['signed' => false])
              ->addColumn('product_type_id', 'integer', ['signed' => false])
              ->addColumn('create_time', 'datetime', ['null' => true])
              ->addColumn('update_time', 'datetime', ['null' => true])
              ->addColumn('delete_time', 'datetime', ['null' => true]) // Soft delete
              ->create();
    }
}
