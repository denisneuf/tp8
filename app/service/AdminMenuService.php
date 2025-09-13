<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\Request;
use think\facade\Cache;

class AdminMenuService
{
    protected $menuItems;
    
    public function __construct()
    {
        $this->initializeMenuItems();
    }
    
    /**
     * Inicializa los items del menú
     */
    protected function initializeMenuItems(): void
    {
        $this->menuItems = [
            "Administradores" => [
                "listar" => "user_index",
                "nuevo"  => "user_create",
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
    }
    
    /**
     * Obtiene los iconos para cada sección del menú
     */
    public function getMenuIcons(): array
    {
        return [
            "Administradores" => "bi-people",
            "Meta" => "bi-tag", 
            "Menu" => "bi-list",
            "Marcas" => "bi-bookmark",
            "Categorías" => "bi-grid",
            "Tipos de Producto" => "bi-box",
            "Producto" => "bi-cart"
        ];
    }
    
    /**
     * Obtiene los datos del menú con detección de la sección activa
     */
    public function getMenuData(): array
    {
        // Intentar obtener del cache primero
        $cacheKey = 'admin_menu_data_' . $this->getCurrentRoute();
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData) {
            return $cachedData;
        }
        
        $currentRoute = $this->getCurrentRoute();
        $activeSection = $this->findActiveSection($currentRoute);
        
        $menuData = [
            'menuItems' => $this->getMenuItems(),
            'currentRoute' => $currentRoute,
            'activeSection' => $activeSection
        ];
        
        // Cachear por 1 hora
        Cache::set($cacheKey, $menuData, 3600);
        
        return $menuData;
    }
    
    /**
     * Obtiene la ruta actual
     */
    protected function getCurrentRoute(): string
    {
        $route = Request::rule()->getName();
        return $route ?: '';
    }
    
    /**
     * Encuentra la sección activa basada en la ruta actual
     */
    protected function findActiveSection(string $currentRoute): ?string
    {
        foreach ($this->menuItems as $section => $links) {
            if (in_array($currentRoute, $links)) {
                return $section;
            }
        }
        
        return null;
    }
    
    /**
     * Obtiene todos los items del menú
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }
    
    /**
     * Limpia el cache del menú
     */
    public function clearCache(): bool
    {
        $pattern = 'admin_menu_data_*';
        return Cache::clear($pattern);
    }
    
    /**
     * Añade un nuevo item al menú dinámicamente
     */
    public function addMenuItem(string $section, array $links): bool
    {
        if (isset($this->menuItems[$section])) {
            $this->menuItems[$section] = array_merge($this->menuItems[$section], $links);
        } else {
            $this->menuItems[$section] = $links;
        }
        
        // Limpiar cache después de modificar
        $this->clearCache();
        
        return true;
    }
}