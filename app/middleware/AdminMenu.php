<?php
declare(strict_types=1);

namespace app\middleware;

use think\facade\View;
use app\service\AdminMenuService;

class AdminMenu
{
    public function handle($request, \Closure $next)
    {
        // Solo aplicar a rutas de administración
        if (strpos($request->pathinfo(), 'admin/') === 0) {
            $menuService = new AdminMenuService();
            $menuData = $menuService->getMenuData();
            
            // Asignar variables a todas las vistas
            View::assign([
                'menuItems' => $menuData['menuItems'],
                'currentRoute' => $menuData['currentRoute'],
                'activeSection' => $menuData['activeSection'],
                'menuIcons' => $menuService->getMenuIcons()
            ]);
        }
        
        return $next($request);
    }
}