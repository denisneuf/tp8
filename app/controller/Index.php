<?php
namespace app\controller;

use app\BaseController;
use think\response\View;
use think\facade\Env;
use app\model\Menu;
use app\model\Brand;
use app\model\Category;
use app\model\Product;
use app\model\ProductType;

class Index extends BaseController
{
    /**
     * Obtener menús con estructura completa
     */
    private function getMenus()
    {
        return Menu::where('visible', true)
            ->order('order', 'asc')
            ->with(['columns.links'])
            ->select();
    }

    /**
     * Página principal
     */
    public function index(): View
    {
        $brands = Brand::where('visible', true)
            ->limit(12)
            ->select();

        $featuredProducts = Product::where('visible', true)
            ->where('available', true)
            ->with(['brand', 'category'])
            ->order('like', 'desc')
            ->limit(8)
            ->select();

        return view('./index/index', [
            'lang' => Env::get('lang.default_lang'),
            'menus' => $this->getMenus(),
            'brands' => $brands,
            'featuredProducts' => $featuredProducts
        ]);
    }

    /**
     * Lista de todas las marcas
     */
    public function brands(): View
    {
        $brands = Brand::where('visible', true)
            ->order('brand_cn', 'asc')
            ->select();

        return view('/index/brands', [
            'menus' => $this->getMenus(),
            'brands' => $brands
        ]);
    }

    /**
     * Detalle de marca con sus productos
     */
    public function brand(string $slug): View
    {
        $brand = Brand::where('slug', $slug)
            ->with(['products' => function($query) {
                $query->where('visible', true)
                      ->where('available', true)
                      ->with(['category', 'productType']);
            }])
            ->find();

        if (!$brand) {
            abort(404, 'Marca no encontrada');
        }

        // Agrupar productos por categoría para el menú lateral
        $categories = Category::where('visible', true)
            ->has('products')
            ->with(['products' => function($query) use ($brand) {
                $query->where('brand_id', $brand->id)
                      ->where('visible', true)
                      ->where('available', true);
            }])
            ->select();

        return view('/index/brand', [
            'menus' => $this->getMenus(),
            'brand' => $brand,
            'categories' => $categories
        ]);
    }

    /**
     * Lista de categorías
     */
    public function categories(): View
    {
        $categories = Category::where('visible', true)
            ->with(['products' => function($query) {
                $query->where('visible', true)
                      ->where('available', true)
                      ->limit(5);
            }])
            ->select();

        return view('/index/categories', [
            'menus' => $this->getMenus(),
            'categories' => $categories
        ]);
    }

    /**
     * Productos por categoría
     */
    public function category(string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->with(['products' => function($query) {
                $query->where('visible', true)
                      ->where('available', true)
                      ->with(['brand', 'productType']);
            }])
            ->find();

        if (!$category) {
            abort(404, 'Categoría no encontrada');
        }

        return view('/index/category', [
            'menus' => $this->getMenus(),
            'category' => $category
        ]);
    }

    /**
     * Detalle de producto
     */
    public function product(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->where('visible', true)
            ->with(['brand', 'category', 'productType', 'attributes'])
            ->find();

        if (!$product) {
            abort(404, 'Producto no encontrado');
        }

        // Productos relacionados
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('visible', true)
            ->where('available', true)
            ->where('id', '<>', $product->id)
            ->with(['brand'])
            ->limit(4)
            ->select();

        return view('/index/product', [
            'menus' => $this->getMenus(),
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }
}