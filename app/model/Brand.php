<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Brand extends Model
{
    use SoftDelete;

    // Nombre de la tabla
    protected $name = 'brands';

    // Campos permitidos para escritura masiva
    protected $field = [
        'pic',
        'brand_en',
        'brand_cn',
        'slug',
        'meta_title',
        'meta_description',
        'keywords',
        'txt_description',
        'block_description',
        'block_pic',
        'visible',
        'email',
        'telephone',
        'direccion',
        'fax',
        'web',
        'create_time',
        'update_time',
        'delete_time',
    ];

    // Tipos de datos
    protected $type = [
        'id'                => 'integer',
        'pic'               => 'string',
        'brand_en'          => 'string',
        'brand_cn'          => 'string',
        'slug'              => 'string',
        'meta_title'        => 'string',
        'meta_description'  => 'text',
        'keywords'          => 'text',
        'txt_description'   => 'text',
        'block_description' => 'text',
        'block_pic'         => 'string',
        'visible'           => 'integer',
        'email'             => 'string',
        'telephone'         => 'string',
        'direccion'         => 'string',
        'fax'               => 'string',
        'web'               => 'string',
        'create_time'       => 'datetime',
        'update_time'       => 'datetime',
        'delete_time'       => 'datetime',
    ];



    // Timestamps automáticos
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Campo para soft delete
    protected $deleteTime = 'delete_time';

    // Relación futura con productos
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
