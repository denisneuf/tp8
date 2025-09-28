<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class ProductTypeFormValidator extends Validate
{
    protected $rule = [

        'name'        => 'require|max:100|unique:product_types,name^product_type_id',
        'slug'        => 'require|max:100|alphaDash|unique:product_types,slug^product_type_id',
        'bs_icon'     => 'max:50',
        'txt_short'   => 'require|max:255',
        'txt_long'    => 'max:65535',
        'description' => 'max:65535',
        'pic'         => 'max:255',
        'bg'          => 'max:255',
        'visible'     => 'boolean',
    ];

    protected $message = [
        'name.require'        => ['code' => 'name', 'msg' => 'El nombre es obligatorio'],
        'name.max'            => ['code' => 'name', 'msg' => 'El nombre no puede superar los 100 caracteres'],
        'name.unique'         => ['code' => 'name', 'msg' => 'Ya existe ese nombre'],
        'slug.require'        => ['code' => 'slug', 'msg' => 'El slug es obligatorio'],
        'slug.max'            => ['code' => 'slug', 'msg' => 'El slug no puede superar los 100 caracteres'],
        'slug.alphaDash'      => ['code' => 'slug', 'msg' => 'El slug solo puede contener letras, números y guiones'],
        'slug.unique'         => ['code' => 'slug', 'msg' => 'Este slug ya está en uso'],
        'bs_icon.max'         => ['code' => 'bs_icon', 'msg' => 'El icono no puede superar los 50 caracteres'],
        'txt_short.require'   => ['code' => 'txt_short', 'msg' => 'El texto corto es obligatorio'],
        'txt_short.max'       => ['code' => 'txt_short', 'msg' => 'El texto corto no puede superar los 255 caracteres'],
        'txt_long.max'        => ['code' => 'txt_long', 'msg' => 'El texto largo es demasiado extenso'],
        'description.max'     => ['code' => 'description', 'msg' => 'La descripción es demasiado extensa'],
        'pic.max'             => ['code' => 'pic', 'msg' => 'La URL de la imagen no puede superar los 255 caracteres'],
        'bg.max'              => ['code' => 'bg', 'msg' => 'La URL del fondo no puede superar los 255 caracteres'],
        'visible.boolean'     => ['code' => 'visible', 'msg' => 'El campo visible debe ser verdadero o falso'],
    ];

    /**
     * Escena para actualización - excluye validación unique del slug si no cambió
     */
    // Escena para actualización (ignorar el propio registro en unique)
    public function sceneUpdate(int $id)
    {


        // Filtramos los campos que queremos validar
        $this->only([
            'name',
            'slug',
            'bs_icon',
            'txt_short',
            'txt_long',
            'description',
            'pic',
            'bg',
            'visible',
        ]);

        // Quitamos las reglas base de txt_short y slug (que tenían unique sin id)
        $this->removeRule(['name', 'slug']);

        // Añadimos las reglas correctas para update
        $this->rule([
            'name' => "require|max:100|unique:product_types,name,{$id}",
            'slug' => "require|max:100|alphaDash|unique:product_types,slug,{$id}",
        ]);

        return $this;

    }
}