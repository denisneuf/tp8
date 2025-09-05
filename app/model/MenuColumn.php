<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class MenuColumn extends Model
{
    protected $table = 'menu_columns';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Soft Delete
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    protected $field = [
        'menu_id',
        'title',
        'order',
        'create_time',
        'update_time',
        'delete_time'
    ];

    protected $type = [
        'id'           => 'integer',
        'menu_id'      => 'integer',
        'title'        => 'string',
        'order'        => 'integer',
        'create_time'  => 'datetime',
        'update_time'  => 'datetime',
        'delete_time'  => 'datetime'
    ];


    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function links()
    {
        return $this->hasMany(MenuLink::class, 'column_id', 'id')->order('order', 'asc');
    }
}
