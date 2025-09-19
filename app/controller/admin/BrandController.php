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

        View::assign([
            'brands' => $brands,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);


        return View::fetch('/admin/brand/list');
    }

    /**
     * Muestra el formulario para crear una nueva marca
     * 
     * @return \think\response\View
     */
    public function create(): string
    {

        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        View::assign([
            'old_data' => $oldData,
            'error_field' => $errorField,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);
        return View::fetch('/admin/brand/create');
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
        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->brandValidator->scene('save')->check($data)) {

            $error = $this->brandValidator->getError(); // Esto devuelve un array en ThinkPHP 8
            // Devuelve ['code' => campo, 'msg' => mensaje]
            $errorField = $error['code'];
            $errorMessage = $error['msg'];
            // Limpiar solo el campo con error
            $cleanData = $data;
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('brand_create'))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        // 2. LUEGO procesar archivos (DENTRO del try-catch)
        try {
            // OBTENER ARCHIVOS
            $file = null;
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
                $file = $request->file('pic');
            }

            $bg = null;
            if (isset($_FILES['block_pic']) && $_FILES['block_pic']['error'] === UPLOAD_ERR_OK) {
                $bg = $request->file('block_pic');
            }

            // Definir paths
            $basePath = app()->getRootPath() . 'public/static/img/';
            $topicPath = $basePath . 'brand/';

            // LÓGICA PARA LA IMAGEN PRINCIPAL (pic)
            if ($file) {
                $this->imageService->setImageMinDimension(800);
                try {
                    $data['pic'] = $this->imageService->processSquareImage(
                        $file, 
                        $topicPath, 
                        $data['brand_en'], 
                        null
                    );
                } catch (\RuntimeException $e) {
                    return redirect((string) url('brand_create'))
                        ->with('old_data', $data)
                        ->with('error', $e->getMessage());
                }
            } else {
                $data['pic'] = null;
            }

            // LÓGICA PARA LA IMAGEN DE BLOQUE (block_pic)
            if ($bg) {
                try {
                    $data['block_pic'] = $this->imageService->processLandscapeImage(
                        $bg, 
                        $topicPath, 
                        $data['brand_en'], 
                        null
                    );
                } catch (\RuntimeException $e) {
                    return redirect((string) url('brand_create'))
                        ->with('old_data', $data)
                        ->with('error', $e->getMessage());
                }
            } else {
                $data['block_pic'] = null;
            }

            // Crear la marca en la base de datos
            Brand::create($data);
            return redirect((string) url('brand_index'))->with('success', 'Marca creada correctamente.');

        } catch (\Exception $e) {
            // Esto solo captura errores inesperados
            return redirect((string) url('brand_create'))
                ->with('old_data', $data)
                ->with('error', 'Error inesperado: ' . $e->getMessage());
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
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        try {
            $brand = Brand::findOrFail($id);
            View::assign('error', $errorMessage);
            View::assign('brand', $brand);
            View::assign('old_data', $oldData);
            View::assign('error_field', $errorField);
            return View::fetch('admin/brand/edit');
            
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('brand_index'))->with('error', $e->getMessage());
        }
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


    public function update(Request $request, int $id)
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

        if (!$this->brandValidator->scene('update')->check($data)) {

            $error = $this->brandValidator->getError();
            $errorField = $error['code'];
            $errorMessage = $error['msg'];


            // Limpiar solo el campo con error
            $cleanData = $data;
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('brand_edit', ['id' => $id]))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }


        // Luego validar manualmente la unicidad solo si cambió
        
        if ($data['brand_en'] !== $brand->brand_en) {
            $exists = \app\model\Brand::where('brand_en', $data['brand_en'])
                ->where('id', '<>', $id)
                ->find();
            
            if ($exists) {

                // Limpiar solo el campo con error
                $cleanData = $data;
                $error_content = $cleanData['brand_en'];
                if (isset($cleanData['brand_en'])) {
                    $cleanData['brand_en'] = '';
                }

                return redirect((string) url('brand_edit', ['id' => $id]))
                    ->with('old_data', $cleanData)
                    ->with('error', 'Manual Ya existe una marca con ese nombre en inglés: ' . $error_content)
                    ->with('error_field', 'brand_en');
            }
        }

        if ($data['slug'] !== $brand->slug) {
            $exists = \app\model\Brand::where('slug', $data['slug'])
                ->where('id', '<>', $id)
                ->find();
            
            if ($exists) {

                // Limpiar solo el campo con error
                $cleanData = $data;
                $error_content = $cleanData['slug'];
                if (isset($cleanData['slug'])) {
                    $cleanData['slug'] = '';
                }

                return redirect((string) url('brand_edit', ['id' => $id]))
                    ->with('old_data', $cleanData)
                    ->with('error', 'Manual El slug ya existe: '. $error_content)
                    ->with('error_field', 'slug');
            }
        }

        try {
            // Inicializar campos de eliminación si no existen
            $data['delete_pic'] = $data['delete_pic'] ?? '0';
            $data['delete_block_pic'] = $data['delete_block_pic'] ?? '0';

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

                    // CONFIGURAR el tamaño mínimo
                    $this->imageService->setImageMinDimension(800);


                    try {
                        $data['pic'] = $this->imageService->processSquareImage(
                            $file,
                            $topicPath, 
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
                        $data['block_pic'] = $this->imageService->processLandscapeImage(
                            $bg,
                            $topicPath, 
                            $data['brand_en'], 
                            $brand->block_pic
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // Actualizar marca en la base de datos
            //Brand::update($data, ['id' => $id]);
            $brand->save($data);
            return redirect((string) url('brand_index'))->with('success', 'Marca actualizada correctamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect((string) url('brand_edit', ['id' => $id]))->with('error', $e->getMessage());
        }
    }

    /**
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la marca a eliminar permanentemente
     * @return \think\response\Redirect
     */
    public function forceDelete(Request $request, int $id): Redirect
    {
        try {
            $brand = Brand::onlyTrashed()->findOrFail($id);
            $brand->force()->delete();
            return redirect((string) url('brand_index'))->with('success', 'Marca eliminada permanentemente.');
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('brand_index'))->with('error', 'Marca no encontrada o ya fue eliminada permanentemente.');
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