<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class CategoryFormValidator extends Validate
{
    protected $rule = [
        'txt_short'   => 'require|max:100|unique:categories',
        'slug'        => 'require|max:100|unique:categories|alphaDash',
        'txt_long'    => 'require|max:255',
        'bs_icon'     => 'max:50',
        'description' => 'max:2000',
        'pic'         => 'max:255',
        'bg'          => 'max:255',
        'visible'     => 'require|in:0,1',
    ];
    
    protected $message = [
        'txt_short.require'   => ['code' => 'txt_short', 'msg' => 'El texto corto es obligatorio'],
        'txt_short.max'       => ['code' => 'txt_short', 'msg' => 'Máximo 100 caracteres para el texto corto'],
        'txt_short.unique'    => ['code' => 'txt_short', 'msg' => 'Ya existe una categoría con ese texto corto'],
        'txt_long.require'    => ['code' => 'txt_long', 'msg' => 'El texto largo es obligatorio'],
        'txt_long.max'        => ['code' => 'txt_long', 'msg' => 'Máximo 255 caracteres para el texto largo'],
        'slug.require'        => ['code' => 'slug', 'msg' => 'El slug es obligatorio'],
        'slug.max'            => ['code' => 'slug', 'msg' => 'Máximo 100 caracteres para el slug'],
        'slug.unique'         => ['code' => 'slug', 'msg' => 'El slug ya existe'],
        'slug.alphaDash'      => ['code' => 'slug', 'msg' => 'El slug solo puede contener letras, números, guiones y guiones bajos'],
        'description.max'     => ['code' => 'description', 'msg' => 'Máximo 2000 caracteres para la descripción'],
        'pic.max'             => ['code' => 'pic', 'msg' => 'Máximo 255 caracteres para la imagen principal'],
        'bg.max'              => ['code' => 'bg', 'msg' => 'Máximo 255 caracteres para la imagen de fondo'],
        'visible.require'     => ['code' => 'visible', 'msg' => 'El campo de visibilidad es obligatorio'],
        'visible.in'          => ['code' => 'visible', 'msg' => 'El campo de visibilidad debe ser 0 o 1'],
        'bs_icon.max'         => ['code' => 'bs_icon', 'msg' => 'Máximo 50 caracteres para el icono Bootstrap'],
    ];
    
    public function sceneUpdate(int $id)
    {
        // Filtramos los campos que queremos validar
        $this->only([
            'txt_short',
            'slug',
            'txt_long',
            'bs_icon',
            'description',
            'pic',
            'bg',
            'visible'
        ]);

        // Quitamos las reglas base de txt_short y slug (que tenían unique sin id)
        $this->removeRule(['txt_short', 'slug']);

        // Añadimos las reglas correctas para update
        $this->rule([
            'txt_short' => "require|max:100|unique:categories,txt_short,{$id},id",
            'slug'      => "require|max:100|alphaDash|unique:categories,slug,{$id},id",
        ]);

        return $this;
    }

}