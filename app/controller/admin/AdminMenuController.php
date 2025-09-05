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
        ],
        "Menu" => [
            "listar" => "menu_index",
            "nuevo"  => "menu_create"
        ],
        "Marcas" => [
            "listar" => "brand_index",
            "nuevo"  => "brand_create"
        ],
        "Categorías" => [
            "listar" => "category_index",
            "nuevo"  => "category_create"
        ],
        "Tipos de Producto" => [
            "listar" => "product_type_index",
            "nuevo"  => "product_type_create"
        ],
        "Producto" => [
            "listar" => "product_index",
            "nuevo"  => "product_create"
        ]
    ];

    public function initialize()
    {
        $currentRoute = app('request')->rule()->getName();
        $activeSection = null;

        foreach ($this->menuItems as $section => $links) {
            foreach ($links as $label => $route) {
                if ($currentRoute === $route) {
                    $activeSection = $section;
                    break 2;
                }
            }
        }

        View::assign([
            'menuItems'     => $this->menuItems,
            'currentRoute'  => $currentRoute,
            'activeSection' => $activeSection
        ]);
    }

    /*
    public function initialize()
    {
        $currentRoute = app('request')->rule()->getName();
        $activeSection = null;

        // Detectar sección activa por coincidencia de prefijo
        // esto falla en product_type_index o product_index
        foreach ($this->menuItems as $section => $links) {
            foreach ($links as $label => $route) {
                $prefix = explode('_', $route)[0]; // ej: 'user' de 'user_index'
                if (strpos($currentRoute, $prefix . '_') === 0) {
                    $activeSection = $section;
                    break 2;
                }
            }
        }

        View::assign([
            'menuItems'     => $this->menuItems,
            'currentRoute'  => $currentRoute,
            'activeSection' => $activeSection
        ]);
    }
    */

}

