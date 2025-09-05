<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class ProductTypeFormValidator extends Validate
{
    protected $rule = [
        'name'        => 'require|max:100',
        'slug'        => 'require|max:100|alphaDash',
        'bs_icon'     => 'max:50',
        'txt_short'   => 'max:255',
        'txt_long'    => 'max:65535',
        'description' => 'max:65535',
        //'pic'         => 'max:255|url',
        //'bg'          => 'max:255|url',
        'pic'         => 'max:255',
        'bg'          => 'max:255',
        'visible'     => 'boolean',
    ];

    protected $message = [
        'name.require'        => 'El nombre es obligatorio',
        'name.max'            => 'El nombre no puede superar los 100 caracteres',
        'slug.require'        => 'El slug es obligatorio',
        'slug.max'            => 'El slug no puede superar los 100 caracteres',
        'slug.alphaDash'      => 'El slug solo puede contener letras, números y guiones',
        'bs_icon.max'         => 'El icono no puede superar los 50 caracteres',
        'txt_short.max'       => 'El texto corto no puede superar los 255 caracteres',
        'txt_long.max'        => 'El texto largo es demasiado extenso',
        'description.max'     => 'La descripción es demasiado extensa',
        'pic.max'             => 'La URL de la imagen no puede superar los 255 caracteres',
        'pic.url'             => 'La URL de la imagen no es válida',
        'bg.max'              => 'La URL del fondo no puede superar los 255 caracteres',
        'bg.url'              => 'La URL del fondo no es válida',
        'visible.boolean'     => 'El campo visible debe ser verdadero o falso',
    ];
}