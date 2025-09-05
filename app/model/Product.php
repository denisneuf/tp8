<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Product extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $table = 'products';

    protected $field = [
        'name',
        'slug',
        'sku',
        'asin',
        'pic',
        'manufacturer',
        'productcode',
        'amazonlink',
        'price',
        'description',
        'stock',
        'like',
        'visible',
        'available',
        'brand_id',
        'category_id',
        'product_type_id',
        'create_time',
        'update_time',
        'delete_time',
    ];

    protected $type = [
        'name' => 'string',
        'slug' => 'string',
        'sku' => 'string',
        'asin' => 'string',
        'pic' => 'string',
        'manufacturer' => 'string',
        'productcode' => 'string',
        'amazonlink' => 'string',
        'price' => 'float',
        'description' => 'text',
        'stock' => 'integer',
        'like' => 'integer',
        'visible' => 'boolean',
        'available' => 'boolean',
        'brand_id' => 'integer',
        'category_id' => 'integer',
        'product_type_id' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
    }
}
