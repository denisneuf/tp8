<?php
namespace app\validate;

use think\Validate;

class ProductFormValidator extends Validate
{
    protected $rule = [
        'name'            => 'require|max:255',
        'slug'            => 'require|max:255',
        'sku'             => 'require|max:40',
        'asin'            => 'require|max:10',
        'manufacturer'    => 'require|max:15',
        'productcode'     => 'max:13',
        'amazonlink'      => 'max:255|url',
        'price'           => 'float|egt:0',
        'description'     => 'max:65535',
        'stock'           => 'integer|egt:0',
        'like'            => 'integer|egt:0',
        'visible'         => 'in:0,1',
        'available'       => 'in:0,1',
        'brand_id'        => 'require|integer|egt:1',
        'category_id'     => 'require|integer|egt:1',
        'product_type_id' => 'require|integer|egt:1',
    ];

    protected $message = [
        'name.require'         => 'El nombre del producto es obligatorio',
        'slug.require'         => 'El slug es obligatorio',
        'sku.require'          => 'El SKU es obligatorio',
        'asin.require'         => 'El ASIN es obligatorio',
        'manufacturer.require' => 'El fabricante es obligatorio',
        'price.float'          => 'El precio debe ser un número decimal',
        'stock.integer'        => 'El stock debe ser un número entero',
        'visible.in'           => 'El campo visible debe ser 0 o 1',
        'available.in'         => 'El campo disponible debe ser 0 o 1',
        'brand_id.require'     => 'La marca es obligatoria',
        'category_id.require'  => 'La categoría es obligatoria',
        'product_type_id.require' => 'El tipo de producto es obligatorio',
    ];
}
