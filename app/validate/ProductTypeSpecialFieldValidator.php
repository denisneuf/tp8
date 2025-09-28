<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class ProductTypeSpecialFieldValidator extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require|max:100|unique:product_special_fields,name^product_type_id',
        'slug' => 'require|max:100|alphaDash|unique:product_special_fields,slug^product_type_id',
        'data_type' => 'require|in:string,integer,float,boolean',
        'unit' => 'max:20',
        'required' => 'boolean',
        //'product_type_id' => 'require|integer|exists:product_types,id'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */


    protected $message = [
        'name.require'      => ['code' => 'name', 'msg' => 'El nombre del campo es obligatorio'],
        'name.max'          => ['code' => 'name', 'msg' => 'El nombre no puede superar los 100 caracteres'],
        'name.unique'          => ['code' => 'name', 'msg' => 'Ya existe un campo con este nombre para este tipo de producto'],
        'slug.require'      => ['code' => 'slug', 'msg' => 'El slug del campo es obligatorio'],
        'slug.max'          => ['code' => 'slug', 'msg' => 'El slug no puede superar los 100 caracteres'],
        'slug.alphaDash'    => ['code' => 'slug', 'msg' => 'El slug solo puede contener letras, números y guiones'],
        'slug.unique'       => ['code' => 'slug', 'msg' => 'Este slug ya está en uso para otro campo'],
        'data_type.require' => ['code' => 'data_type', 'msg' => 'El tipo de dato es obligatorio'],
        'data_type.in'      => ['code' => 'data_type', 'msg' => 'El tipo de dato debe ser: texto, entero, decimal o booleano'],
        'unit.max'          => ['code' => 'unit', 'msg' => 'La unidad no puede superar los 20 caracteres'],
        'required.boolean'  => ['code' => 'required', 'msg' => 'El campo requerido debe ser verdadero o falso'],
    ];


    // Escena para actualización (ignorar el propio registro en unique)
    public function sceneUpdate(int $id)
    {


        // Filtramos los campos que queremos validar
        $this->only([
            'name',
            'slug',
            'data_type',
            'unit',
            'required',
        ]);

        // Quitamos las reglas base de txt_short y slug (que tenían unique sin id)
        $this->removeRule(['name', 'slug']);

        // Añadimos las reglas correctas para update
        $this->rule([
            'name' => "require|max:100|unique:product_special_fields,name,{$id},id",
            'slug'     => "require|max:100|alphaDash|unique:product_special_fields,slug,{$id},id",
        ]);

        return $this;

    }
}
