<?php

namespace app\controller;

use app\BaseController;
use think\facade\Env;
use app\model\Menu;
use app\model\Brand;

class Index extends BaseController
{
    public function index()
    {

        $brands = Brand::select();

        //dump($brands);

        // Cargar todos los menús visibles con sus columnas y enlaces
        $menus = Menu::where('visible', true)
            ->order('order', 'asc')
            ->with(['columns.links'])
            ->select();

        //dump($menus);

        return view('./index/index', [
            'lang'  => Env::get('lang.default_lang'),
            'menus' => $menus,
            'brands' => $brands,
        ]);
    }

    public function hello($name = 'ThinkPHP8')
    {
        return 'hello,' . $name;
    }
}
