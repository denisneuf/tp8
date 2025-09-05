<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    // Habilitar timestamps automáticos
    protected $autoWriteTimestamp = true;

    // Formato de los campos datetime
    protected $dateFormat = 'Y-m-d H:i:s';

    // Campos de timestamp personalizados
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Soft Delete
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // Nombre de la tabla (opcional si coincide con el nombre del modelo en minúsculas)
    protected $table = 'users';

    // Campos que pueden asignarse en masa (opcional, para seguridad)
    protected $field = [
        'pid',
        'username',
        'password',
        'nickname',
        'lastip',
        'loginnum',
        'email',
        'mobile',
        'islock',
        'create_time',
        'update_time',
        'delete_time', // ← nuevo campo
    ];

    protected $type = [
        'pid'         => 'integer',
        'username'    => 'string',
        'password'    => 'string',
        'nickname'    => 'string',
        'lastip'      => 'string',
        'loginnum'    => 'integer',
        'email'       => 'string',
        'mobile'      => 'string',
        'islock'      => 'boolean',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time'  => 'datetime', // ← nuevo tipo
    ];

    // Si quieres ocultar campos al convertir a array/json
    protected $hidden = ['password'];
}