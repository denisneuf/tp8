<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use think\facade\View;
use app\BaseController;

class AdminMenuController extends BaseController
{
    protected $menuItems = [
        "Administradores" => [
            "listar" => "user_index",
            "nuevo"  => "user_create"
        ],
        "Meta" => [
            "listar" => "/admin/meta",
            "nuevo"  => "/admin/meta/create"
        ]
    ];

    public function initialize()
    {

        $currentRoute = app('request')->rule()->getName();
        View::assign([
            'menuItems'    => $this->menuItems,
            'currentRoute' => $currentRoute
        ]);

    }
}

