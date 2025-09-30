<?php
namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Category extends Model
{
    use SoftDelete;

    // Nombre de la tabla
    protected $name = 'categories';

    // Timestamps automáticos
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Campo para soft delete
    protected $deleteTime = 'delete_time';

    // Campos permitidos para asignación masiva
    protected $field = [
        'bs_icon',
        'txt_short',
        'txt_long',
        'slug',
        'description',
        'pic',
        'bg',
        'visible',
        'create_time',
        'update_time',
        'delete_time',
    ];

    // Conversión de tipos
    protected $type = [
        'bs_icon'      => 'string',
        'txt_short'    => 'string',
        'txt_long'     => 'string',
        'slug'         => 'string',
        'description'  => 'string',
        'pic'          => 'string',
        'bg'           => 'string',
        'visible'      => 'integer',
        'create_time'  => 'datetime',
        'update_time'  => 'datetime',
        'delete_time'  => 'datetime',
    ];


    // En app/model/Category.php - Añadir estas relaciones
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function productTypes()
    {
        return $this->hasMany(ProductType::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->order('order', 'asc');
    }


}
