<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class MenuLink extends Model
{
    protected $table = 'menu_links';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // Soft Delete
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    protected $field = [
        'column_id',
        'title',
        'url',
        'order',
        'create_time',
        'update_time',
        'delete_time'
    ];

    protected $type = [
        'id'           => 'integer',
        'column_id'    => 'integer',
        'title'        => 'string',
        'url'          => 'string',
        'order'        => 'integer',
        'create_time'  => 'datetime',
        'update_time'  => 'datetime',
        'delete_time'  => 'datetime'
    ];

    public function column()
    {
        return $this->belongsTo(MenuColumn::class, 'column_id', 'id');
    }
}
