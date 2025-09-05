<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class CategoryFormValidator extends Validate
{
    protected $rule = [
        'bs_icon'     => 'max:55',
        'txt_short'   => 'require|max:15',
        'txt_long'    => 'require|max:35',
        'slug'        => 'require|max:55|alphaDash',
        'description' => 'max:500',
        'pic'         => 'max:35',
        'bg'          => 'max:35',
        'visible'     => 'require|in:0,1',
    ];

    protected $message = [
        'txt_short.require'   => 'El texto corto es obligatorio.',
        'txt_short.max'       => 'El texto corto no puede superar los 15 caracteres.',
        'txt_long.require'    => 'El texto largo es obligatorio.',
        'txt_long.max'        => 'El texto largo no puede superar los 35 caracteres.',
        'slug.require'        => 'El slug es obligatorio.',
        'slug.max'            => 'El slug no puede superar los 55 caracteres.',
        'slug.alphaDash'      => 'El slug solo puede contener letras, números, guiones y guiones bajos.',
        'visible.require'     => 'El campo de visibilidad es obligatorio.',
        'visible.in'          => 'El campo de visibilidad debe ser 0 o 1.',
    ];
}
