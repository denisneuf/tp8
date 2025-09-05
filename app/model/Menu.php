<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Menu extends Model
{
    protected $table = 'menus';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Soft Delete
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    protected $field = [
        'title',
        'url',
        'has_submenu',
        'order',
        'visible',
        'create_time',
        'update_time',
        'delete_time'
    ];

    protected $type = [
        'id'           => 'integer',
        'title'        => 'string',
        'url'          => 'string',
        'has_submenu'  => 'boolean',
        'visible'      => 'boolean',
        'order'        => 'integer',
        'create_time'  => 'datetime',
        'update_time'  => 'datetime',
        'delete_time'  => 'datetime'
    ];

    public function columns()
    {
        return $this->hasMany(MenuColumn::class, 'menu_id', 'id')->order('order', 'asc');
    }
}
