<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ProductSpecialValue extends Model
{
    protected $name = 'product_special_values';
    protected $autoWriteTimestamp = 'datetime';
    protected $deleteTime = 'delete_time';


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function field()
    {
        return $this->belongsTo(ProductSpecialField::class, 'special_field_id');
    }

    
}
