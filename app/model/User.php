<?php
declare(strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    use SoftDelete;

    // Habilitar timestamps automáticos
    protected $autoWriteTimestamp = true;

    // Formato de los campos datetime
    protected $dateFormat = 'Y-m-d H:i:s';

    // Campos de timestamp personalizados
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    // Nombre de la tabla (opcional si coincide con el nombre del modelo en minúsculas)
    protected $table = 'users';

    // Campos asignables en masa
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
        'delete_time',
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
        'delete_time' => 'datetime',
    ];

    // Ocultar password al serializar
    protected $hidden = ['password'];

    // -----------------------------
    // Métodos personalizados
    // -----------------------------

    /**
     * Encuentra un usuario activo (no bloqueado) por su nombre de usuario.
     *
     * @param string $username
     * @return static|null
     */
    public static function findActiveByUsername(string $username): ?self
    {
        return self::where('username', $username)
                   ->where('islock', 0)
                   ->find();
    }

    /**
     * Verifica la contraseña contra el hash almacenado.
     *
     * @param string $plainPassword
     * @return bool
     */
    public function checkPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

}