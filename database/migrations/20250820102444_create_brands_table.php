<?php
declare(strict_types=1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateBrandsTable extends Migrator
{
    public function change()
    {
        $table = $this->table('brands');

        $table->addColumn('pic', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Imagen principal de la marca'])
              ->addColumn('brand_en', 'string', ['limit' => 100, 'comment' => 'Nombre en inglés'])
              ->addColumn('brand_cn', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'Nombre en chino'])
              ->addColumn('slug', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'Slug para URL'])
              ->addColumn('meta_title', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Título SEO'])
              ->addColumn('meta_description', 'text', ['null' => true, 'default' => null, 'comment' => 'Descripción SEO'])
              ->addColumn('keywords', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Palabras clave SEO'])
              ->addColumn('txt_description', 'text', ['null' => true, 'default' => null, 'comment' => 'Descripción larga'])
              ->addColumn('block_description', 'text', ['null' => true, 'default' => null, 'comment' => 'Descripción para bloque destacado'])
              ->addColumn('block_pic', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Imagen para bloque destacado'])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'Email de contacto'])
              ->addColumn('telephone', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => 'Teléfono'])
              ->addColumn('direccion', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Dirección'])
              ->addColumn('fax', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => 'Fax'])
              ->addColumn('web', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Sitio web'])
              ->addColumn('create_time', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Fecha de creación'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Fecha de actualización'])
              ->addColumn('delete_time', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Fecha de eliminación (soft delete)'])
              ->create();
    }
}
