<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class ProductType extends Model
{
    use SoftDelete;

    protected $name = 'product_types';

    // Timestamps automáticos
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Campo para soft delete
    protected $deleteTime = 'delete_time';


    protected $type = [
        'id'           => 'integer',
        'name'         => 'string',
        'slug'         => 'string',
        'bs_icon'      => 'string',
        'txt_short'    => 'string',
        'txt_long'     => 'text',
        'description'  => 'text',
        'pic'          => 'string',
        'bg'           => 'string',
        'visible'      => 'boolean',
        'create_time'  => 'datetime',
        'update_time'  => 'datetime',
        'delete_time'  => 'datetime',
    ];

    protected $field = [
        'name', 'slug', 'bs_icon', 'txt_short', 'txt_long',
        'description', 'pic', 'bg', 'visible',
        'create_time', 'update_time', 'delete_time'
    ];

    public function specialFields()
    {
        return $this->hasMany(ProductSpecialField::class, 'product_type_id');
    }
}
