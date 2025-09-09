<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class BrandFormValidator extends Validate
{
    protected $rule = [
        'brand_en'          => 'require|max:100|unique:brands',
        'slug'              => 'require|max:100|unique:brands',
        'brand_cn'          => 'max:100',
        'meta_title'        => 'max:255',
        'meta_description'  => 'max:1000',
        'keywords'          => 'max:255',
        'txt_description'   => 'max:2000',
        'block_description' => 'max:2000',
        'pic'               => 'max:255',
        'block_pic'         => 'max:255',
        'email'             => 'email|max:100',
        'telephone'         => 'max:50',
        'direccion'         => 'max:255',
        'fax'               => 'max:50',
        'web'               => 'url|max:255',
    ];

    protected $message = [
        'brand_en.require'         => 'El nombre en inglés es obligatorio',
        'brand_en.max'             => 'Máximo 100 caracteres para el nombre en inglés',
        'brand_en.unique'          => 'Ya existe una marca con ese nombre en inglés',
        'slug.require'             => 'El slug es obligatorio',
        'slug.max'                 => 'Máximo 100 caracteres para el slug',
        'brand_cn.max'             => 'Máximo 100 caracteres para el nombre en chino',
        'meta_title.max'           => 'Máximo 255 caracteres para el título SEO',
        'meta_description.max'     => 'Máximo 1000 caracteres para la descripción SEO',
        'keywords.max'             => 'Máximo 255 caracteres para las palabras clave',
        'txt_description.max'      => 'Máximo 2000 caracteres para la descripción larga',
        'block_description.max'    => 'Máximo 2000 caracteres para la descripción destacada',
        'pic.max'                  => 'Máximo 255 caracteres para la imagen principal',
        'block_pic.max'            => 'Máximo 255 caracteres para la imagen destacada',
        'email.email'              => 'Formato de email inválido',
        'email.max'                => 'Máximo 100 caracteres para el email',
        'telephone.max'            => 'Máximo 50 caracteres para el teléfono',
        'direccion.max'            => 'Máximo 255 caracteres para la dirección',
        'fax.max'                  => 'Máximo 50 caracteres para el fax',
        'web.url'                  => 'Formato de URL inválido',
        'web.max'                  => 'Máximo 255 caracteres para el sitio web',
    ];
}
