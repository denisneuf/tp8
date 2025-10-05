<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\View;

class MetaService
{
    /**
     * Asigna metadatos para productos y detecta si ALGÚN campo se generó por defecto
     */
    public function assignProductMeta($product): void
    {
        // Verificar cada campo individualmente
        $isTitleDefault = $this->isEmpty($product->meta_title) && $this->isEmpty($product->title);
        $isMetaTitleDefault = $this->isEmpty($product->meta_title);
        $isDescriptionDefault = $this->isEmpty($product->meta_description);
        $isKeywordsDefault = $this->isEmpty($product->meta_keywords);
        $isOgTitleDefault = $this->isEmpty($product->og_title);
        $isOgDescriptionDefault = $this->isEmpty($product->og_description);
        $isOgImageDefault = $this->isEmpty($product->og_image);
        
        // CORREGIDO: 'product' es el valor correcto para productos, no lo consideramos por defecto
        $isOgTypeDefault = $this->isEmpty($product->og_type);

        // Si AL MENOS UN campo usa valor por defecto, marcamos como true
        $anyFieldDefault = $isTitleDefault || $isMetaTitleDefault || $isDescriptionDefault || 
                          $isKeywordsDefault || $isOgTitleDefault || $isOgDescriptionDefault || 
                          $isOgImageDefault || $isOgTypeDefault;

        $metaData = [
            'title'       => $product->getSeoTitle(),
            'metatitle'   => $product->meta_title ?: $product->getSeoTitle(),
            'description' => $product->getSeoDescription(),
            'keywords'    => $product->meta_keywords ?: $this->generateProductKeywords($product),
            'og_title'    => $product->getOgTitle(),
            'og_description' => $product->getOgDescription(),
            'og_image'    => $product->getOgImage(),
            'og_type'     => $product->getOgType(),
            'defaultmeta' => $anyFieldDefault,
            
            // Flags individuales para debugging
            'meta_flags' => [
                'default_title' => $isTitleDefault,
                'default_metatitle' => $isMetaTitleDefault,
                'default_description' => $isDescriptionDefault,
                'default_keywords' => $isKeywordsDefault,
                'default_og_title' => $isOgTitleDefault,
                'default_og_description' => $isOgDescriptionDefault,
                'default_og_image' => $isOgImageDefault,
                'default_og_type' => $isOgTypeDefault,
            ]
        ];

        View::assign($metaData);
    }

    /**
     * Verifica si un valor está realmente vacío (más estricto que empty())
     */
    private function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }
        
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' || $value === '0';
        }
        
        return empty($value);
    }

    /**
     * Genera keywords automáticamente para productos
     */
    private function generateProductKeywords($product): string
    {
        $keywords = [];
        
        $keywords[] = $product->name;
        
        if ($product->brand) {
            $keywords[] = $product->brand->brand_cn;
        }
        
        if ($product->category) {
            $keywords[] = $product->category->name;
        }
        
        if ($product->manufacturer) {
            $keywords[] = $product->manufacturer;
        }

        return implode(', ', array_slice($keywords, 0, 10));
    }
}