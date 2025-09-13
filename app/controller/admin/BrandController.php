<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\View;
use think\facade\Session;
use think\facade\Validate;
use think\Request;
use app\model\Brand;
use app\validate\BrandFormValidator;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use app\service\ImageService;
use think\response\Redirect;
use app\BaseController;

/**
 * Controlador para la gestión de marcas en el panel de administración
 * 
 * @package app\controller\admin
 */
class BrandController extends BaseController
{
    /**
     * @var BrandFormValidator
     */
    private BrandFormValidator $brandValidator;
    /**
     * @var ImageService
     */
    protected ImageService $imageService;
    /**
     * @var AdminMenuService
     */

    /**
     * Constructor del controlador
     *
     * @param BrandFormValidator $brandValidator
     */
    public function initialize(): void
    {
        parent::initialize();
        // Usar el contenedor de dependencias de ThinkPHP
        $this->brandValidator = app(BrandFormValidator::class);
        $this->imageService = app(ImageService::class); // Inyectar el servicio
    }

    /**
     * Muestra el listado de marcas con paginación
     * 
     * @return \think\response\View
     */
    public function index(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $brands = Brand::withTrashed()->order('id desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        //$menuData = $this->menuService->getMenuData();
        //View::assign('menuItems', $menuData['menuItems']);
        //View::assign('brands', $brands);
        //View::assign('success', $successMessage);
        //View::assign('error', $errorMessage);


        View::assign([
            'brands' => $brands,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);


        return View::fetch('admin/brand/list');
    }

    /**
     * Muestra el formulario para crear una nueva marca
     * 
     * @return \think\response\View
     */
    public function create(): string
    {
        return View::fetch('admin/brand/create');
    }

    /**
     * Guarda una nueva marca en la base de datos
     * 
     * @param Request $request Solicitud HTTP con los datos del formulario
     * @return \think\response\Redirect
     */
    public function save(Request $request): Redirect
    {
        $data = $request->post();

        // Usar el validador inyectado en lugar de crear una nueva instancia
        if (!$this->brandValidator->check($data)) {
            return redirect((string) url('brand_create'))->with('error', $this->brandValidator->getError());
        }

        try {
            Brand::create($data);
            return redirect((string) url('brand_index'))->with('success', 'Marca creada correctamente.');
        } catch (\Exception $e) {
            return redirect((string) url('brand_create'))->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar una marca existente
     * 
     * @param int $id ID de la marca a editar
     * @return \think\response\View|\think\response\Redirect
     */
    public function edit(int $id): string|Redirect
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        try {
            $brand = Brand::findOrFail($id);
            View::assign('success', $successMessage);
            View::assign('error', $errorMessage);
            View::assign('brand', $brand);
            return View::fetch('admin/brand/edit');
            
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('brand_index'))->with('error', $e->getMessage());
        }
    }

    /**
     * Obtiene las reglas de validación para actualizar una marca
     * 
     * Las reglas incluyen validación condicional para unicidad del nombre en inglés
     * solo cuando el campo ha cambiado respecto al valor original.
     * 
     * @param array $data Datos del formulario a validar
     * @param Brand $brand Instancia de la marca existente
     * @param int $id ID de la marca que se está actualizando
     * @return array<string, array<string>> Array de reglas de validación
     * @throws \InvalidArgumentException Si los datos no contienen los campos necesarios
     */
    private function getUpdateValidationRules(array $data, Brand $brand, int $id): array
    {
        // Validar que los datos necesarios estén presentes
        if (!isset($data['brand_en'])) {
            throw new \InvalidArgumentException('El campo brand_en es requerido para la validación');
        }

        $rules = [
            'brand_en' => ['require', 'max:100'],
            'slug'     => ['require', 'max:100', 'unique:brands,slug,' . $id],
            'email'    => ['email'],
            'web'      => ['url'],
            'brand_cn' => ['max:100'],
            'meta_title' => ['max:255'],
            'meta_description' => ['max:1000'],
            'keywords' => ['max:255'],
            'txt_description' => ['max:2000'],
            'block_description' => ['max:2000'],
            'pic' => ['max:255'],
            'block_pic' => ['max:255'],
            'telephone' => ['max:50'],
            'direccion' => ['max:255'],
            'fax' => ['max:50'],
        ];
        
        /**
         * Añadir regla de unicidad condicional para brand_en
         * Solo se valida la unicidad si el valor ha cambiado respecto al original
         * Esto evita errores de validación cuando el usuario no modifica el nombre
         */
        if ($data['brand_en'] !== $brand->brand_en) {
            $rules['brand_en'][] = 'unique:brands,brand_en';
        }
        
        return $rules;
    }

    /**
     * Actualiza una marca existente en la base de datos
     * 
    * @param Request $request Solicitud HTTP con los datos del formulario
    * @param int $id ID de la marca a actualizar
    * @return \think\response\Redirect Redirección con mensaje de éxito o error
    * 
    * @throws \think\db\exception\DataNotFoundException Si la marca no existe
    * @throws \think\db\exception\ModelNotFoundException Si la marca no existe
    * @throws \RuntimeException Si ocurre un error durante el procesamiento de imágenes
    * @throws \InvalidArgumentException Si los datos de validación son incorrectos
     */


    public function update(Request $request, int $id): Redirect
    {
        /** @var Brand $brand Instancia de la marca a actualizar */
        $brand = Brand::findOrFail($id);
        $data = $request->post();

        // OBTENER ARCHIVOS AL INICIO (antes de cualquier procesamiento)
        $file = null;
        if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
            $file = $request->file('pic');
        }

        $bg = null;
        if (isset($_FILES['block_pic']) && $_FILES['block_pic']['error'] === UPLOAD_ERR_OK) {
            $bg = $request->file('block_pic');
        }

        // Definir paths una sola vez
        $basePath = app()->getRootPath() . 'public/static/img/';
        $topicPath = $basePath . 'brand/';

        try {
            // Inicializar campos de eliminación si no existen
            $data['delete_pic'] = $data['delete_pic'] ?? '0';
            $data['delete_block_pic'] = $data['delete_block_pic'] ?? '0';

            // Obtener reglas de validación específicas para actualización
            $rules = $this->getUpdateValidationRules($data, $brand, $id);
            $this->brandValidator->rule($rules);
            
            if (!$this->brandValidator->check($data)) {
                return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $this->brandValidator->getError());
            }

            // LÓGICA PARA LA IMAGEN PRINCIPAL (pic)
            if ($data['delete_pic'] == '1') {
                // ELIMINAR imagen existente
                if ($brand->pic && file_exists($topicPath . $brand->pic)) {
                    $this->imageService->deleteBrandImage($brand->pic, $topicPath);
                }
                $data['pic'] = null;
                
            } else {
                // Verificar si hay nuevo archivo (ya obtenido al inicio)
                if ($file) {
                    try {
                        $data['pic'] = $this->imageService->processBrandLogo(
                            $file, 
                            $data['brand_en'], 
                            $brand->pic
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // LÓGICA PARA LA IMAGEN DE BLOQUE (block_pic)
            if ($data['delete_block_pic'] == '1') {
                // ELIMINAR imagen existente
                if ($brand->block_pic && file_exists($topicPath . $brand->block_pic)) {
                    $this->imageService->deleteBrandImage($brand->block_pic, $topicPath);
                }
                $data['block_pic'] = null;
                
            } else {
                // Verificar si hay nuevo archivo (ya obtenido al inicio)
                if ($bg) {
                    try {
                        $data['block_pic'] = $this->imageService->processBrandBlockImage(
                            $bg, 
                            $data['brand_en'], 
                            $brand->block_pic
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // Actualizar marca en la base de datos
            Brand::update($data, ['id' => $id]);
            return redirect((string) url('brand_index'))->with('success', 'Marca actualizada correctamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
        }
    }

    /**
     * Elimina una marca (soft delete)
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la marca a eliminar
     * @return \think\response\Redirect
     */
    public function delete(Request $request, int $id): Redirect
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return redirect((string) url('brand_index'))->with('success', 'Marca eliminada correctamente.');
    }

    /**
     * Restaura una marca previamente eliminada
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la marca a restaurar
     * @return \think\response\Redirect
     */
    public function restore(Request $request, int $id): Redirect
    {
        $brand = Brand::onlyTrashed()->findOrFail($id);
        $brand->restore();
        return redirect((string) url('brand_index'))->with('success', 'Marca restaurada correctamente.');
    }
}