<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class LoginFormValidator extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'username' => 'require|max:50',
        'password' => 'require|min:6',
    ];
    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'username.require' => 'El nombre de usuario es obligatorio',
        'username.max'     => 'El nombre de usuario no puede exceder 50 caracteres',
        'password.require' => 'La contraseña es obligatoria',
        'password.min'     => 'La contraseña debe tener al menos 6 caracteres',
    ];
}
