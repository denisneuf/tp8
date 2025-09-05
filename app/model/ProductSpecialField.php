<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ProductSpecialField extends Model
{
    protected $name = 'product_special_fields';
    protected $autoWriteTimestamp = 'datetime';
    protected $deleteTime = 'delete_time';


    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function values()
    {
        return $this->hasMany(ProductSpecialValue::class, 'special_field_id');
    }

}
