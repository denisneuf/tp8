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
        'name.require' => 'El nombre del campo es obligatorio',
        'name.max' => 'El nombre no puede superar los 100 caracteres',
        'name.unique' => 'Ya existe un campo con este nombre para este tipo de producto',
        'slug.require' => 'El slug es obligatorio',
        'slug.max' => 'El slug no puede superar los 100 caracteres',
        'slug.alphaDash' => 'El slug solo puede contener letras, números y guiones',
        'slug.unique' => 'Ya existe un campo con este slug para este tipo de producto',
        'data_type.require' => 'El tipo de dato es obligatorio',
        'data_type.in' => 'El tipo de dato debe ser: string, integer, float o boolean',
        'unit.max' => 'La unidad no puede superar los 20 caracteres',
        'required.boolean' => 'El campo requerido debe ser verdadero o falso',
        //'product_type_id.require' => 'El tipo de producto es obligatorio',
        //'product_type_id.exists' => 'El tipo de producto no existe'
    ];

    // Escena para actualización (ignorar el propio registro en unique)
    public function sceneUpdate()
    {
        return $this->remove('name', 'unique')
                    ->remove('slug', 'unique');
    }
}
