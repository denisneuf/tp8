<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Meta extends Model
{

    use SoftDelete;
    // Nombre de la tabla
    protected $table = 'meta';

    // Campos permitidos para inserción/actualización masiva
    protected $field = [
        'page',
        'title',
        'metatitle',
        'description',
        'keywords',
        // Open Graph fields
        'og_title',
        'og_description', 
        'og_image',
        'og_type',
        'create_time',
        'update_time',
        'delete_time',
    ];

    // Auto escritura de timestamps
    protected $autoWriteTimestamp = 'datetime'; // Para create_time y update_time automáticos
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Campo para soft delete
    protected $deleteTime = 'delete_time';

    // Tipos de campo (cast)
    protected $type = [
        'id'          => 'integer',
        'page'        => 'string',
        'title'       => 'string',
        'metatitle'   => 'string',
        'description' => 'string',
        'keywords'    => 'string',
        // Open Graph fields
        'og_title'      => 'string',
        'og_description' => 'string',
        'og_image'      => 'string',
        'og_type'       => 'string',        
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time'  => 'datetime',
    ];
}
