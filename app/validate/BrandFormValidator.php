<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class BrandFormValidator extends Validate
{
    protected $rule = [
        'brand_en'          => 'require|max:30|unique:brands',
        'slug'              => 'require|max:50|unique:brands|alphaDash',
        'brand_cn'          => 'max:100',
        'meta_title'        => 'max:255',
        'meta_description'  => 'max:1000',
        'keywords'          => 'max:255',
        'txt_description'   => 'max:2000',
        'block_description' => 'max:2000',
        'pic'               => 'max:255',
        'block_pic'         => 'max:255',
        'visible'     => 'require|in:0,1',
        'email'             => 'email|max:100',
        'telephone'         => 'max:50',
        'direccion'         => 'max:255',
        'fax'               => 'max:50',
        'web'               => 'url|max:255',
    ];

    protected $message = [
        'brand_en.require'         => ['code' => 'brand_en', 'msg' => 'El nombre en inglés es obligatorio'],
        'brand_en.max'             => ['code' => 'brand_en', 'msg' => 'Máximo 30 caracteres para el nombre en inglés'],
        'brand_en.unique'          => ['code' => 'brand_en', 'msg' => 'Ya existe una marca con ese nombre en inglés'],
        'slug.require'             => ['code' => 'slug', 'msg' => 'El slug es obligatorio'],
        'slug.max'                 => ['code' => 'slug', 'msg' => 'Máximo 50 caracteres para el slug'],
        'slug.unique'              => ['code' => 'slug', 'msg' => 'El slug ya existe'],
        'slug.alphaDash'           => ['code' => 'slug', 'msg' => 'El slug solo puede contener letras, números, guiones y guiones bajos'],
        'brand_cn.max'             => ['code' => 'brand_cn', 'msg' => 'Máximo 100 caracteres para el nombre en chino'],
        'meta_title.max'           => ['code' => 'meta_title', 'msg' => 'Máximo 255 caracteres para el título SEO'],
        'meta_description.max'     => ['code' => 'meta_description', 'msg' => 'Máximo 1000 caracteres para la descripción SEO'],
        'keywords.max'             => ['code' => 'keywords', 'msg' => 'Máximo 255 caracteres para las palabras clave'],
        'txt_description.max'      => ['code' => 'txt_description', 'msg' => 'Máximo 2000 caracteres para la descripción larga'],
        'block_description.max'    => ['code' => 'block_description', 'msg' => 'Máximo 2000 caracteres para la descripción destacada'],
        'pic.max'                  => ['code' => 'pic', 'msg' => 'Máximo 255 caracteres para la imagen principal'],
        'block_pic.max'            => ['code' => 'block_pic', 'msg' => 'Máximo 255 caracteres para la imagen destacada'],
        'visible.require'     => ['code' => 'visible', 'msg' => 'El campo de visibilidad es obligatorio'],
        'visible.in'          => ['code' => 'visible', 'msg' => 'El campo de visibilidad debe ser 0 o 1'],
        'email.email'              => ['code' => 'email', 'msg' => 'Formato de email inválido'],
        'email.max'                => ['code' => 'email', 'msg' => 'Máximo 100 caracteres para el email'],
        'telephone.max'            => ['code' => 'telephone', 'msg' => 'Máximo 50 caracteres para el teléfono'],
        'direccion.max'            => ['code' => 'direccion', 'msg' => 'Máximo 255 caracteres para la dirección'],
        'fax.max'                  => ['code' => 'fax', 'msg' => 'Máximo 50 caracteres para el fax'],
        'web.url'                  => ['code' => 'web', 'msg' => 'Formato de URL inválido'],
        'web.max'                  => ['code' => 'web', 'msg' => 'Máximo 255 caracteres para el sitio web'],
    ];

    public function sceneUpdate(int $id)
    {
        // Filtramos los campos que queremos validar
        $this->only([
            'brand_en',
            'slug',
            'brand_cn',
            'meta_title',
            'meta_description',
            'keywords',
            'txt_description',
            'block_description',
            'pic',
            'block_pic',
            'visible',
            'email',
            'telephone',
            'direccion',
            'fax',
            'web'
        ]);

        // Quitamos las reglas base de txt_short y slug (que tenían unique sin id)
        $this->removeRule(['brand_en', 'slug']);

        // Añadimos las reglas correctas para update
        $this->rule([
            'brand_en' => "require|max:30|unique:brands,brand_en,{$id},id",
            'slug'     => "require|max:50|alphaDash|unique:brands,slug,{$id},id",
        ]);

        return $this;
    }

}
