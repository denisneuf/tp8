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
        'description',        // Descripción larga del producto (YA EXISTIA)
        'stock',
        'like',
        'visible',
        'available',
        'brand_id',
        'category_id',
        'product_type_id',
        
        // Nuevos campos meta SEO (DE LA MIGRACIÓN)
        'title',              // Title normal
        'meta_title',         // Meta title para SEO
        'meta_description',   // Meta description para SEO  
        'meta_keywords',      // Meta keywords para SEO
        'og_title',           // Open Graph title
        'og_description',     // Open Graph description
        'og_image',           // Open Graph image
        'og_type',            // Open Graph type (default: 'product')
        
        'create_time',
        'update_time',
        'delete_time',
    ];

    protected $type = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'sku' => 'string',
        'asin' => 'string',
        'pic' => 'string',
        'manufacturer' => 'string',
        'productcode' => 'string',
        'amazonlink' => 'string',
        'price' => 'float',
        'description' => 'text',        // Descripción larga
        'stock' => 'integer',
        'like' => 'integer',
        'visible' => 'boolean',
        'available' => 'boolean',
        'brand_id' => 'integer',
        'category_id' => 'integer',
        'product_type_id' => 'integer',
        'title' => 'string',
        'meta_title' => 'string',
        'meta_description' => 'string',
        'meta_keywords' => 'string',
        'og_title' => 'string',
        'og_description' => 'string',
        'og_image' => 'string',
        'og_type' => 'string',
        
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

    /**
     * Método para obtener el título SEO con fallback inteligente
     * Jerarquía: meta_title -> title -> name
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: ($this->title ?: $this->name);
    }

    /**
     * Método para obtener la descripción SEO con fallback inteligente
     * Jerarquía: meta_description -> description (recortada)
     */
    public function getSeoDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }
        // Fallback a los primeros 160 caracteres de la descripción larga
        return $this->description ? substr(strip_tags($this->description), 0, 160) : '';
    }

    /**
     * Método para obtener el título OG con fallback inteligente
     * Jerarquía: og_title -> meta_title -> title -> name
     */
    public function getOgTitle(): string
    {
        return $this->og_title ?: $this->getSeoTitle();
    }

    /**
     * Método para obtener la descripción OG con fallback inteligente
     * Jerarquía: og_description -> meta_description -> description (recortada)
     */
    public function getOgDescription(): string
    {
        return $this->og_description ?: $this->getSeoDescription();
    }

    /**
     * Método para obtener la imagen OG con fallback inteligente
     */
    public function getOgImage(): string
    {
        if ($this->og_image) {
            return $this->og_image;
        }
        // Fallback a la imagen principal del producto
        return $this->pic ? '/static/img/product/thumbnails/' . str_replace('.', '_lg.', $this->pic) : '';
    }

    /**
     * Método para obtener el tipo OG con fallback
     */
    public function getOgType(): string
    {
        return $this->og_type ?: 'product';
    }
}