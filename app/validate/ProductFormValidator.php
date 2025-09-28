<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class ProductFormValidator extends Validate
{
    protected $rule = [
        // Campos existentes
        'name'            => 'require|max:255|unique:products',
        'slug'            => 'require|max:255|unique:products',
        'sku'             => 'require|max:40|unique:products',
        'asin'            => 'require|max:10|unique:products',
        'manufacturer'    => 'require|max:15|unique:products',
        'productcode'     => 'max:13|unique:products',
        'amazonlink'      => 'max:255|url|unique:products',
        'pic'             => 'max:255|unique:products',
        'price'           => 'float|egt:0',
        'description'     => 'max:65535',
        'stock'           => 'integer|egt:0',
        'like'            => 'integer|egt:0',
        'visible'         => 'in:0,1',
        'available'       => 'in:0,1',
        'brand_id'        => 'require|integer|egt:1',
        'category_id'     => 'require|integer|egt:1',
        'product_type_id' => 'require|integer|egt:1',
        
        // Nuevos campos meta SEO
        'title'           => 'max:70',
        'meta_title'      => 'max:70',
        'meta_description' => 'max:160',
        'meta_keywords'   => 'max:255',
        'og_title'        => 'max:70',
        'og_description'  => 'max:160',
        'og_image'        => 'max:255|url',
        'og_type'         => 'max:50|in:product,article,website',
    ];

    protected $message = [
        // Mensajes para campos existentes
        'name.require'         => ['code' => 'name', 'msg' => 'El nombre del producto es obligatorio'],
        'name.unique'          => ['code' => 'name', 'msg' => 'Este nombre de producto ya existe'],
        'name.max'             => ['code' => 'name', 'msg' => 'El nombre no puede superar 255 caracteres'],
        
        'slug.require'         => ['code' => 'slug', 'msg' => 'El slug es obligatorio'],
        'slug.unique'          => ['code' => 'slug', 'msg' => 'Este slug ya existe'],
        'slug.max'             => ['code' => 'slug', 'msg' => 'El slug no puede superar 255 caracteres'],
        
        'sku.require'          => ['code' => 'sku', 'msg' => 'El SKU es obligatorio'],
        'sku.unique'           => ['code' => 'sku', 'msg' => 'Este SKU ya existe'],
        'sku.max'              => ['code' => 'sku', 'msg' => 'El SKU no puede superar 40 caracteres'],
        
        'asin.require'         => ['code' => 'asin', 'msg' => 'El ASIN es obligatorio'],
        'asin.unique'          => ['code' => 'asin', 'msg' => 'Este ASIN ya existe'],
        'asin.max'             => ['code' => 'asin', 'msg' => 'El ASIN no puede superar 10 caracteres'],
        
        'manufacturer.require' => ['code' => 'manufacturer', 'msg' => 'El fabricante es obligatorio'],
        'manufacturer.unique'  => ['code' => 'manufacturer', 'msg' => 'Este fabricante ya existe'],
        'manufacturer.max'     => ['code' => 'manufacturer', 'msg' => 'El fabricante no puede superar 15 caracteres'],
        
        'productcode.unique'   => ['code' => 'productcode', 'msg' => 'Este código de producto ya existe'],
        'productcode.max'      => ['code' => 'productcode', 'msg' => 'El código de producto no puede superar 13 caracteres'],
        
        'amazonlink.unique'    => ['code' => 'amazonlink', 'msg' => 'Este enlace de Amazon ya existe'],
        'amazonlink.max'       => ['code' => 'amazonlink', 'msg' => 'El enlace de Amazon no puede superar 255 caracteres'],
        'amazonlink.url'       => ['code' => 'amazonlink', 'msg' => 'El enlace de Amazon debe ser una URL válida'],
        
        'pic.unique'           => ['code' => 'pic', 'msg' => 'Esta imagen ya existe'],
        'pic.max'              => ['code' => 'pic', 'msg' => 'El nombre de la imagen no puede superar 255 caracteres'],
        
        'price.float'          => ['code' => 'price', 'msg' => 'El precio debe ser un número decimal'],
        'price.egt'            => ['code' => 'price', 'msg' => 'El precio debe ser mayor o igual a 0'],
        
        'description.max'      => ['code' => 'description', 'msg' => 'La descripción no puede superar 65,535 caracteres'],
        
        'stock.integer'        => ['code' => 'stock', 'msg' => 'El stock debe ser un número entero'],
        'stock.egt'            => ['code' => 'stock', 'msg' => 'El stock debe ser mayor o igual a 0'],
        
        'like.integer'         => ['code' => 'like', 'msg' => 'Los likes deben ser un número entero'],
        'like.egt'             => ['code' => 'like', 'msg' => 'Los likes deben ser mayor o igual a 0'],
        
        'visible.in'           => ['code' => 'visible', 'msg' => 'El campo visible debe ser 0 o 1'],
        'available.in'         => ['code' => 'available', 'msg' => 'El campo disponible debe ser 0 o 1'],
        
        'brand_id.require'     => ['code' => 'brand_id', 'msg' => 'La marca es obligatoria'],
        'brand_id.integer'     => ['code' => 'brand_id', 'msg' => 'La marca debe ser un ID válido'],
        'brand_id.egt'         => ['code' => 'brand_id', 'msg' => 'La marca debe ser un ID válido'],
        
        'category_id.require'  => ['code' => 'category_id', 'msg' => 'La categoría es obligatoria'],
        'category_id.integer'  => ['code' => 'category_id', 'msg' => 'La categoría debe ser un ID válido'],
        'category_id.egt'      => ['code' => 'category_id', 'msg' => 'La categoría debe ser un ID válido'],
        
        'product_type_id.require' => ['code' => 'product_type_id', 'msg' => 'El tipo de producto es obligatorio'],
        'product_type_id.integer' => ['code' => 'product_type_id', 'msg' => 'El tipo de producto debe ser un ID válido'],
        'product_type_id.egt'     => ['code' => 'product_type_id', 'msg' => 'El tipo de producto debe ser un ID válido'],
        
        // Mensajes para campos meta SEO
        'title.max'            => ['code' => 'title', 'msg' => 'El título no puede superar 70 caracteres'],
        'meta_title.max'       => ['code' => 'meta_title', 'msg' => 'El meta título no puede superar 70 caracteres'],
        'meta_description.max' => ['code' => 'meta_description', 'msg' => 'La meta descripción no puede superar 160 caracteres'],
        'meta_keywords.max'    => ['code' => 'meta_keywords', 'msg' => 'Las palabras clave no pueden superar 255 caracteres'],
        'og_title.max'         => ['code' => 'og_title', 'msg' => 'El título Open Graph no puede superar 70 caracteres'],
        'og_description.max'   => ['code' => 'og_description', 'msg' => 'La descripción Open Graph no puede superar 160 caracteres'],
        'og_image.max'         => ['code' => 'og_image', 'msg' => 'La URL de la imagen Open Graph no puede superar 255 caracteres'],
        'og_image.url'         => ['code' => 'og_image', 'msg' => 'La imagen Open Graph debe ser una URL válida'],
        'og_type.max'          => ['code' => 'og_type', 'msg' => 'El tipo Open Graph no puede superar 50 caracteres'],
        'og_type.in'           => ['code' => 'og_type', 'msg' => 'El tipo Open Graph debe ser: product, article o website'],
    ];

    /**
     * Escena para actualización
     */
    public function sceneUpdate($id)
    {
        return $this->only([
            'name', 'slug', 'sku', 'asin', 'manufacturer', 'productcode', 
            'amazonlink', 'pic', 'price', 'description', 'stock', 'like', 'visible', 
            'available', 'brand_id', 'category_id', 'product_type_id',
            'title', 'meta_title', 'meta_description', 'meta_keywords',
            'og_title', 'og_description', 'og_image', 'og_type'
        ])
        ->remove('name', 'unique')
        ->remove('slug', 'unique')
        ->remove('sku', 'unique')
        ->remove('asin', 'unique')
        ->remove('manufacturer', 'unique')
        ->remove('productcode', 'unique')
        ->remove('amazonlink', 'unique')
        ->remove('pic', 'unique')
        ->append('name', 'unique:products,name,' . $id)
        ->append('slug', 'unique:products,slug,' . $id)
        ->append('sku', 'unique:products,sku,' . $id)
        ->append('asin', 'unique:products,asin,' . $id)
        ->append('manufacturer', 'unique:products,manufacturer,' . $id)
        ->append('productcode', 'unique:products,productcode,' . $id)
        ->append('amazonlink', 'unique:products,amazonlink,' . $id)
        ->append('pic', 'unique:products,pic,' . $id);
    }
}