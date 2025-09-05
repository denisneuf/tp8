<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Meta extends Model
{
    // Nombre de la tabla
    protected $table = 'meta';

    // Campos permitidos para inserción/actualización masiva
    protected $field = [
        'page',
        'title',
        'metatitle',
        'description',
        'keywords',
        'create_time',
        'update_time',
    ];

    // Auto escritura de timestamps
    protected $autoWriteTimestamp = 'datetime'; // Para create_time y update_time automáticos
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Tipos de campo (cast)
    protected $type = [
        'id'          => 'integer',
        'page'        => 'string',
        'title'       => 'string',
        'metatitle'   => 'string',
        'description' => 'string',
        'keywords'    => 'string',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
}
