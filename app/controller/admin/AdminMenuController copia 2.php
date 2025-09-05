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
            "nuevo"  => "user_create",
            //"editar"  => "user_edit", //problem id
        ],
        "Meta" => [
            "listar" => "meta_index",
            "nuevo"  => "meta_create"
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

